<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Hunter\Core\HtmlMake\Html;

/**
 * 生成静态化文件
 * php hunter html:make
 */
class HtmlMakeCmd extends BaseCommand {

   /** @var Validator  */
   protected $routeList;

   /**
    * InstallCommand constructor.
    * @param Site $site
    */
   public function __construct() {
       $application = new Application();
       $this->routeList = $application->boot()->getRoutesList();
       parent::__construct();
   }

   /**
    * {@inheritdoc}
    */
   protected function configure() {
     $this
          ->setName('html:make')
          ->setDescription('commands.module.install.description')
          ->addArgument('module', InputArgument::OPTIONAL, 'commands.module.install.argument.module')
          ->addArgument('path', InputArgument::OPTIONAL, 'commands.module.install.argument.path');
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
      $cached = true;
      $module = $input->getArgument('module');
      $path = $input->getArgument('path');

      if ($module) {
        if($path){
          foreach ($this->routeList[$module] as $name => $info) {
            if($path == $info['path'] && substr($info['path'], 0, 7) != '/admin/'){
              Html::make($info['defaults']['_controller'], [], $name);
            }
          }
        }else {
          foreach ($this->routeList[$module] as $name => $info) {
            if(substr($info['path'], 0, 7) != '/admin/'){
              Html::make($info['defaults']['_controller'], [], $name);
            }
          }
        }
      }else {
        foreach ($this->routeList as $module => $module_routers) {
          foreach ($module_routers as $name => $info) {
            if(substr($info['path'], 0, 7) != '/admin/'){
              Html::make($info['defaults']['_controller'], [], $name);
            }
          }
        }
      }

      if($cached){
        $output->writeln('['.date("Y-m-d H:i:s").'] html generate successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] html generate failed!');
      }
   }

}
