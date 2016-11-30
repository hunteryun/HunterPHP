<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;

/**
 * 安装模块命令
 * php hunter module:install
 */
class ModuleInstallCmd extends BaseCommand {

   /** @var Validator  */
   protected $moduleList;

   /**
    * InstallCommand constructor.
    * @param Site $site
    */
   public function __construct() {
       $application = new Application();
       $this->moduleList = $application->boot()->getModulesList();
       parent::__construct();
   }

   /**
    * {@inheritdoc}
    */
   protected function configure() {
       $this
           ->setName('module:install')
           ->setDescription('commands.module.install.description')
           ->addOption('module', '', InputOption::VALUE_REQUIRED, 'commands.module.install.options.module');
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
      $installed = false;
      if(isset($this->moduleList[$input->getOption('module')])){
          $install_file = str_replace('info.yml', 'install', $this->moduleList[$input->getOption('module')]['pathname']);

          if(file_exists($install_file)){
            require_once $install_file;
          }

          $schema_fun = $input->getOption('module').'_schema';
          $install_fun = $input->getOption('module').'_install';
          if (function_exists($schema_fun)) {
            $schemas = $schema_fun();
          }

          $installed = db_schema()->installSchema($schemas);

          if (function_exists($install_fun)) {
            $install_fun();
          }
      }

      if($installed){
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getOption('module').' module install successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getOption('module').' module install failed!');
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
           $question = new Question('Please enter modue name:', '');
           $module = $helper->ask($input, $output, $question);
           $input->setOption('module', $module);
       }
   }

}
