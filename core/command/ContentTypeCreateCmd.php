<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Hunter\Core\Utility\StringConverter;

/**
 * 创建content-type命令
 * php hunter ct:create
 */
class ContentTypeCreateCmd extends BaseCommand {

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
       $this->stringConverter = new StringConverter();

       parent::__construct();
   }

   /**
    * {@inheritdoc}
    */
   protected function configure() {
       $this
           ->setName('ct:create')
           ->setDescription('commands.controller.create.description')
           ->addOption(
                'type',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.controller.options.type'
            )
            ->addOption(
                'name',
                '',
                InputOption::VALUE_OPTIONAL,
                'commands.create.controller.options.name'
            )
            ->addOption(
                 'description',
                 '',
                 InputOption::VALUE_REQUIRED,
                 'commands.create.controller.options.description'
             );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $type = $input->getOption('type');
       $name = $input->getOption('name');
       $description = $input->getOption('description');

       $modulecommand = $this->getApplication()->find('module:create');

       $modulearguments = array(
         'command' => 'module:create',
         '--module' => $type,
         '--machine-name' => $this->stringConverter->createMachineName($type),
         '--module-path' => '/module',
         '--description' => $description,
         '--core' => '1.x',
         '--package' => 'Custom',
         '--module-file' => 'yes',
         'isContentType' => TRUE,
       );

       $moduletypeInput = new ArrayInput($modulearguments);
       $returnCode = $modulecommand->run($moduletypeInput, $output);

       $ctlcommand = $this->getApplication()->find('ctl:create');

       $ctlearguments = array(
         'command' => 'ctl:create',
         '--module' => $type,
         '--class' => ucfirst($type),
         '--routes' => array(
           array(
             'title' => $type.' list',
             'name' => $type.'.'.$type.'_list',
             'method' => $type.'_list',
             'path' => '/admin/'.$type.'/list',
             'args' => array(),
           ),
           array(
             'title' => $type.' add',
             'name' => $type.'.'.$type.'_add',
             'method' => $type.'_add',
             'path' => '/admin/'.$type.'/add',
             'args' => array(),
           ),
           array(
             'title' => $type.' edit',
             'name' => $type.'.'.$type.'_edit',
             'method' => $type.'_edit',
             'path' => '/admin/'.$type.'/edit/{'.substr($type, 0, 1 ).'id}',
             'args' => array('$'.substr($type, 0, 1 ).'id'),
           ),
           array(
             'title' => $type.' update',
             'name' => $type.'.'.$type.'_update',
             'method' => $type.'_update',
             'path' => '/admin/'.$type.'/update',
             'args' => array(),
           ),
           array(
             'title' => $type.' del',
             'name' => $type.'.'.$type.'_del',
             'method' => $type.'_del',
             'path' => '/admin/'.$type.'/del/{'.substr($type, 0, 1 ).'id}',
             'args' => array('$'.substr($type, 0, 1 ).'id'),
           )
         ),
         'isContentType' => TRUE,
       );

       $ctltypeInput = new ArrayInput($ctlearguments);
       $returnCode = $ctlcommand->run($ctltypeInput, $output);

       $writed = $this->renderFile('ct-list.html', HUNTER_ROOT .'/theme/admin/'.$type.'-list.html', array('type' => $type, 'name' => $name));
       $writed = $this->renderFile('ct-add.html', HUNTER_ROOT .'/theme/admin/'.$type.'-add.html', array('type' => $type, 'name' => $name));
       $writed = $this->renderFile('ct-edit.html', HUNTER_ROOT .'/theme/admin/'.$type.'-edit.html', array('type' => $type, 'name' => $name));
       $writed = $this->renderFile('ct-install.html', HUNTER_ROOT .'/module/'.$type.'/'.$type.'.install', array('type' => $type, 'name' => $name));

       $module_install_command = $this->getApplication()->find('module:install');

       $module_install_arguments = array(
         'command' => 'module:install',
         '--module' => $type,
       );

       $module_install_typeInput = new ArrayInput($module_install_arguments);
       $returnCode = $module_install_command->run($module_install_typeInput, $output);

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$type.' content type create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$type.' content type create failed!');
       }
   }

   /**
    * {@inheritdoc}
    */
   protected function interact(InputInterface $input, OutputInterface $output) {
       $helper = $this->getHelper('question');

       // --type option
       $type = $input->getOption('type');
       if (!$type) {
           $question = new Question('Enter the new type name:', '');
           $type = $helper->ask($input, $output, $question);
           $input->setOption('type', $type);
       }

       // --name option
       $name = $input->getOption('name');
       if (!$name) {
           $question = new Question('Enter the name:', '');
           $name = $helper->ask($input, $output, $question);
           $input->setOption('name', $name);
       }

       // --description option
       $description = $input->getOption('description');
       if (!$description) {
           $question = new Question('Enter type description [My custom content type]:', 'My custom content type');
           $description = $helper->ask($input, $output, $question);
           $input->setOption('description', $description);
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
