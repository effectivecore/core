<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore {
          use \effectivecore\event_factory as event;
          use \effectivecore\message_factory as message;
          use \effectivecore\modules\user\session_factory as session;
          class form extends \effectivecore\markup {

  # elements support:
  # ─────────────────────────────────────────────────────────────────────
  # - textarea
  # - input[type=text]
  # - input[type=password]
  # - input[type=search]
  # - input[type=url]
  # - input[type=tel]
  # - input[type=email]
  # - select
  # - input[type=file]
  # - input[type=checkbox]
  # - input[type=radio]
  # - input[type=number]
  # - input[type=range]
  # - input[type=date]
  # - input[type=time]
  # - input[type=color]
  # - button[type=button]
  # - button[type=reset]
  # - button[type=submit]
  # - input[type=hidden]         : not processed
  # ─────────────────────────────────────────────────────────────────────

  # elements are not supported and not processed:
  # ─────────────────────────────────────────────────────────────────────
  # - input[type=button]         : use button[type=button] instead
  # - input[type=reset]          : use button[type=reset] instead
  # - input[type=submit]         : use button[type=submit] instead
  # - input[type=image]          : use imgage instead
  # - input[type=week]           : use week_macro instead
  # - input[type=month]          : use month_macro instead
  # - input[type=datetime]       : use date + time instead
  # - input[type=datetime-local] : use date + time instead
  # ─────────────────────────────────────────────────────────────────────

  # note:
  # ─────────────────────────────────────────────────────────────────────
  # 1. more info in \effectivecore\events_form
  # ─────────────────────────────────────────────────────────────────────

  public $tag_name = 'form';
  public $clicked_button = null;
  public $clicked_button_name = null;
  public $errors = [];

  function render() {
    $this->build();
    return parent::render();
  }

  function add_error($element_id = null, $message = null) {
    $this->errors[$element_id][] = $message;
  }

  function build() {
    $values = static::values_get() + static::files_get();
    $id = $this->attribute_select('id');
  # build all form elements
    $elements = $this->child_select_all();
    foreach ($elements as $c_element) {
      if (method_exists($c_element, 'build')) {
        $c_element->build();
      }
    }
  # renew elements list after build and get all fields
    $elements = $this->child_select_all();
    $fields   = $this->fields_get();
  # call init handlers
    event::start('on_form_init', $id, [$this, $fields]);
  # if current user click the button
    if (isset($values['form_id'][0]) &&
              $values['form_id'][0] === $id && isset($values['button'][0])) {
    # get more info about clicked button
      foreach ($elements as $c_element) {
        if ($c_element instanceof markup &&
            $c_element->tag_name == 'button' &&
            $c_element->attribute_select('type') == 'submit' &&
            $c_element->attribute_select('value') === $values['button'][0]) {
          $this->clicked_button      = $c_element;
          $this->clicked_button_name = $c_element->attribute_select('value');
          break;
        }
      }
    # call validate handlers
      if (empty($this->clicked_button->novalidate)) {
        event::start('on_form_validate', $id, [$this, $fields, &$values]);
      }
    # show errors and set error class
      foreach ($this->errors as $c_npath => $c_errors) {
        foreach ($c_errors as $c_error) {
          if ($c_npath) $elements[$c_npath]->attribute_insert('class', ['error' => 'error']);
          if ($c_error) message::add_new($c_error, 'error');
        }
      }
    # call submit handler (if no errors)
      if (count($this->errors) == 0) {
        event::start('on_form_submit', $id, [$this, $fields, &$values]);
      }
    }

  # add form_id to the form markup
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'validation_hash',
      'value' => static::validation_hash_get($values),
      ]), 'hidden_validation_hash');
    $this->child_insert(new markup_simple('input', [
      'type'  => 'hidden',
      'name'  => 'form_id',
      'value' => $id,
    ]), 'hidden_form_id');
  }

  function fields_get() {
    $return = [];
    foreach ($this->child_select_all() as $c_npath => $c_child) {
      if ($c_child instanceof \effectivecore\form_container) {
        $return[$c_npath] = $c_child;
      }
    }
    return $return;
  }

  static function validation_hash_generate() {
    return md5(session::id_get().rand(0, PHP_INT_MAX));
  }

  static function validation_hash_get($values) {
    $c_value = filter_var(isset($values['validation_hash'][0]) ?
                                $values['validation_hash'][0] : '', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '%^[0-9a-f]{32}$%']]);
    if ($c_value) return $c_value;
    else          return static::validation_hash_generate();
  }

  static function values_get() {
    $return = [];
    # conversion matrix (expected: string|array):
    # ─────────────────────────────────────────────────────────────────────
    # - $_POST[name] == ''                  -> return [0 => '']
    # - $_POST[name] == 'value'             -> return [0 => 'value']
    # ─────────────────────────────────────────────────────────────────────
    # - $_POST[name] == [0 => '']           -> return [0 => '']
    # - $_POST[name] == [0 => '', ...]      -> return [0 => '', ...]
    # - $_POST[name] == [0 => 'value']      -> return [0 => 'value']
    # - $_POST[name] == [0 => 'value', ...] -> return [0 => 'value', ...]
    # ─────────────────────────────────────────────────────────────────────
    foreach ($_POST as $c_field => $c_value) {
      $return[$c_field] = is_array($c_value) ?
                                   $c_value : [$c_value];
    }
    return $return;
  }

  static function files_get() {
    $return = [];
    # conversion matrix (expected: string|array):
    # ─────────────────────────────────────────────────────────────────────
    # - $_FILES[name] == '',                 -> ignored empty
    # - $_FILES[name] == 'value'             -> return [name => [0 => 'value']]
    # ─────────────────────────────────────────────────────────────────────
    # - $_FILES[name] == [0 => '']           -> ignored empty
    # - $_FILES[name] == [0 => '', ...]      -> ignored empty
    # - $_FILES[name] == [0 => 'value']      -> return [name => [0 => 'value']]
    # - $_FILES[name] == [0 => 'value', ...] -> return [name => [0 => 'value', ...]]
    # ─────────────────────────────────────────────────────────────────────
    foreach ($_FILES as $c_field => $c_info) {
      if (!is_array($c_info['name']))     $c_info['name']     = [$c_info['name']];
      if (!is_array($c_info['type']))     $c_info['type']     = [$c_info['type']];
      if (!is_array($c_info['size']))     $c_info['size']     = [$c_info['size']];
      if (!is_array($c_info['tmp_name'])) $c_info['tmp_name'] = [$c_info['tmp_name']];
      if (!is_array($c_info['error']))    $c_info['error']    = [$c_info['error']];
      foreach ($c_info as $c_prop => $c_values) {
        foreach ($c_values as $c_index => $c_value) {
          if ($c_info['error'][$c_index] !== UPLOAD_ERR_NO_FILE) {
            if (!isset($return[$c_field][$c_index]))
                       $return[$c_field][$c_index] = new \stdClass();
            $return[$c_field][$c_index]->{$c_prop} = $c_value;
          }
        }
      }
    }
    return $return;
  }

}}