<?php

namespace Hunter\Core\FormApi;

use Hunter\Core\FormApi\Form;

class NoHtml extends Form {
    /**
     * the html form code
     *
     * @var string $form
     */
    private $form;

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
    public function start($action, $id = null, $class = null, $enctype = false, $method = Form::POST, $charset = 'utf8')
    {
        switch ($enctype)
        {
            case true:
                $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="'. $charset.'" class="layui-form '.$class.'" enctype="multipart/form-data">';
            break;
            default :
                $this->form =  $this->form = '<form action="'.$action.'" id="'.$id.'" method="'. $method.'" accept-charset="'. $charset.'" class="layui-form '.$class.'">';
            break;
        }
        return $this;

    }

    /**
     * create a div to hide inputs
     *
     * @return $this
     */
    public function startHide()
    {
        $this->form .= '<div class="hidden">';

        return $this;
    }

    /**
     * close the hide div
     *
     * @return $this
     */
    public function endHide()
    {

        $this->form .= '</div>';

        return $this;
    }

    /**
     * generate a file input
     *
     * @param $name
     * @param $class
     * @param $text
     * @param null $ico
     * @return $this
     */
    public function file($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        $this->form .= '<input type="text" id="'.$name.'"'.hunter_attributes($field['#attributes']).'>
          <input type="file" name="file" class="layui-upload-file">';

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
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
    public function hidden($name, $value)
    {
        $this->form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

        return $this;
    }

    /**
     * generate a new input
     *
     * @param string $type
     * @param string $name
     * @param string $placeholder
     * @param string $value
     * @param string $icon
     * @param bool $required
     * @param bool $autofocus
     * @param string $autoComplete
     * @return $this
     */
    public function input($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        $this->form .= '<input '.hunter_attributes($field['#attributes']).'>';

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * the framework csrf field
     *
     * @param string
     * @return $this
     */
    public function csrf($csrf)
    {
        $this->form .= $csrf;

        return $this;
    }

    /**
     * generate a new button
     *
     * @param string $text
     * @param string $class
     * @param string $type
     * @param string $icon
     * @return $this
     */
    public function button($text, $class, $type = Form::BUTTON, $icon = null)
    {
        switch ($type)
        {
            case Form::BUTTON:
                $this->form .='<button class="'.$class.'" type="button"> <i class="'.$icon.'"></i>' .$text.'</button>';
                break;
            case Form::RESET:
                $this->form .='<button class="'.$class.'" type="reset"> <i class="'.$icon.'"></i>' .$text.'</button>';
                break;
            case Form::SUBMIT:
                $this->form .='<button class="'.$class.'" type="submit"> <i class="'.$icon.'"></i>' .$text.'</button>';
                break;
            default :
                $this->form .='<button class="'.$class.'" type="button"> <i class="'.$icon.'"></i>' .$text.'</button>';
            break;

        }

        return $this;
    }

    /**
     * generate a reset button
     *
     * @param $text
     * @param $class
     * @param null $icon
     * @return mixed
     */
    public function reset($text, $class, $icon = null)
    {
        $this->form .= '<button class="'.$class.'" type="reset"> <i class="'.$icon.'"> </i> ' .$text.'</button>';

        return $this;
    }

    /**
     * generate a textarea
     *
     * @param string $name
     * @param string $placeholder
     * @param integer $cols
     * @param integer $row
     * @param bool $autofocus
     * @return $this
     */
    public function textarea($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        if(isset($field['#default_value'])){
          $this->form .= '<textarea' . hunter_attributes($field['#attributes']) . '>'.$field['#default_value'].'</textarea>';
        }else {
          $this->form .= '<textarea' . hunter_attributes($field['#attributes']) . '></textarea>';
        }

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * generate a image
     *
     * @param string $src
     * @param string $alt
     * @param string $width
     * @param string $class
     * @return $this
     */
    public function img($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        $this->form .= '<img src="'.$field['#default_value'].'"'.hunter_attributes($field['#attributes']).'>';

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
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
    public function submit($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<button'.hunter_attributes($field['#attributes']).'> '.$field['#value'].'</button>';

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * generate a form link
     *
     * @param $url
     * @param $class
     * @param null $icon
     * @param string $text
     * @return $this
     */
    public function link($url, $class, $text, $icon = null)
    {
        $this->form .= '<a href="'.$url.'" class="'.$class.'"> <i class="'. $icon .'"></i>  '.$text .'</a>';

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
    public function select($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        $this->form .= '<select'.hunter_attributes($field['#attributes']).'>';

        foreach ($field['#options'] as $v => $title)
        {
          if($v == $field['#value']){
            $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
          }else{
            $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
          }
        }

        $this->form .= '</select>';

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * create a checkbox
     *
     * @param string $name
     * @param string $text
     * @param string $class
     * @param bool $checked
     * @return $this
     */
    public function checkbox($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
            $field['#attributes']['title'] = $title;
            if(is_array($field['#value']) && in_array($v, $field['#value'])){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<input'.hunter_attributes($field['#attributes']).'>';
            }
          }
        }

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * create a radio input
     *
     * @param string $name
     * @param string $text
     * @param string $class
     * @param bool $checked
     * @return $this
     */
    public function radio($name, $field)
    {
        if(!empty($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!empty($field['#label_prefix'])){
          $this->form .= $field['#label_prefix'];
        }

        $this->form .= '<label>'.$field['#title'].'</label>';

        if(!empty($field['#label_suffix'])){
          $this->form .= $field['#label_suffix'];
        }

        if(!empty($field['#field_prefix'])){
          $this->form .= $field['#field_prefix'];
        }

        if(!empty($field['#options'])){
          foreach ($field['#options'] as $v => $title) {
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

        if(!empty($field['#field_suffix'])){
          $this->form .= $field['#field_suffix'];
        }

        if(!empty($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * close the form
     *
     * @return string
     */
    public function end($form_id)
    {
       if(!empty($form_id)){
         $this->form .= '<input type="hidden" name="form_id" value="'.$form_id.'">';
       }

       $this->form .= '</form>';

       return $this->form;
    }

    /**
     * generate two inline input
     *
     * @param $typeOne
     * @param $nameOne
     * @param $placeholderOne
     * @param $valueOne
     * @param $iconOne
     * @param $typeTwo
     * @param $nameTwo
     * @param $placeholderTwo
     * @param $valueTwo
     * @param $iconTwo
     * @return $this
     */
    public function twoInlineInput($typeOne, $nameOne, $placeholderOne, $valueOne, $iconOne, $typeTwo, $nameTwo, $placeholderTwo, $valueTwo, $iconTwo)
    {
        $this->form .= '<div class="row">';

            $this->form .= '<div class="col-md-6">';
                $this->input($typeOne,$nameOne,$placeholderOne,$valueOne,$iconOne);
            $this->form .= '</div>';

            $this->form .= '<div class="col-md-6">';
                $this->input($typeTwo,$nameTwo,$placeholderTwo,$valueTwo,$iconTwo);
            $this->form .= '</div>';

        $this->form .= '</div>';

        return $this;
    }

    /**
     * generate two inline select
     *
     * @param $nameOne
     * @param array $optionsOne
     * @param $iconOne
     * @param $nameTwo
     * @param array $optionsTwo
     * @param $iconTwo
     * @return $this
     */
    public function twoInlineSelect($nameOne, array $optionsOne, $iconOne, $nameTwo, array $optionsTwo, $iconTwo)
    {
        $this->form .= '<div class="row">';

            $this->form .= '<div class="col-md-6">';
                $this->select($nameOne,$optionsOne,$iconOne);
            $this->form .= '</div>';

            $this->form .= '<div class="col-md-6">';
                $this->select($nameTwo,$optionsTwo,$iconTwo);
            $this->form .= '</div>';

        $this->form .= '</div>';

        return $this;
    }

    /**
     * select with redirect
     *
     * @param $name
     * @param array $options
     * @param $icon
     * @return $this
     */
    public function redirectSelect($name, array $options, $icon)
    {
        $this->form .= '<div class="layui-form-item"> <span class="input-group-addon"><i class="'.$icon.'"></i></span> <select class="form-control"  name="'.$name.'" id="'.$name.'" size="1"  onChange="location = this.options[this.selectedIndex].value">';

        foreach ($options as $k=>  $value)
        {
            $this->form .= ' <option value="'.$k.'" class="form-control"> '.$value.'</option>';
        }

        $this->form .= '  </select></div>';

        return $this;
    }

    /**
     * two select with redirect
     *
     * @param $nameOne
     * @param array $optionsOne
     * @param $iconOne
     * @param $nameTwo
     * @param array $optionsTwo
     * @param $iconTwo
     * @return $this
     */
    public function twoRedirectSelect($nameOne, array $optionsOne, $iconOne, $nameTwo, array $optionsTwo, $iconTwo)
    {

        $this->form .= '<div class="row">';

            $this->form .= '<div class="col-md-6">';
                $this->redirectSelect($nameOne,$optionsOne,$iconOne);
            $this->form .= '</div>';

            $this->form .= '<div class="col-md-6">';
                $this->redirectSelect($nameTwo,$optionsTwo,$iconTwo);
            $this->form .= '</div>';

        $this->form .= '</div>';

        return $this;

    }

    /**
     * generate one select and one input
     *
     * @param $selectName
     * @param array $options
     * @param $selectIcon
     * @param $type
     * @param $name
     * @param $placeholder
     * @param null $value
     * @param null $icon
     * @return $this
     */
    public function OneSelectAndOneInput($selectName, array $options, $selectIcon, $type, $name, $placeholder, $value = null, $icon = null)
    {
        $this->form .= '<div class="row">';
            $this->form .= '<div class="col-md-6">';
                $this->select($selectName,$options,$selectIcon);
            $this->form .= '</div>';
            $this->form .= '<div class="col-md-6">';
                $this->input($type,$name,$placeholder,$value,$icon);
            $this->form .= '</div>';
        $this->form .= '</div>';

        return $this;
    }

    /**
     * generate one input and one select
     *
     * @param $type
     * @param $name
     * @param $placeholder
     * @param null $value
     * @param null $icon
     * @param $selectName
     * @param array $options
     * @param $selectIcon
     * @return $this
     */
    public function OneInputAndOneSelect($type, $name, $label, $placeholder, $value = null, $icon = null, $selectName, array $options, $selectIcon)
    {
        $this->form .= '<div class="row">';
            $this->form .= '<div class="col-md-6">';
                $this->input($type,$name,$placeholder,$value,$icon);
            $this->form .= '</div>';
            $this->form .= '<div class="col-md-6">';
                 $this->select($selectName,$options,$selectIcon);
            $this->form .= '</div>';
        $this->form .= '</div>';

        return $this;
    }
}
