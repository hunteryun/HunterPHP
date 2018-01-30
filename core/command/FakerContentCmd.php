<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Hunter\Core\App\Application;
use Faker\Factory;
use Hunter\Core\Serialization\Yaml;

/**
 * 创建假数据命令
 * php hunter faker
 */
class FakerContentCmd extends BaseCommand {

    /** @var Validator  */
    protected $moduleList;

    /**
     * FakerCommand constructor.
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
         ->setName('faker')
         ->setDescription('commands.faker.description')
         ->addOption('locale', '', InputOption::VALUE_OPTIONAL, 'commands.faker.options.locale')
         ->addOption('confs', '', InputOption::VALUE_REQUIRED, 'commands.faker.options.confs')
         ->addOption('number', '', InputOption::VALUE_REQUIRED, 'commands.faker.options.number');
    }

    /**
    * {@inheritdoc}
    */
    protected function execute(InputInterface $input, OutputInterface $output) {
      $locale = $input->getOption('locale');
      $confs = $input->getOption('confs');
      $number = $input->getOption('number');

      $faker = Factory::create($locale);

      foreach ($confs as $table => $fs) {
        $fields = array();
        for($i = 0; $i < (int) $input->getOption('number'); $i++) {
            if(!empty($fs)){
              foreach ($fs as $name => $type) {
                if(strpos($type,'faker-') !== false){
                  switch ($type=str_replace('faker-','',$type))
                  {
                  case 'password':
                    $fields[$name] = 'password';
                    break;
                  case 'imageUrl':
                    $fields[$name] = $faker->imageUrl(100, 100, 'cats');
                    break;
                  case 'manyImageUrl':
                    $fields[$name] = json_encode(
                      array(
                        array(
                          'image' => $faker->imageUrl(100, 100, 'cats')
                        ),
                        array(
                          'image' => $faker->imageUrl(100, 100, 'cats')
                        ),
                        array(
                          'image' => $faker->imageUrl(100, 100, 'cats')
                        )
                      )
                    );
                    break;
                  case 'randomTwo':
                    $fields[$name] = $faker->randomElement([0, 1]);
                    break;
                  case 'numberBetween-1':
                    $fields[$name] = $faker->numberBetween(0, 10);
                    break;
                  case 'numberBetween-2':
                    $fields[$name] = $faker->numberBetween(10, 100);
                    break;
                  case 'numberBetween-2-50':
                    $fields[$name] = $faker->numberBetween(10, 50);
                    break;
                  case 'numberBetween-3':
                    $fields[$name] = $faker->numberBetween(100, 1000);
                    break;
                  case 'numberBetween-4':
                    $fields[$name] = $faker->numberBetween(1000, 10000);
                    break;
                  case 'numberBetween-5':
                    $fields[$name] = $faker->numberBetween(10000, 100000);
                    break;
                  case 'numberBetween-6':
                    $fields[$name] = $faker->numberBetween(100000, 1000000);
                    break;
                  default:
                    $fields[$name] = $faker->$type;
                  }
                }else {
                  if(is_string($type)){
                    switch ($type)
                    {
                    case 'timenow':
                      $fields[$name] = time();
                      break;
                    default:
                      $fields[$name] = $type;
                    }
                  }else {
                    $fields[$name] = $type;
                  }
                }
              }
            }
            $result = db_insert($table)->fields($fields)->execute();
        }
      }

      if($result){
        $output->writeln('['.date("Y-m-d H:i:s").'] There have '. $input->getOption('number').' content create successful!');
      }else{
        $output->writeln('['.date("Y-m-d H:i:s").'] There have '. $input->getOption('number').' content create failed!');
      }
    }

    /**
    * {@inheritdoc}
    */
    protected function interact(InputInterface $input, OutputInterface $output) {
       $helper = $this->getHelper('question');

       $faker_type_question = new ChoiceQuestion(
          'Choose the faker type [select]:',
          array('select', 'config'),
          0
       );
       $faker_type = $helper->ask($input, $output, $faker_type_question);

       if($faker_type == 'select'){
         $locale_question = new ChoiceQuestion(
            'Choose the locale language [zh_CN]:',
            array('zh_CN', 'en-GB'),
            0
         );
         $locale = $helper->ask($input, $output, $locale_question);
         $input->setOption('locale', $locale);

         // --table option
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

         // --fields option
         $confs = $input->getOption('confs');
         if (!$confs) {
             while (true) {
                $field_choices = db_get_fields($table);
                $field_choices[count($field_choices)] = 'DONE';
                $question = new ChoiceQuestion(
                  'Select the field name (leave empty and press enter when done) []:',
                  $field_choices,
                  'DONE'
                );
                $field_name = $helper->ask($input, $output, $question);

                if ($field_name === 'DONE') {
                    break;
                }

                $field_type_choices = array('name', 'imageUrl', 'address', 'text', 'word', 'randomTwo', 'email', 'password', 'ipv4', 'uuid', 'hexcolor', 'ean6', 'languageCode', 'boolean', 'phoneNumber', 'unixTime');
                $question = new ChoiceQuestion(
                  'Select the field type []:',
                  $field_type_choices,
                  0
                );
                $field_type = $helper->ask($input, $output, $question);

                $confs[$table][$field_name] = $field_type;
             }
             $input->setOption('confs', $confs);
         }
       }else {
         // --module option
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

         $faker_config = 'module/'.$module.'/config/'.$module.'.faker.yml';
         if(file_exists($faker_config)){
           $conf = Yaml::decode(file_get_contents($faker_config));
           if(isset($conf['locale'])){
             $input->setOption('locale', $conf['locale']);
           }
           if(isset($conf['faker']) && !empty($conf['faker'])){
             $input->setOption('confs', $conf['faker']);
           }
         }
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
