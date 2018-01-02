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
           ->setDescription('commands.content-type.create.description')
           ->addOption(
                'type',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.content-type.options.type'
            )
            ->addOption(
                'name',
                '',
                InputOption::VALUE_OPTIONAL,
                'commands.create.content-type.options.name'
            )
            ->addOption(
                 'description',
                 '',
                 InputOption::VALUE_REQUIRED,
                 'commands.create.content-type.options.description'
             )
             ->addOption(
                 'fields',
                 '',
                 InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                 'commands.create.content-type.options.fields'
             );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $type_name = $input->getOption('type');
       $type = $this->stringConverter->createMachineName($input->getOption('type'));
       $name = $input->getOption('name');
       $description = $input->getOption('description');
       $fields = $input->getOption('fields');

       $modulecommand = $this->getApplication()->find('module:create');

       $modulearguments = array(
         'command' => 'module:create',
         '--module' => $type_name,
         '--machine-name' => $type,
         '--module-path' => '/module',
         '--description' => $description,
         '--core' => '1.x',
         '--package' => 'Custom',
         '--module-file' => 'yes',
         '--dependencies' => '',
         '--create-faker' => TRUE,
         '--create-composer' => FALSE,
         'isContentType' => TRUE,
         'fields' => $fields,
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
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $type.' add',
             'name' => $type.'.'.$type.'_add',
             'method' => $type.'_add',
             'path' => '/admin/'.$type.'/add',
             'args' => array(),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $type.' edit',
             'name' => $type.'.'.$type.'_edit',
             'method' => $type.'_edit',
             'path' => '/admin/'.$type.'/edit/{'.substr($type, 0, 1 ).'id}',
             'args' => array('$'.substr($type, 0, 1 ).'id'),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $type.' update',
             'name' => $type.'.'.$type.'_update',
             'method' => $type.'_update',
             'path' => '/admin/'.$type.'/update',
             'args' => array(),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $type.' del',
             'name' => $type.'.'.$type.'_del',
             'method' => $type.'_del',
             'path' => '/admin/'.$type.'/del/{'.substr($type, 0, 1 ).'id}',
             'args' => array('$'.substr($type, 0, 1 ).'id'),
             'permission' => 'access admin page',
             'nocache' => false
           )
         ),
         'isContentType' => TRUE,
         'fields' => $fields,
       );

       $ctltypeInput = new ArrayInput($ctlearguments);
       $returnCode = $ctlcommand->run($ctltypeInput, $output);

       $writed = $this->renderFile('ct-list.html', HUNTER_ROOT .'/theme/admin/'.$type.'-list.html', array('type' => $type, 'name' => $name, 'fields' => $fields));
       $writed = $this->renderFile('ct-add.html', HUNTER_ROOT .'/theme/admin/'.$type.'-add.html', array('type' => $type, 'name' => $name, 'fields' => $fields));
       $writed = $this->renderFile('ct-edit.html', HUNTER_ROOT .'/theme/admin/'.$type.'-edit.html', array('type' => $type, 'name' => $name, 'fields' => $fields));
       $writed = $this->renderFile('ct-install.html', HUNTER_ROOT .'/module/'.$type.'/'.$type.'.install', array('type' => $type, 'name' => $name, 'fields' => $fields));

       $module_install_command = $this->getApplication()->find('module:install');

       $module_install_arguments = array(
         'command' => 'module:install',
         'module' => $type,
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
           $name = hunter_convert_to_utf8($helper->ask($input, $output, $question));
           $input->setOption('name', $name);
       }

       // --description option
       $description = $input->getOption('description');
       if (!$description) {
           $question = new Question('Enter type description [My custom content type]:', 'My custom content type');
           $description = hunter_convert_to_utf8($helper->ask($input, $output, $question));
           $input->setOption('description', $description);
       }

       // --fields option
       $fields = $input->getOption('fields');
       if (!$fields) {
           while (true) {
              //name
              $field_name_question = new Question('Enter the field name (leave empty and press enter when done) []:', '');
              $name = str_replace(' ','_',strtolower($helper->ask($input, $output, $field_name_question)));

              if ($name === '') {
                  break;
              }

              //lable
              $lable_question = new Question('Enter the lable name []:', '');
              $lable = hunter_convert_to_utf8($helper->ask($input, $output, $lable_question));

              //type
              $type_question = new ChoiceQuestion(
                 'Choose the field type [varchar]:',
                 array('varchar', 'int', 'blob', 'text'),
                 0
              );
              $type = $helper->ask($input, $output, $type_question);

              switch ($type)
              {
              case 'int':
                $type_setting_default_question = new Question('Enter the int default value [0]:', 0);
                $type_setting[$name]['default'] = $helper->ask($input, $output, $type_setting_default_question);
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
                $type_setting[$name]['length'] = '60';
                break;
              case 'blob':
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
                break;
              case 'text':
                $type_setting_size_question = new ChoiceQuestion(
                   'Choose the field type [big]:',
                   array('big', 'normal'),
                   0
                );
                $type_setting[$name]['size'] = $helper->ask($input, $output, $type_setting_size_question);
                break;
              default:
                $type_setting_length_question = new Question('Enter the varchar length [255]:', '255');
                $type_setting[$name]['length'] = $helper->ask($input, $output, $type_setting_length_question);
                $type_setting_default_question = new Question('Enter the int default value []:', '');
                $type_setting[$name]['default'] = $helper->ask($input, $output, $type_setting_default_question);
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
              }

              //html_type
              $html_type_question = new ChoiceQuestion(
                 'Choose the field html type [varchar]:',
                 array('text', 'textarea', 'image', 'file', 'select', 'radio', 'checkbox', 'hidden', 'password', 'tel'),
                 0
              );
              $html_type = $helper->ask($input, $output, $html_type_question);

              switch ($html_type)
              {
              case 'select':
              case 'radio':
              case 'checkbox':
                $i = 0;
                while (true) {
                  //option
                  $html_type_option_value_question = new Question('Enter the options value (leave empty and press enter when done) []:', '');
                  $html_type_option[$name][$i]['value'] = str_replace(' ','_',strtolower($helper->ask($input, $output, $html_type_option_value_question)));

                  if ($html_type_option[$name][$i]['value'] === '') {
                      break;
                  }

                  //html type option lable
                  $html_type_option_lable_question = new Question('Enter the options lable []:', '');
                  $html_type_option[$name][$i]['lable'] = hunter_convert_to_utf8($helper->ask($input, $output, $html_type_option_lable_question));
                  $i++;
                }

                $html_type_setting[$name] = array();
                if($html_type == 'checkbox'){
                  //checkbox skin
                  $html_type_setting_skin_question = new ChoiceQuestion(
                     'Choose the skin type [default]:',
                     array('default', 'primary', 'switch'),
                     0
                  );
                  $html_type_setting[$name]['skin'] = $helper->ask($input, $output, $html_type_setting_skin_question);

                  //checkbox custom value
                  $html_type_setting_custom_value_question = new Question('Enter the checkbox custom value [yes]:', 'yes');
                  $html_type_setting[$name]['custom_value'] = $helper->ask($input, $output, $html_type_setting_custom_value_question);
                }
                break;
              case 'image':
                //accept option
                $image_type_question = new ChoiceQuestion(
                   'Choose the image type [single]:',
                   array('single', 'multiple'),
                   0
                );
                $html_type_setting[$name]['image_type'] = $helper->ask($input, $output, $image_type_question);
                break;
              case 'file':
                //accept option
                $file_accept_question = new ChoiceQuestion(
                   'Choose the file accept type [file]:',
                   array('file', 'video', 'audio'),
                   0
                );
                $html_type_setting[$name]['file_accept'] = $helper->ask($input, $output, $file_accept_question);

                if($html_type_setting[$name]['file_accept'] == 'video') {
                  $default_exts = 'rm|rmvb|wmv|avi|mp4|3gp|mkv';
                }elseif ($html_type_setting[$name]['file_accept'] == 'audio') {
                  $default_exts = 'wav|mp3|ogg|wma|aac';
                }else {
                  $default_exts = 'doc|pdf|txt|xls|zip|rar|7z';
                }

                //file exts
                $file_exts_question = new Question('Enter the allowed extensions ['.$default_exts.']:', $default_exts);
                $html_type_setting[$name]['file_exts'] = $helper->ask($input, $output, $file_exts_question);

                //file size
                $file_size_question = new Question('Enter the file size, default no limited [KB]:', 0);
                $html_type_setting[$name]['file_size'] = $helper->ask($input, $output, $file_size_question);
                break;
              default:
                $html_type_option[$name] = array();
                $html_type_setting[$name] = array();
              }
              if(isset($html_type_option[$name])){
                unset($html_type_option[$name][count($html_type_option[$name])-1]);
              }
              $fields[$name] = [
                  'name' => $name,
                  'lable' => $lable,
                  'type' => $type,
                  'type_setting' => $type_setting[$name],
                  'html_type' => $html_type,
                  'html_type_option' => isset($html_type_option[$name]) ? $html_type_option[$name] : array(),
                  'html_type_setting' => $html_type_setting[$name],
              ];
           }

           $input->setOption('fields', $fields);
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
