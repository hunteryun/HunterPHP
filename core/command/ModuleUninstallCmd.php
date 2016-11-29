<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;

/**
 * 卸载模块命令
 * php hunter module:uninstall
 */
class ModuleUninstallCmd extends BaseCommand {

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
          ->setName('module:uninstall')
          ->setDescription('commands.module.uninstall.description')
          ->addArgument('module', InputArgument::REQUIRED, 'commands.module.uninstall.options.module')
          ->addOption('force', '', InputOption::VALUE_NONE, 'commands.module.uninstall.options.force');
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
      $uninstalled = false;
      $module = $input->getArgument('module');
      if(isset($this->moduleList[$module])){
          $install_file = str_replace('info.yml', 'install', $this->moduleList[$module]['pathname']);

          if(file_exists($install_file)){
            require_once $install_file;
          }

          $schema_fun = $module.'_schema';
          $uninstall_fun = $module.'_uninstall';
          if (function_exists($schema_fun)) {
              $tables = array_keys($schema_fun());
          }

          foreach ($tables as $table) {
            $uninstalled = db_schema()->dropTable($table);
          }

          if (function_exists($uninstall_fun)) {
            $uninstall_fun();
          }
      }

      if($uninstalled){
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module uninstall successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module uninstall failed!');
      }
   }

}
