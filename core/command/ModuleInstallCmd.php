<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Noodlehaus\Config;

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
      if($input->getArgument('module') == 'all'){
        ksort($this->moduleList);
        foreach ($this->moduleList as $module => $item) {
          $install_file = str_replace('info.yml', 'install', $item['pathname']);
          $installed = $this->exec_install($module, $install_file);
        }
      }else {
        if(isset($this->moduleList[$input->getArgument('module')])){
            $install_file = str_replace('info.yml', 'install', $this->moduleList[$input->getArgument('module')]['pathname']);
        }else {
            $install_file = 'module/'.$input->getArgument('module').'/'.$input->getArgument('module').'.install';
        }
        $installed = $this->exec_install($input->getArgument('module'), $install_file);
      }

      if($installed){
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module install successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] '.$input->getArgument('module').' module install failed!');
      }
   }

   /**
    * 执行安装
    */
   protected function exec_install($module, $install_file) {
     $installed = false;

     if(file_exists($install_file)){
       require_once $install_file;

       $schema_fun = $module.'_schema';
       $install_fun = $module.'_install';
       if (function_exists($schema_fun)) {
         $schemas = $schema_fun();
         $installed = db_schema()->installSchema($schemas);
       }

       if (function_exists($install_fun)) {
         $installed = true;
         $install_fun();
       }
     }

     if(module_exists('variable')){
       $install_dir = 'module/'.$module.'/config/install';
       if(is_dir($install_dir)){
         $conf = new Config($install_dir);
         $configdata = $conf->all();
         if(!empty($configdata)){
           foreach ($configdata as $key => $value) {
             variable_set($module.'.'.$key, $value);
           }
         }
         $installed = true;
       }
     }

     return $installed;
   }

}
