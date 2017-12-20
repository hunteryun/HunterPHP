<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Hunter\Core\Utility\StringConverter;

/**
 * 创建Controller命令
 * php hunter ctl:create
 */
class ControllerCreateCmd extends BaseCommand {

   /**
    * @var moduleList
    */
   protected $moduleList;

   /**
    * @var permissionList
    */
   protected $permissionList;

   /**
    * @var routeList
    */
   protected $routeList;

   /**
    * @var StringConverter
    */
   protected $stringConverter;

   /**
    * @var bool
    */
   protected $append = false;

   /**
    * InstallCommand constructor.
    * @param Site $site
    */
   public function __construct() {
       $application = new Application();
       $this->moduleList = $application->boot()->getModulesList();
       $this->routeList = $application->boot()->getRoutesList();
       $this->permissionList = $application->boot()->getPermissionsList();
       $this->stringConverter = new StringConverter();

       parent::__construct();
   }

   /**
    * {@inheritdoc}
    */
   protected function configure() {
       $this
           ->setName('ctl:create')
           ->setDescription('commands.controller.create.description')
           ->addOption(
                'module',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.controller.options.module'
            )
            ->addOption(
                'class',
                '',
                InputOption::VALUE_OPTIONAL,
                'commands.create.controller.options.class'
            )
            ->addOption(
                'routes',
                '',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'commands.create.controller.options.routes'
            )
            ->addArgument(
                'isContentType',
                InputArgument::OPTIONAL,
                'Is this a new content type?'
            )
            ->addArgument(
                'fields',
                InputArgument::OPTIONAL,
                'content type fields'
            );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $module = $input->getOption('module');
       $class = $input->getOption('class');
       $routes = $input->getOption('routes');
       $isContentType = $input->getArgument('isContentType');
       $fields = $input->getArgument('fields');

       if(isset($this->moduleList[$module])){
         $module_path = HUNTER_ROOT .'/'. dirname($this->moduleList[$module]['pathname']);
       }else{
         $module_path = HUNTER_ROOT .'/module/'.strtolower($module);
       }

       $routes = $this->inlineValueAsArray($routes);
       $input->setOption('routes', $routes);

       $parameters = [
         'class_name' => $class,
         'module' => $module,
         'routes' => $routes,
         'append' => $this->append,
         'fields' => $fields,
       ];

       if($isContentType){
         $ctltemplate = 'ctcontroller.php.html';
       }else {
         $ctltemplate = 'controller.php.html';
       }

       if(!$this->append || !file_exists($module_path.'/src/Controller/'.$class.'Controller.php')){
         $writed = $this->renderFile(
             $ctltemplate,
             $module_path.'/src/Controller/'.$class.'Controller.php',
             $parameters
         );
       }

       $writed = $this->renderFile(
           'routing-controller.yml.html',
           $module_path.'/'.$module.'.routing.yml',
           $parameters,
           FILE_APPEND
       );

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$class.' Controller create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$class.' Controller create failed!');
       }
   }

   /**
    * {@inheritdoc}
    */
   protected function interact(InputInterface $input, OutputInterface $output) {
       $helper = $this->getHelper('question');

       // --module option
       $module = $input->getOption('module');
       if (!$module) {
           $choices = array_keys($this->moduleList);
           $default_name = current($choices);
           if (null !== $default_name) {
              $values = array_flip($choices);
              $default = $values[$default_name];
           }
           $question = new ChoiceQuestion(
              'Select the modue name ['.$default_name.']:',
              $choices,
              $default
           );
           $module = $helper->ask($input, $output, $question);
           $input->setOption('module', $module);
       }

       // --class option
       $class = $input->getOption('class');
       if (!$class) {
           $question = new Question('Enter the Controller class name [Default]:', 'Default');
           $class = $helper->ask($input, $output, $question);
           $input->setOption('class', $class);
       }

       // --routes option
       $routes = $input->getOption('routes');
       if (!$routes) {
           if(isset($this->routeList[$module]) && !empty($this->routeList[$module])){
             $this->append = true;
           }

           while (true) {
              //route method title
              $title_question = new Question('Enter the Controller method title (leave empty and press enter when done) []:', '');
              $title = $helper->ask($input, $output, $title_question);

              if ($title === '') {
                  break;
              }

              //route method
              $method_question = new Question('Enter the action method name [hello]:', 'hello');
              $method = $helper->ask($input, $output, $method_question);

              //route path
              $path_question = new Question('Enter the route path [/'.$module.'/hello/{name}]:', '/'.$module.'/hello/{name}');
              $path = $helper->ask($input, $output, $path_question);

              //route name
              $classMachineName = $this->stringConverter->createMachineName($class);
              $routeName = $module . '.' . str_replace("controller", "", $classMachineName) . '_' . $method;

              //permission
              $en_permission_question = new ConfirmationQuestion('Enable permission (y/n) [No]? ', FALSE);
              $en_permission = $helper->ask($input, $output, $en_permission_question);

              $permission = false;
              if($en_permission){
                $permission_choices = array_keys($this->permissionList);
                $default_permission_vaule = array_search('access admin page', $permission_choices);
                $permission_question = new ChoiceQuestion(
                   'Select the permission name []:',
                   $permission_choices,
                   $default_permission_vaule
                );
                $permission = $helper->ask($input, $output, $permission_question);
              }

              //nocache
              $nocache_question = new ConfirmationQuestion('Enable cache (y/n) [No]? ', FALSE);
              $nocache = $helper->ask($input, $output, $nocache_question);

              $routes[] = [
                  'title' => hunter_convert_to_utf8($title),
                  'name' => $routeName,
                  'method' => $method,
                  'path' => $path,
                  'args' => $this->getArgumentsFromRoute($path),
                  'permission' => $permission,
                  'nocache' => $nocache
              ];
           }

           $input->setOption('routes', $routes);
       }
   }

   /**
    * @param string $template
    * @param string $target
    * @param array  $parameters
    * @param null   $flag
    *
    * @return bool
    */
   protected function renderFile($template, $target, $parameters, $flag = null) {
       if (!is_dir(dirname($target))) {
           mkdir(dirname($target), 0777, true);
       }

       if (file_put_contents($target, theme('command')->render($template, $parameters), $flag)) {
           $this->files[] = str_replace(HUNTER_ROOT.'/', '', $target);

           return true;
       }

       return false;
   }

   /**
    * @return array
    */
   private function inlineValueAsArray($inputValue)
   {
       $inputArrayValue = [];
       foreach ($inputValue as $key => $value) {
           if (!is_array($value)) {
               $inputValueItems = [];
               foreach (explode(" ", $value) as $inputKeyValueItem) {
                   list($inputKeyItem, $inputValueItem) = explode(":", $inputKeyValueItem);
                   $inputValueItems[$inputKeyItem] = $inputValueItem;
               }
               $inputArrayValue[$key] = $inputValueItems;
           }
       }

       return $inputArrayValue?$inputArrayValue:$inputValue;
   }

   /**
    * @return array
    */
   public function getArgumentsFromRoute($path)
   {
       $returnValues = '';
       preg_match_all('/{(.*?)}/', $path, $returnValues);

       $returnValues = array_map(
           function ($value) {
               return sprintf('$%s', $value);
           }, $returnValues[1]
       );

       return $returnValues;
   }

}
