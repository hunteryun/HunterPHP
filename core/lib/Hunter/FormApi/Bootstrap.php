<?php

namespace Hunter\Core\FormApi;

class Bootstrap {

    private $form;
    private $form_show_type;

    /**
     * start the form
     */
    public function start($action, $id = null, $class = null, $show_type = 'block', $enctype = false, $method = 'post') {
        $this->form_show_type = $show_type;
        switch ($enctype)
        {
            case true:
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" class="form-horizontal '.$class.'" enctype="multipart/form-data">';
            break;
            default :
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="utf8" class="form-horizontal '.$class.'">';
            break;
        }
        return $this;
    }

    /**
     * generate a file input
     */
    public function file($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          $this->form .= '<div class="col-sm-6">
          <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">'.$field['#title'].'</span>
            <input type="text" class="form-control" id="'.$name.'"'.hunter_attributes($field['#attributes']).' aria-describedby="basic-addon1">
          </div>
          </div>
          <div class="col-sm-2" style="padding:0;">
          <a href="javascript:;" class="file btn btn-primary">'.t('选择').'
            <input type="file" name="uploadfile" class="liteupload" multiple="multiple">
          </a>';
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-6"><input type="text" class="form-control" id="'.$name.'"'.hunter_attributes($field['#attributes']).'>
          </div>
          <div class="col-sm-2" style="padding:0;">
          <a href="javascript:;" class="file btn btn-primary">'.t('选择').'
            <input type="file" name="uploadfile" class="liteupload" multiple="multiple">
          </a>';
        }

        if(isset($field['#description'])){
          $this->form .= '<p class="help-block">'.$field['#description'].'</p>';
        }

        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * generate a hidden input
     */
    public function hidden($name, $value) {
        $this->form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

        return $this;
    }

    /**
     * generate a new input
     */
    public function input($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel'){
          $field['#attributes']['placeholder'] = $field['#title'];
          $this->form .= '<div class="col-sm-6"><input class="form-control" '.hunter_attributes($field['#attributes']).'>';
        }elseif ($this->form_show_type == 'inline') {
          $this->form .= '<div class="col-sm-6"><div class="input-group"><span class="input-group-addon" id="basic-addon1">'.$field['#title'].'</span><input class="form-control" '.hunter_attributes($field['#attributes']).' aria-describedby="basic-addon1"></div>';
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10"><input class="form-control" '.hunter_attributes($field['#attributes']).'>';
        }

        if(isset($field['#description'])){
          $this->form .= '<span class="help-block">'.$field['#description'].'</span>';
        }

        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a textarea
     */
    public function textarea($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!isset($field['#attributes']['rows'])){
          $field['#attributes']['rows'] = 6;
        }
        $this->form .= '<div class="form-group">';
        if(isset($field['#default_value'])){
          if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
            $field['#attributes']['placeholder'] = $field['#title'];
            $this->form .= '<div class="col-sm-10"><textarea ' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
          }else {
            $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10"><textarea ' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
          }
        }else {
          if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
            $field['#attributes']['placeholder'] = $field['#title'];
            $this->form .= '<div class="col-sm-12"><textarea ' . hunter_attributes($field['#attributes']) . '></textarea>';
          }else {
            $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10"><textarea ' . hunter_attributes($field['#attributes']) . '></textarea>';
          }
        }

        if(isset($field['#description'])){
          $this->form .= '<span class="help-block">'.$field['#description'].'</span>';
        }
        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     */
    public function img($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-10">
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
     */
    public function captcha($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-3">
            <input type="text" name="_captcha" id="'.$name.'" class="form-control">
            </div>
            <div class="col-sm-3">
            <div class="captcha">
            <img class="" src="/captcha/make" onclick="this.src=\'/captcha/make?ver=\'+new Date().getTime()" title="'.t('点击刷新验证码').'">
            </div>
            </div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a fieldset
     */
    public function fieldset($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <fieldset class="'.hunter_attributes($field['#attributes']).'" style="margin-top: 20px;">
          <legend>'.$field['#title'].'</legend>
        </fieldset>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     */
    public function markup($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        if(isset($field['#title'])){
          if(isset($field['#hidden']) && $field['#hidden']){
            $this->form .= '
            <div class="form-group" id="'.$name.'" style="display:none;">';
          }else {
            $this->form .= '
            <div class="form-group">';
          }

          if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
            $this->form .= '<div class="col-sm-6">'.$field['#markup'].'
            </div></div>';
          }else {
            $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label>
              '.$field['#markup'].'
            </div>';
          }
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
     */
    public function submit($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          $this->form .= '<div class="form-group"><div class="col-sm-2"><button class="btn btn-primary" '.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button></div></div>';
        }else {
          $this->form .= '<div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button class="btn btn-primary" '.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button></div></div>';
        }

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a select
     */
    public function select($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel'){
          $this->form .= '<div class="col-sm-6"><select class="form-control"'.hunter_attributes($field['#attributes']).'>';
          $this->form .= ' <option value=""> '.t('请选择').$field['#title'].'</option>';
        }elseif ($this->form_show_type == 'inline') {
          $this->form .= '<div class="col-sm-6"><div class="input-group">
          <span class="input-group-addon" id="basic-addon1">'.$field['#title'].'</span>
          <select class="form-control"'.hunter_attributes($field['#attributes']).' aria-describedby="basic-addon1">';
          $this->form .= ' <option value=""> '.t('请选择').'</option>';
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label> <div class="col-sm-10"><select class="form-control"'.hunter_attributes($field['#attributes']).'>';
          $this->form .= ' <option value=""> '.t('请选择').'</option>';
        }

        foreach ($field['#options'] as $v => $title)
        {
          if($v == $field['#value']){
            $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
          }else{
            $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
          }
        }

        $this->form .= '  </select></div>';
        if($this->form_show_type == 'inline'){
          $this->form .= '</div>';
        }
        $this->form .= '</div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * create a checkbox
     */
    public function checkbox($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          $this->form .= '<div class="col-sm-12">';
          if(!isset($field['#attributes']['hide-label'])){
            $this->form .= ' <div class="title">'.t('请选择').$field['#title'].'</div>';
          }
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10">';
        }

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $this->form .= '<label class="checkbox-inline">';
            $field['#attributes']['value'] = $v;
            $field['#attributes']['name'] = $name.'['.$v.']';
            if((is_array($field['#value']) && in_array($v, $field['#value'])) || $field['#value'] == $field['#attributes']['value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>'.$title;
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>'.$title;
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
     * create a radio input
     */
    public function radio($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          $this->form .= '<div class="col-sm-12">';
          if(!isset($field['#attributes']['hide-label'])){
            $this->form .= ' <div class="title">'.t('请选择').$field['#title'].'</div>';
          }
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10">';
        }

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $this->form .= '<label class="radio-inline">';
            $field['#attributes']['value'] = $v;
            if($v == $field['#value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>'.$title;
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>'.$title;
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
