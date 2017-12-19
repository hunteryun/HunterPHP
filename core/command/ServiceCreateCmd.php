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

/**
 * 创建Service命令
 * php hunter service:create
 */
class ServiceCreateCmd extends BaseCommand {

   /**
    * @var moduleList
    */
   protected $moduleList;

   /**
    * @var serviceList
    */
   protected $serviceList;

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
       $this->serviceList = $application->boot()->getServicesList();

       parent::__construct();
   }

   /**
    * {@inheritdoc}
    */
   protected function configure() {
       $this
           ->setName('service:create')
           ->setDescription('commands.service.create.description')
           ->addOption(
                'module',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.service.options.module'
            )
            ->addOption(
                'services',
                '',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'commands.create.service.options.services'
            );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $module = $input->getOption('module');
       $services = $input->getOption('services');

       if(isset($this->moduleList[$module])){
         $module_path = HUNTER_ROOT .'/'. dirname($this->moduleList[$module]['pathname']);
       }else{
         $module_path = HUNTER_ROOT .'/module/'.strtolower($module);
       }

       $services = $this->inlineValueAsArray($services);
       $input->setOption('services', $services);

       $parameters = [
         'module' => $module,
         'services' => $services,
         'append' => $this->append,
       ];

       $writed = $this->renderFile(
           'services.yml.html',
           $module_path.'/'.$module.'.services.yml',
           $parameters,
           FILE_APPEND
       );

       foreach ($services as $key => $perm) {
         if(!$this->append || !file_exists($module_path.'/src/Plugin/'.$perm['class'].'.php')){
           $parms = [
             'module' => $module,
             'class' => $perm['class'],
           ];

           $writed = $this->renderFile(
               'services.php.html',
               $module_path.'/src/Plugin/'.$perm['class'].'.php',
               $parms
           );
         }
       }

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$module.' Service create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$module.' Service create failed!');
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

       // --services option
       $services = $input->getOption('services');
       if (!$services) {
           if(isset($this->serviceList[$module]) && !empty($this->serviceList[$module])){
             $this->append = true;
           }

           while (true) {
              //service name
              $name_question = new Question('Enter the service name (leave empty and press enter when done) []:', '');
              $name = $helper->ask($input, $output, $name_question);

              if ($name === '') {
                  break;
              }

              //service class
              $class_question = new Question('Enter the class name ['.ucwords($module).'Plugin]:', ucwords($module).'Plugin');
              $class = $helper->ask($input, $output, $class_question);

              //service arguments
              $en_argument_question = new ConfirmationQuestion('Enable argument (y/n) [No]? ', FALSE);
              $en_argument = $helper->ask($input, $output, $en_argument_question);

              $arguments = false;
              if($en_argument){
                $arguments_question = new Question('Enter the service arguments []:', false);
                $arguments = $helper->ask($input, $output, $arguments_question);
              }

              $services[] = [
                  'name' => strtolower($name),
                  'class' => $class,
                  'arguments' => $arguments,
              ];
           }

           $input->setOption('services', $services);
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
