<?php

/*
 * @file
 *
 * Blade Environment
 */

namespace Hunter\Core\Templating\Blade;

class Environment {

    /**
     * Loader
     */
    protected $loader;

    /**
     * 配置信息
     */
    protected $options;

    /**
     * 全局变量
     */
    protected $globals = array();

    /**
     * All of the finished, captured sections.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * The stack of in-progress sections.
     *
     * @var array
     */
    protected $sectionStack = [];

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * 析构函数
     */
    public function __construct($loader = null, $options = array()) {
        $this->loader  = $loader;
        $this->options = $options + array(
            'cache' => false,
        );
        $this->addGlobal('__env', $this);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Xiaoler\Blade\View
     */
    public function make($name, $data = [], $mergeData = []) {
        $name = $this->normalizeName($name);

        if(!strpos($name,'.')){
          $name = $name.'.html';
        }

        if(substr($name, 0, 1) != '/'){
          $name = '/'.$name;
        }

        $data = array_merge($mergeData, $data);

        return view($name, $data);
    }

    /**
     * Normalize a view name.
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeName($name) {
        if (strpos($name, '::') === false) {
            return str_replace('.', '/', $name);
        }

        list($namespace, $name) = explode('::', $name);

        return $namespace.'::'.str_replace('.', '/', $name);
    }

    public function render($name, $parameters = array()) {
        ob_start();
        $this->display($name, $parameters);
        return ob_get_clean();
    }

    public function display($name, $parameters = array()) {
      global $hunter_debug;
      $this->addGlobal('messages', $this->renderMessages());
      $compile = new BladeCompiler($name, $this->loader->getCachePath());
      $compiled = $compile->getCompiledPath($name);

      if($this->exists($compiled, TRUE) && !$hunter_debug){
        $variables = $this->mergeGlobals($parameters);
        extract($variables);
        include $compiled;
      }else{
        if ($file = $this->exists($name)) {
            $compile->compile($name);
            $variables = $this->mergeGlobals($parameters);
            extract($variables);
            include $compiled;
        }
      }
      return $this;
    }

    public function exists($name, $cache = FALSE) {
        return $this->getLoader()->exists($name, $cache);
    }

    public function setLoader($loader) {
        $this->loader = $loader;
        return $this;
    }

    public function getLoader() {
        return $this->loader;
    }

    public function addGlobal($name, $value) {
        $this->globals[$name] = $value;
        return $this;
    }

    public function setGlobals(array $context) {
        $this->globals = $context + $this->globals;
        return $this;
    }

    public function getGlobals() {
        return $this->globals;
    }

    public function mergeGlobals(array $context) {
        return $context + $this->getGlobals();
    }

    /**
     * Start injecting content into a section.
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    public function startSection($section, $content = '') {
        if ($content === '') {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content);
        }
    }

    /**
     * Inject inline content into a section.
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    public function inject($section, $content) {
        return $this->startSection($section, $content);
    }

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yieldSection() {
        return $this->yieldContent($this->stopSection());
    }

    /**
     * Stop injecting content into a section.
     *
     * @param  bool  $overwrite
     * @return string
     */
    public function stopSection($overwrite = false) {
        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    /**
     * Stop injecting content into a section and append it.
     *
     * @return string
     */
    public function appendSection() {
        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    /**
     * Append content to a given section.
     *
     * @param  string  $section
     * @param  string  $content
     * @return void
     */
    protected function extendSection($section, $content) {
        if (isset($this->sections[$section])) {
            $content = str_replace('@parent', $content, $this->sections[$section]);
        }

        $this->sections[$section] = $content;
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string  $section
     * @param  string  $default
     * @return string
     */
    public function yieldContent($section, $default = '') {
        $sectionContent = $default;

        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }

        $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

        return str_replace(
            '--parent--holder--', '@parent', str_replace('@parent', '', $sectionContent)
        );
    }

    /**
     * Flush all of the section contents.
     *
     * @return void
     */
    public function flushSections() {
        $this->sections = [];

        $this->sectionStack = [];
    }

    /**
     * Flush all of the section contents if done rendering.
     *
     * @return void
     */
    public function flushSectionsIfDoneRendering() {
        if ($this->doneRendering()) {
            $this->flushSections();
        }
    }

    /**
     * Increment the rendering counter.
     *
     * @return void
     */
    public function incrementRender() {
        $this->renderCount++;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender() {
        $this->renderCount--;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering() {
        return $this->renderCount == 0;
    }

    /**
     * Handle render session message.
     *
     * @return string
     */
    public function renderMessages($type = NULL) {
      $messages = hunter_get_messages($type);

      if(!empty($messages)){
        foreach($messages as $type => $message){
          $out = '<div class="messages '.$type.'">';
          $out .= '<ul>';
          foreach($message as $item){
            $out .= '<li>'.$item.'</li>';
          }
          $out .= '</ul>';
          $out .= '</div>';
        }
        return $out;
      }
      return FALSE;
    }

}
