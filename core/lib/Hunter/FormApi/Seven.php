<?php

namespace Hunter\Core\FormApi;

class Seven {
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
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" '.$class.'" enctype="multipart/form-data">';
            break;
            default :
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" '.$class.'>';
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
          <div class="form-item" id="'.$name.'SimpleUploader">
            <label class="form-label">'.$field['#title'].'</label>
            <div class="form-text-block simple-upload">
              <div class="button button--small">'.t('选择文件').'
                <input type="file" name="uploadfile" id="'.$name.'Upload" onClick="simpleUploader(\''.$name.'\')" class="liteupload" multiple="multiple">
              </div>
            </div>
            <div class="uploader-img-list" id="imagePreview"></div>
          </div>';
        }else{
          $this->form .= '
            <div class="form-item">
              <label class="form-label">'.$field['#title'].'</label>
              <div class="form-text-block" id="'.$name.'SimpleUploader">
                <input type="text" class="form-text" size="60" id="single-img"'.hunter_attributes($field['#attributes']).'>
                <div class="sigle-upload">';

          if(!isset($field['#disabled']) || (isset($field['#disabled']) && !$field['#disabled'])){
            if($field['#type'] == 'file'){
              $this->form .= '<button type="button" class="btn" id="'.$name.'btn">'.t('上传文件').'</button>';
            }else {
              $this->form .= '<button type="button" class="btn" id="'.$name.'btn">'.t('上传图片').'</button>';
            }
          }

          $this->form .= '<input type="file" name="uploadfile" id="'.$name.'Upload" onClick="simpleUploader(\''.$name.'\')" class="liteupload"></div>';
          $this->form .= '<div class="uploader-img-list" id="imagePreview">';
          if(isset($field['#default_value']) && !empty($field['#default_value'])) {
            $this->form .= '<div class="item"><img src="'.$field['#default_value'].'" class="upload-img"></div>';
          }
          $this->form .= '</div>';

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
        $this->form .= '<div class="form-item"><label class="form-label">'.$field['#title'].'</label> <div class="form-text-block"><input class="form-text" size="60" '.hunter_attributes($field['#attributes']).'>';
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
          $this->form .= '<div class="form-item"><label class="form-label">'.$field['#title'].'</label><div class="form-textarea-wrapper"><textarea class="form-textarea resize-vertical"' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
        }else {
          $this->form .= '<div class="form-item"><label class="form-label">'.$field['#title'].'</label><div class="form-textarea-wrapper"><textarea class="form-textarea resize-vertical"' . hunter_attributes($field['#attributes']) . '></textarea>';
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
        <div class="form-item">
            <label class="form-label">'.$field['#title'].'</label>
            <div class="form-text-block">
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
        <div class="form-item">
            <label class="form-label">'.$field['#title'].'</label>
            <div class="form-text-inline" style="margin-left:10px;">
              <input type="text" name="_captcha" id="'.$name.'" class="form-text">
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
            <div class="form-item" id="'.$name.'" style="display:none;">';
          }else {
            $this->form .= '
            <div class="form-item">';
          }

          $this->form .= '<label class="form-label">'.$field['#title'].'</label>
              <div class="form-text-block">
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
        $this->form .= '<div class="form-item"><div class="form-text-block"><button class="button button--primary js-form-submit form-submit" '.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button></div></div>';

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
        $this->form .= '<div class="form-item"> <label class="form-label">'.$field['#title'].'</label> <div class="form-text-block"><select'.hunter_attributes($field['#attributes']).'>';

        foreach ($field['#options'] as $v => $title) {
          if(is_array($field['#value'])) {
            if(in_array($v, $field['#value'])){
              $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
            }else{
              $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
            }
          }else {
            if($v == $field['#value']){
              $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
            }else{
              $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
            }
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
        <div class="form-item" pane="">
            <label class="form-label">'.$field['#title'].'</label>
            <div class="form-text-block">';

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $field['#attributes']['name'] = $name.'['.$v.']';
            $field['#attributes']['title'] = $title;
            if((is_array($field['#value']) && in_array($v, $field['#value'])) || (isset($field['#attributes']['value']) && $field['#value'] == $field['#attributes']['value'])){
              $field['#attributes']['checked'] = 'checked';
              if(isset($field['#disable_selected']) && $field['#disable_selected']) {
                $field['#attributes']['disabled'] = 'disabled';
                $this->form .= '<label class="form-type-checkbox form-disabled">';
              }else {
                $this->form .= '<label class="form-type-checkbox">';
              }

              $this->form .= '<input class="a-checkbox"'.hunter_attributes($field['#attributes']).'><span class="b-checkbox"></span>'.$title;
            }else {
              unset($field['#attributes']['checked']);
              unset($field['#attributes']['disabled']);
              $this->form .= '<label class="form-type-checkbox">';
              $this->form .= '<input class="a-checkbox"'.hunter_attributes($field['#attributes']).'><span class="b-checkbox"></span>'.$title;
            }
            $this->form .= '</label>';
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
        <div class="form-item" pane="">
            <label class="form-label">'.$field['#title'].'</label>
            <div class="form-text-block">';

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $this->form .= '<label class="form-type-radio">';
            $field['#attributes']['value'] = $v;
            $field['#attributes']['title'] = $title;
            if($v == $field['#value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input class="a-radio"'.hunter_attributes($field['#attributes']).'><span class="b-radio"></span>'.$title;
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input class="a-radio"'.hunter_attributes($field['#attributes']).'><span class="b-radio"></span>'.$title;
            }
            $this->form .= '</label>';
          }
        }

        $this->form .= '</div></div>';

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
