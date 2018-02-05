<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
                'module_name',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.content-type.options.module_name'
            )
            ->addOption(
                'machine_name',
                '',
                InputOption::VALUE_OPTIONAL,
                'commands.create.content-type.options.machine_name'
            )
            ->addOption(
                'lable_name',
                '',
                InputOption::VALUE_OPTIONAL,
                'commands.create.content-type.options.lable_name'
            )
            ->addOption(
                 'description',
                 '',
                 InputOption::VALUE_REQUIRED,
                 'commands.create.content-type.options.description'
             )
             ->addOption(
                'entity_support',
                '',
                InputOption::VALUE_REQUIRED,
                'commands.create.content-type.options.entity_support'
              )
              ->addOption(
                 'token_support',
                 '',
                 InputOption::VALUE_REQUIRED,
                 'commands.create.content-type.options.token_support'
             )
             ->addOption(
                 'fields',
                 '',
                 InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                 'commands.create.content-type.options.fields'
             )
             ->addOption(
                 'use_last',
                 FALSE,
                 InputOption::VALUE_OPTIONAL,
                 'commands.create.content-type.options.use_last'
             );
   }

   /**
    * {@inheritdoc}
    */
   protected function execute(InputInterface $input, OutputInterface $output) {
       $module_name = hunter_convert_to_utf8($input->getOption('module_name'));
       $machine_name = $input->getOption('machine_name');
       $ct_cache = cache()->get('ct_cmd_'.$machine_name);
       if(!empty($ct_cache)){
         $lable_name = $ct_cache['lable_name'];
         $description = $ct_cache['description'];
         $entity_support = (bool) $ct_cache['entity_support'];
         $token_support = (bool) $ct_cache['token_support'];
         $fields = $ct_cache['fields'];
       }else {
         $lable_name = $input->getOption('lable_name');
         $description = $input->getOption('description');
         $entity_support = (bool) $input->getOption('entity_support');
         $token_support = (bool) $input->getOption('token_support');
         $fields = $input->getOption('fields');
       }

       $modulecommand = $this->getApplication()->find('module:create');

       $modulearguments = array(
         'command' => 'module:create',
         '--module' => $input->getOption('module_name'),
         '--machine-name' => $machine_name,
         '--module-path' => '/module',
         '--description' => $description,
         '--core' => '1.x',
         '--package' => 'Custom',
         '--module-file' => 'yes',
         '--dependencies' => '',
         '--create-faker' => TRUE,
         '--create-views' => TRUE,
         '--create-composer' => FALSE,
         'isContentType' => TRUE,
         'supportEntity' => $entity_support,
         'supportToken' => $token_support,
         'fields' => $fields,
       );

       $moduletypeInput = new ArrayInput($modulearguments);
       $returnCode = $modulecommand->run($moduletypeInput, $output);

       $ctlcommand = $this->getApplication()->find('ctl:create');

       $ctlearguments = array(
         'command' => 'ctl:create',
         '--module' => $machine_name,
         '--class' => ucfirst($machine_name),
         '--routes' => array(
           array(
             'title' => $machine_name.' list',
             'name' => $machine_name.'.'.$machine_name.'_list',
             'method' => $machine_name.'_list',
             'path' => '/admin/'.$machine_name.'/list',
             'args' => array(),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $machine_name.' add',
             'name' => $machine_name.'.'.$machine_name.'_add',
             'method' => $machine_name.'_add',
             'path' => '/admin/'.$machine_name.'/add',
             'args' => array(),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $machine_name.' edit',
             'name' => $machine_name.'.'.$machine_name.'_edit',
             'method' => $machine_name.'_edit',
             'path' => '/admin/'.$machine_name.'/edit/{'.substr($machine_name, 0, 1 ).'id}',
             'args' => array('$'.substr($machine_name, 0, 1 ).'id'),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $machine_name.' update',
             'name' => $machine_name.'.'.$machine_name.'_update',
             'method' => $machine_name.'_update',
             'path' => '/admin/'.$machine_name.'/update',
             'args' => array(),
             'permission' => 'access admin page',
             'nocache' => false
           ),
           array(
             'title' => $machine_name.' del',
             'name' => $machine_name.'.'.$machine_name.'_del',
             'method' => $machine_name.'_del',
             'path' => '/admin/'.$machine_name.'/del/{'.substr($machine_name, 0, 1 ).'id}',
             'args' => array('$'.substr($machine_name, 0, 1 ).'id'),
             'permission' => 'access admin page',
             'nocache' => false
           )
         ),
         'isContentType' => TRUE,
         'fields' => $fields,
       );

       $ctltypeInput = new ArrayInput($ctlearguments);
       $returnCode = $ctlcommand->run($ctltypeInput, $output);

       $writed = $this->renderFile('ct-list.html', HUNTER_ROOT .'/theme/admin/'.$machine_name.'-list.html', array('type' => $machine_name, 'name' => $lable_name, 'fields' => $fields));
       $writed = $this->renderFile('ct-add.html', HUNTER_ROOT .'/theme/admin/'.$machine_name.'-add.html', array('type' => $machine_name, 'name' => $lable_name, 'fields' => $fields));
       $writed = $this->renderFile('ct-edit.html', HUNTER_ROOT .'/theme/admin/'.$machine_name.'-edit.html', array('type' => $machine_name, 'name' => $lable_name, 'fields' => $fields));
       $writed = $this->renderFile('ct-install.html', HUNTER_ROOT .'/module/'.$machine_name.'/'.$machine_name.'.install', array('type' => $machine_name, 'name' => $lable_name, 'fields' => $fields));

       $module_install_command = $this->getApplication()->find('module:install');

       $module_install_arguments = array(
         'command' => 'module:install',
         'module' => $machine_name,
       );

       $module_install_typeInput = new ArrayInput($module_install_arguments);
       $returnCode = $module_install_command->run($module_install_typeInput, $output);

       if($writed){
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$machine_name.' content type create successful!');
       }else{
         $output->writeln('['.date("Y-m-d H:i:s").'] '.$machine_name.' content type create failed!');
       }
   }

   /**
    * {@inheritdoc}
    */
   protected function interact(InputInterface $input, OutputInterface $output) {
       $helper = $this->getHelper('question');

       // --module_name option
       $module_name = $input->getOption('module_name');
       if (!$module_name) {
           $question = new Question('Enter the new module name:', '');
           $module_name = hunter_convert_to_utf8($helper->ask($input, $output, $question));
           $input->setOption('module_name', $module_name);
       }

       // --machine_name option
       $machine_name = $input->getOption('machine_name');
       if (!$machine_name) {
           $question = new Question('Enter the machine name ['.$this->stringConverter->createMachineName($module_name).']:', $this->stringConverter->createMachineName($module_name));
           $machine_name = $helper->ask($input, $output, $question);
           $input->setOption('machine_name', $machine_name);
       }

       if($cache = cache()->get('ct_cmd_'.$machine_name)){
         // --use last config option
         $use_last = $input->getOption('use_last');
         if (!$use_last) {
             $use_last_question = new ConfirmationQuestion('Use last config from cache (y/n) [No]? ', FALSE);
             $use_last = $helper->ask($input, $output, $use_last_question);
             $input->setOption('use_last', $use_last);
         }

         if($use_last){
           return $cache;
         }
       }

       // --lable_name option
       $lable_name = $input->getOption('lable_name');
       if (!$lable_name) {
           $question = new Question('Enter the lable name:', '');
           $lable_name = hunter_convert_to_utf8($helper->ask($input, $output, $question));
           $input->setOption('lable_name', $lable_name);
       }

       // --description option
       $description = $input->getOption('description');
       if (!$description) {
           $question = new Question('Enter type description [My custom content type]:', 'My custom content type');
           $description = hunter_convert_to_utf8($helper->ask($input, $output, $question));
           $input->setOption('description', $description);
       }

       // --support entity option
       $entity_support = $input->getOption('entity_support');
       if (!$entity_support) {
           $entity_support_question = new ConfirmationQuestion('Enable support Entity (y/n) [No]? ', FALSE);
           $entity_support = $helper->ask($input, $output, $entity_support_question);
           $input->setOption('entity_support', $entity_support);
       }

       // --support token option
       $token_support = $input->getOption('token_support');
       if (!$token_support) {
           $token_support_question = new ConfirmationQuestion('Enable support Token (y/n) [No]? ', FALSE);
           $token_support = $helper->ask($input, $output, $token_support_question);
           $input->setOption('token_support', $token_support);
       }

       // --fields option
       $fields = $input->getOption('fields');
       if (!$fields) {
           while (true) {
              //name
              $field_name_question = new Question('Enter the field name (leave empty and press enter when done) []:', '');
              $field_name = str_replace(' ','_',strtolower($helper->ask($input, $output, $field_name_question)));

              if ($field_name === '') {
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
              $field_type = $helper->ask($input, $output, $type_question);

              switch ($field_type)
              {
              case 'int':
                $type_setting_default_question = new Question('Enter the int default value [0]:', 0);
                $type_setting[$field_name]['default'] = $helper->ask($input, $output, $type_setting_default_question);
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$field_name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
                $type_setting[$field_name]['length'] = '60';
                break;
              case 'blob':
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$field_name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
                break;
              case 'text':
                $type_setting_size_question = new ChoiceQuestion(
                   'Choose the field type [big]:',
                   array('big', 'normal'),
                   0
                );
                $type_setting[$field_name]['size'] = $helper->ask($input, $output, $type_setting_size_question);
                break;
              default:
                $type_setting_length_question = new Question('Enter the varchar length [255]:', '255');
                $type_setting[$field_name]['length'] = $helper->ask($input, $output, $type_setting_length_question);
                $type_setting_default_question = new Question('Enter the int default value []:', '');
                $type_setting[$field_name]['default'] = $helper->ask($input, $output, $type_setting_default_question);
                $type_setting_notnull_question = new Question('Not null value [TRUE]:', TRUE);
                $type_setting[$field_name]['notnull'] = $helper->ask($input, $output, $type_setting_notnull_question);
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
                  $html_type_option[$field_name][$i]['value'] = str_replace(' ','_',strtolower($helper->ask($input, $output, $html_type_option_value_question)));

                  if ($html_type_option[$field_name][$i]['value'] === '') {
                      break;
                  }

                  //html type option lable
                  $html_type_option_lable_question = new Question('Enter the options lable []:', '');
                  $html_type_option[$field_name][$i]['lable'] = hunter_convert_to_utf8($helper->ask($input, $output, $html_type_option_lable_question));
                  $i++;
                }

                $html_type_setting[$field_name] = array();
                if($html_type == 'checkbox'){
                  //checkbox skin
                  $html_type_setting_skin_question = new ChoiceQuestion(
                     'Choose the skin type [default]:',
                     array('default', 'primary', 'switch'),
                     0
                  );
                  $html_type_setting[$field_name]['skin'] = $helper->ask($input, $output, $html_type_setting_skin_question);

                  //checkbox custom value
                  $html_type_setting_custom_value_question = new Question('Enter the checkbox custom value [yes]:', 'yes');
                  $html_type_setting[$field_name]['custom_value'] = $helper->ask($input, $output, $html_type_setting_custom_value_question);
                }
                break;
              case 'image':
                //accept option
                $image_type_question = new ChoiceQuestion(
                   'Choose the image type [single]:',
                   array('single', 'multiple'),
                   0
                );
                $html_type_setting[$field_name]['image_type'] = $helper->ask($input, $output, $image_type_question);
                break;
              case 'file':
                //accept option
                $file_accept_question = new ChoiceQuestion(
                   'Choose the file accept type [file]:',
                   array('file', 'video', 'audio'),
                   0
                );
                $html_type_setting[$field_name]['file_accept'] = $helper->ask($input, $output, $file_accept_question);

                if($html_type_setting[$field_name]['file_accept'] == 'video') {
                  $default_exts = 'rm|rmvb|wmv|avi|mp4|3gp|mkv';
                }elseif ($html_type_setting[$field_name]['file_accept'] == 'audio') {
                  $default_exts = 'wav|mp3|ogg|wma|aac';
                }else {
                  $default_exts = 'doc|pdf|txt|xls|zip|rar|7z';
                }

                //file exts
                $file_exts_question = new Question('Enter the allowed extensions ['.$default_exts.']:', $default_exts);
                $html_type_setting[$field_name]['file_exts'] = $helper->ask($input, $output, $file_exts_question);

                //file size
                $file_size_question = new Question('Enter the file size, default no limited [KB]:', 0);
                $html_type_setting[$field_name]['file_size'] = $helper->ask($input, $output, $file_size_question);
                break;
              default:
                $html_type_option[$field_name] = array();
                $html_type_setting[$field_name] = array();
              }
              if(isset($html_type_option[$field_name])){
                unset($html_type_option[$field_name][count($html_type_option[$field_name])-1]);
              }
              $fields[$field_name] = [
                  'name' => $field_name,
                  'lable' => $lable,
                  'type' => $field_type,
                  'type_setting' => $type_setting[$field_name],
                  'html_type' => $html_type,
                  'html_type_option' => isset($html_type_option[$field_name]) ? $html_type_option[$field_name] : array(),
                  'html_type_setting' => $html_type_setting[$field_name],
              ];
           }

           $input->setOption('fields', $fields);
       }

       $ct_cache = array(
         'module_name' => $module_name,
         'machine_name' => $machine_name,
         'lable_name' => $lable_name,
         'description' => $description,
         'entity_support' => $entity_support,
         'token_support' => $token_support,
         'fields' => $fields
       );

       cache()->set('ct_cmd_'.$machine_name, $ct_cache);
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
