<?php

namespace Hunter\Core\FormApi;

class Layui {
    /**
     * the html form code
     *
     * @var string $form
     */
    private $form;
    private $form_show_type;

    /**
     * start the form
     *
     * @param string $action
     * @param string $class
     * @param bool $enctype
     * @param string $method
     * @param string $charset
     * @return $this
     */
    public function start($action, $id = null, $class = null, $show_type = 'block', $enctype = false, $method = 'post') {
        $this->form_show_type = $show_type;
        switch ($enctype)
        {
            case true:
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" class="layui-form '.$class.'" lay-filter="formTest'.$id.'" enctype="multipart/form-data">';
            break;
            default :
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" class="layui-form '.$class.'" lay-filter="formTest'.$id.'">';
            break;
        }
        return $this;
    }

    /**
     * generate a file input
     *
     * @param $name
     * @param $field
     * @return $this
     */
    public function file($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        if(isset($field['#multiple'])){
          $this->form .= '
            <div class="layui-form-item">
              <label class="layui-form-label">'.$field['#title'].'</label>
              <div class="layui-input-block">
                <div class="layui-upload">
                  <button type="button" class="layui-btn layui-btn-normal" id="'.$name.'List">选择多文件</button>
                  <div class="layui-upload-list">
                    <table class="layui-table">
                      <thead>
                        <tr><th>文件名</th>
                        <th>预览</th>
                        <th>大小</th>
                        <th>状态</th>
                        <th>描述</th>
                        <th>操作</th>
                      </tr></thead>
                      <tbody id="demoList"></tbody>
                    </table>
                  </div>
                  <button type="button" class="layui-btn" id="'.$name.'ListAction">开始上传</button>
                </div>';

          if(isset($field['#description'])){
            $this->form .= '<span class="description">'.$field['#description'].'</span>';
          }

          $this->form .= '</div></div>';
        }else{
          $this->form .= '
            <div class="layui-form-item">
              <label class="layui-form-label">'.$field['#title'].'</label>
              <div class="layui-input-block">
                <input type="text" id="'.$name.'"'.hunter_attributes($field['#attributes']).'>';

          if(!isset($field['#disabled']) || (isset($field['#disabled']) && !$field['#disabled'])){
            if($field['#type'] == 'file'){
              $this->form .= '<button type="button" class="layui-btn" id="'.$name.'btn">'.t('上传文件').'</button>';
            }else {
              $this->form .= '<button type="button" class="layui-btn" id="'.$name.'btn">'.t('上传图片').'</button>';
            }
          }

          if(isset($field['#description'])){
            $this->form .= '<span class="description">'.$field['#description'].'</span>';
          }

          $this->form .= '</div></div>';
        }

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * generate a hidden input
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function hidden($name, $value) {
        $this->form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

        return $this;
    }

    /**
     * generate a new input
     *
     * @param string $type
     * @param array $field
     * @return $this
     */
    public function input($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '<div class="layui-form-item"><label class="layui-form-label">'.$field['#title'].'</label> <div class="layui-input-block"><input class="layui-input" '.hunter_attributes($field['#attributes']).'>';
        if(isset($field['#description'])){
          $this->form .= '<span class="description">'.$field['#description'].'</span>';
        }

        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a textarea
     *
     * @param string $name
     * @param array field
     * @return $this
     */
    public function textarea($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        if(isset($field['#default_value'])){
          $this->form .= '<div class="layui-form-item layui-form-text"><label class="layui-form-label">'.$field['#title'].'</label><div class="layui-input-block"><textarea' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
        }else {
          $this->form .= '<div class="layui-form-item layui-form-text"><label class="layui-form-label">'.$field['#title'].'</label><div class="layui-input-block"><textarea' . hunter_attributes($field['#attributes']) . '></textarea>';
        }

        if(isset($field['#description'])){
          $this->form .= '<span class="description">'.$field['#description'].'</span>';
        }
        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     *
     * @return $this
     */
    public function img($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="layui-form-item">
            <label class="layui-form-label">'.$field['#title'].'</label>
            <div class="layui-input-block">
                <img src="'.$field['#default_value'].'"'.hunter_attributes($field['#attributes']).'>
            </div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a captcha
     *
     * @return $this
     */
    public function captcha($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="layui-form-item">
            <label class="layui-form-label">'.$field['#title'].'</label>
            <div class="layui-input-inline" style="margin-left:10px;">
              <input type="text" name="_captcha" id="'.$name.'" class="layui-input">
            </div>
            <div class="captcha">
            <img class="" src="/captcha/make" onclick="this.src=\'/captcha/make?ver=\'+new Date().getTime()" title="'.t('点击刷新验证码').'">
            </div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a fieldset
     *
     * @return $this
     */
    public function fieldset($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <fieldset class="layui-elem-field layui-field-title'.hunter_attributes($field['#attributes']).'" style="margin-top: 20px;">
          <legend>'.$field['#title'].'</legend>
        </fieldset>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     *
     * @param string $src
     * @param array $field
     * @return $this
     */
    public function markup($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        if(isset($field['#title'])){
          if(isset($field['#hidden']) && $field['#hidden']){
            $this->form .= '
            <div class="layui-form-item" id="'.$name.'" style="display:none;">';
          }else {
            $this->form .= '
            <div class="layui-form-item">';
          }

          $this->form .= '<label class="layui-form-label">'.$field['#title'].'</label>
              <div class="layui-input-block">
                  '.$field['#markup'].'
              </div>
          </div>';
        }else {
          $this->form .= $field['#markup'];
        }

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * generate a submit button
     *
     * @param $text
     * @param $class
     * @param null $icon
     * @return $this
     */
    public function submit($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '<div class="layui-form-item"><div class="layui-input-block"><button class="layui-btn" '.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button></div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a select
     *
     * @param string $name
     * @param array $options
     * @param string $icon
     * @return $this
     */
    public function select($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '<div class="layui-form-item"> <label class="layui-form-label">'.$field['#title'].'</label> <div class="layui-input-block"><select'.hunter_attributes($field['#attributes']).'>';

        foreach ($field['#options'] as $v => $title)
        {
          if($v == $field['#value']){
            $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
          }else{
            $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
          }
        }

        $this->form .= '  </select>';

        if(isset($field['#description'])){
          $this->form .= '<span class="description">'.$field['#description'].'</span>';
        }

        $this->form .= ' </div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * create a checkbox
     *
     * @param string $name
     * @param string $field
     * @return $this
     */
    public function checkbox($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="layui-form-item" pane="">
            <label class="layui-form-label">'.$field['#title'].'</label>
            <div class="layui-input-block">';

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            if(isset($field['#attributes']['lay-skin']) && $field['#attributes']['lay-skin'] != 'switch'){
              $field['#attributes']['value'] = $v;
            }
            $field['#attributes']['name'] = $name.'['.$v.']';
            $field['#attributes']['title'] = $title;
            if((is_array($field['#value']) && in_array($v, $field['#value'])) || (isset($field['#attributes']['value']) && $field['#value'] == $field['#attributes']['value'])){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }
          }
        }

        $this->form .= '</div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * create a radio input
     *
     * @param string $name
     * @param string $field
     * @return $this
     */
    public function radio($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="layui-form-item" pane="">
            <label class="layui-form-label">'.$field['#title'].'</label>
            <div class="layui-input-block">';

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $field['#attributes']['value'] = $v;
            $field['#attributes']['title'] = $title;
            if($v == $field['#value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }
          }
        }

        $this->form .= '</div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * close the form
     *
     * @return string
     */
    public function end($form_id) {
       if(!empty($form_id)){
         $this->form .= '<input type="hidden" name="form_id" value="'.$form_id.'">';
       }

       $this->form .= csrf_field();
       $this->form .= '</form>';

       return $this->form;
    }

}
