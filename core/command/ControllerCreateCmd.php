<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
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
    * @var routeList
    */
   protected $routeList;

   /**
    * @var StringConverter
    */
   protected $stringConverter;

   /**
    * InstallCommand constructor.
    * @param Site $site
    */
   public function __construct() {
       $application = new Application();
       $this->moduleList = $application->boot()->getModulesList();
       $this->routeList = $application->boot()->getRoutesList();
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
            );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $module = $input->getOption('module');
       $class = $input->getOption('class');
       $routes = $input->getOption('routes');

       if(isset($this->moduleList[$module])){
         $module_path = HUNTER_ROOT .'/'. dirname($this->moduleList[$module]['pathname']);
       }

       $routes = $this->inlineValueAsArray($routes);
       $input->setOption('routes', $routes);

       $parameters = [
         'class_name' => $class,
         'module' => $module,
         'routes' => $routes,
       ];

       $writed = $this->renderFile(
                     'controller.php.html',
                     $module_path.'/src/Controller/'.$class.'.php',
                     $parameters
                 );

       $writed = $this->renderFile(
                     'routing-controller.yml.html',
                     $module_path.'/'.$module.'.routing.yml',
                     $parameters,
                     FILE_APPEND
                 );

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$class.' controller create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$class.' controller create failed!');
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
           $question = new Question('Enter the modue name:', '');
           $module = $helper->ask($input, $output, $question);
           $input->setOption('module', $module);
       }

       // --class option
       $class = $input->getOption('class');
       if (!$class) {
           $question = new Question('Enter the Controller class name [DefaultController]:', 'DefaultController');
           $class = $helper->ask($input, $output, $question);
           $input->setOption('class', $class);
       }

       // --routes option
       $routes = $input->getOption('routes');
       if (!$routes) {
           if(isset($this->routeList[$module])){
             foreach ($this->routeList[$module] as $key => $info) {
               list($class, $method) = explode("::", $info['defaults']['_controller']);
               $routes[] = [
                 'title' => $info['defaults']['_title'],
                 'name' => $key,
                 'method' => $method,
                 'path' => $info['path']
               ];
             }
           }
           while (true) {
              $title_question = new Question('Enter the Controller method title (leave empty and press enter when done) []:', '');
              $title = $helper->ask($input, $output, $title_question);

              if ($title === '') {
                  break;
              }

              $method_question = new Question('Enter the action method name [hello]:', 'hello');
              $method = $helper->ask($input, $output, $method_question);

              $path_question = new Question('Enter the route path [/'.$module.'/hello/{name}]:', '/'.$module.'/hello/{name}');
              $path = $helper->ask($input, $output, $path_question);

              $classMachineName = $this->stringConverter->createMachineName($class);
              $routeName = $module . '.' . str_replace("controller", "", $classMachineName) . '_' . $method;

              $routes[] = [
                  'title' => $title,
                  'name' => $routeName,
                  'method' => $method,
                  'path' => $path
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

}
