<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Hunter\Core\Utility\StringConverter;
use Faker\Factory;

/**
 * 创建假数据命令
 * php hunter faker
 */
class FakerContentCmd extends BaseCommand {

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
         ->setName('faker')
         ->setDescription('commands.faker.description')
         ->addOption('table', '', InputOption::VALUE_REQUIRED, 'commands.faker.options.number')
         ->addOption('fields', '', InputOption::VALUE_REQUIRED, 'commands.faker.options.number')
         ->addOption('number', '', InputOption::VALUE_REQUIRED, 'commands.faker.options.number');
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
      $faker = Factory::create();
      for($i = 0; $i < (int) $input->getOption('number'); $i++) {
          $mid = db_insert($input->getOption('table'))
              ->fields(array(
                'name' => $faker->name,
              ))
              ->execute();
      }
      if($mid){
        $output->writeln('['.date("Y-m-d H:i:s").'] '. $input->getOption('table') .' have '. $input->getOption('number').' content create successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] '. $input->getOption('table') .' have '. $input->getOption('number').' content create failed!');
      }
   }

   /**
    * {@inheritdoc}
    */
   protected function interact(InputInterface $input, OutputInterface $output) {
       $helper = $this->getHelper('question');

       // --table option
       $table = $input->getOption('table');
       if (!$table) {
           $choices = db_get_tables();
           $default_name = current($choices);
           if (null !== $default_name) {
              $values = array_flip($choices);
              $default = $values[$default_name];
           }
           $question = new ChoiceQuestion(
              'Select the table name ['.$default_name.']:',
              $choices,
              $default
           );
           $table = $helper->ask($input, $output, $question);
           $input->setOption('table', $table);
       }

       // --fields option
       $fields = $input->getOption('fields');
       if (!$fields) {
           while (true) {
              $field_choices = db_get_fields($table);
              $field_choices[count($field_choices)] = '';
              $question = new ChoiceQuestion(
                'Select the field name (leave empty and press enter when done) []:',
                $field_choices,
                ''
              );
              $field_name = $helper->ask($input, $output, $question);

              if ($field_name === '') {
                  break;
              }

              $field_type_choices = array('name', 'address', 'text', 'randomNumber');
              $question = new ChoiceQuestion(
                'Select the field type []:',
                $field_type_choices
              );
              $field_type = $helper->ask($input, $output, $question);

              $fields[] = [
                  'name' => $field_name,
                  'type' => $field_type,
              ];
           }

           $input->setOption('fields', $fields);
       }

      // --number option
      $number = $input->getOption('number');
      if (!$number) {
          $question = new Question('How many record you want to create? [10]:', 10);
          $number = $helper->ask($input, $output, $question);
          $input->setOption('number', $number);
      }
   }

}
