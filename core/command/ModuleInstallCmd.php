<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;

/**
 * 迁移数据库命令
 * php hunter migrate:db
 */
class ModuleInstallCmd extends BaseCommand {
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

      $Application = new Application();

      $installed = $Application->installModule($input->getOption('module'));

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
