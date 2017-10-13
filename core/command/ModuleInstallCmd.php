<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
          ->addArgument('module', InputArgument::REQUIRED, 'commands.module.install.argument.module');
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
      $installed = false;
      if(isset($this->moduleList[$input->getArgument('module')])){
          $install_file = str_replace('info.yml', 'install', $this->moduleList[$input->getArgument('module')]['pathname']);
      }else {
          $install_file = 'module/'.$input->getArgument('module').'/'.$input->getArgument('module').'.install';
      }

      if(file_exists($install_file)){
        require_once $install_file;

        $schema_fun = $input->getArgument('module').'_schema';
        $install_fun = $input->getArgument('module').'_install';
        if (function_exists($schema_fun)) {
          $schemas = $schema_fun();
          $installed = db_schema()->installSchema($schemas);
        }

        if (function_exists($install_fun)) {
          $installed = true;
          $install_fun();
        }
      }

      if($installed){
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module install successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module install failed!');
      }
   }

}
