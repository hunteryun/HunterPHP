<?php

namespace Hunter\Core\FormApi;

use Gregwar\Captcha\CaptchaBuilder;

class Bootstrap {

    private $form;

    /**
     * start the form
     */
    public function start($action, $id = null, $class = null, $enctype = false, $method = 'post', $charset = 'utf8') {
        switch ($enctype)
        {
            case true:
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="'. $charset.'" class="form-horizontal '.$class.'" enctype="multipart/form-data">';
            break;
            default :
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="'. $charset.'" class="form-horizontal '.$class.'">';
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

        $this->form .= '
          <div class="form-group">
            <label class="col-sm-2 control-label" class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-10">
            <input type="file" id="'.$name.'">';

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

        $this->form .= '<div class="form-group"><label class="col-sm-2 control-label">'.$field['#title'].'</label> <div class="col-sm-10"><input class="form-control" '.hunter_attributes($field['#attributes']).'>';
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

        if(isset($field['#default_value'])){
          $this->form .= '<div class="form-group"><label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10"><textarea class="form-control" ' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
        }else {
          $this->form .= '<div class="form-group"><label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-10"><textarea class="form-control" ' . hunter_attributes($field['#attributes']) . '></textarea>';
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
        $builder = new CaptchaBuilder;
        $builder->build($width = 100, $height = 38);
        session()->set('_captcha', $builder->getPhrase());
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
              <input type="text" name="_captcha" id="'.$name.'" class="form-control">
            <div class="captcha"><img src="'.$builder->inline().'"'.hunter_attributes($field['#attributes']).'></div>
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

          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label>
            '.$field['#markup'].'
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
     */
    public function submit($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '<div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button class="btn btn-primary" '.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button></div></div>';

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
        $this->form .= '<div class="form-group"> <label class="col-sm-2 control-label">'.$field['#title'].'</label> <select'.hunter_attributes($field['#attributes']).'>';

        foreach ($field['#options'] as $v => $title)
        {
          if($v == $field['#value']){
            $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
          }else{
            $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
          }
        }

        $this->form .= '  </select></div>';

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
        $this->form .= '
        <div class="form-group" pane="">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-10">';

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
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-10">';

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
