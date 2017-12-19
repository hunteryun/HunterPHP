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
 * 创建Permission命令
 * php hunter ctl:create
 */
class PermissionCreateCmd extends BaseCommand {

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
    * @var StringConverter
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
           ->setName('perm:create')
           ->setDescription('commands.controller.create.description')
           ->addOption(
                'module',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.permission.options.module'
            )
            ->addOption(
                'permissions',
                '',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'commands.create.permission.options.routes'
            );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $module = $input->getOption('module');
       $permissions = $input->getOption('permissions');

       if(isset($this->moduleList[$module])){
         $module_path = HUNTER_ROOT .'/'. dirname($this->moduleList[$module]['pathname']);
       }else{
         $module_path = HUNTER_ROOT .'/module/'.strtolower($module);
       }

       $permissions = $this->inlineValueAsArray($permissions);
       $input->setOption('permissions', $permissions);

       $parameters = [
         'module' => $module,
         'permissions' => $permissions,
         'append' => $this->append,
       ];

       $writed = $this->renderFile(
           'permissions.yml.html',
           $module_path.'/'.$module.'.permissions.yml',
           $parameters,
           FILE_APPEND
       );

       foreach ($permissions as $key => $perm) {
         if(!$this->append || !file_exists($module_path.'/src/'.$perm['callback_name'].'Permission.php')){
           $parms = [
             'module' => $module,
             'callback_name' => $perm['callback_name'],
           ];

           $writed = $this->renderFile(
               'permission.php.html',
               $module_path.'/src/'.$perm['callback_name'].'Permission.php',
               $parms
           );
         }
       }

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$module.' Permission create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$module.' Permission create failed!');
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

       // --permissions option
       $permissions = $input->getOption('permissions');
       if (!$permissions) {
           if(isset($this->permissionList[$module]) && !empty($this->permissionList[$module])){
             $this->append = true;
           }

           while (true) {
              //permission title
              $title_question = new Question('Enter the permission title (leave empty and press enter when done) []:', '');
              $title = $helper->ask($input, $output, $title_question);

              if ($title === '') {
                  break;
              }

              //permission name
              $name_question = new Question('Enter the permission name ['.strtolower($title).']:', strtolower($title));
              $name = $helper->ask($input, $output, $name_question);

              //permission callback name
              $callback_name_question = new Question('Enter the callback name ['.ucwords($module).']:', ucwords($module));
              $callback_name = $helper->ask($input, $output, $callback_name_question);

              $permissions[] = [
                  'title' => $title,
                  'name' => $name,
                  'callback_name' => $callback_name
              ];
           }

           $input->setOption('permissions', $permissions);
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
