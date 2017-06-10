<?php

namespace effectivecore {
          use \effectivecore\file_factory as files;
          use \effectivecore\message_factory as messages;
          const settings_cache_file_name      = 'cache--settings.php';
          const settings_cache_file_name_orig = 'cache--settings--original.php';
          class storage_instance_s {

  static $data_orig;
  static $data;

  static function init() {
    $file = new file(dir_dynamic.settings_cache_file_name);
    if ($file->is_exist()) {
      $file->insert();
    } else {
      $data_orig       = static::settings_get_all();
      $data            = unserialize(serialize($data_orig)); # deep array clone
      $changes_static  = static::changes_get_static($data_orig);
      $changes_dynamic = static::changes_get_dynamic();
      static::changes_apply_to_settings($changes_static,  $data);
      static::changes_apply_to_settings($changes_dynamic, $data);
      static::$data = $data;
    # save cache
      if (is_writable(dir_dynamic)) {
        static::settings_save_cache($data_orig, settings_cache_file_name_orig, '  settings::$data_orig');
        static::settings_save_cache($data,      settings_cache_file_name,      '  settings::$data');
      } else {
        messages::add_new(
          'Can not save data to the directory "dynamic"!'.br.
          'Directory "dynamic" should be writable.'.br.
          'System is working slowly at now.', 'warning'
        );
      }
    }
  }

  ########################
  ### shared functions ###
  ########################

  function select($group = '') {
    if (!static::$data) static::init();
    if ($group)  return static::$data[$group];
    else         return static::$data;
  }

  function changes_insert_dynamic() {
    if (!static::$data) static::init();
  # ...
  }

  function changes_update_dynamic() {
    if (!static::$data) static::init();
  # ...
  }

  function changes_delete_dynamic() {
    if (!static::$data) static::init();
  # ...
  }

  ################
  ### settings ###
  ################

  static function settings_get_all() {
    $return = [];
    $files = files::get_all(dir_system, '%^.*\.data$%') +
             files::get_all(dir_modules, '%^.*\.data$%');
    $modules_path = [];
    foreach ($files as $c_file) {
      if ($c_file->get_file_full() == 'module.data') {
        $modules_path[$c_file->get_dir_parent()] = $c_file->get_dirs_relative();
      }
    }
    foreach ($files as $c_file) {
      $c_scope = 'global';
      foreach ($modules_path as $c_dir_parent => $c_dir_relative) {
        if (strpos($c_file->get_dirs_relative(), $c_dir_relative) === 0) {
          $c_scope = $c_dir_parent;
          break;
        }
      }
      $c_parsed = static::settings_to_code($c_file->load());
      foreach ($c_parsed as $c_type => $c_data) {
        if (is_object($c_data)) {
          if ($c_type == 'module') $c_data->path = $modules_path[$c_scope];
          $return[$c_type][$c_scope] = $c_data;
        }
        if (is_array($c_data)) {
          if (!isset($return[$c_type][$c_scope])) $return[$c_type][$c_scope] = [];
          $return[$c_type][$c_scope] += $c_data;
        }
      }
    }
    return $return;
  }

  static function settings_save_cache($data, $file_name, $prefix) {
    $file = new file(dir_dynamic.$file_name);
    $file->set_data(
      "<?php \n\nnamespace effectivecore { # ARRAY[type][scope]...\n\n  ".
        "use \\effectivecore\\storage_instance_s as settings;\n\n".
          factory::data_export($data, $prefix).
      "\n}");
    return $file->save();
  }

  ###############
  ### changes ###
  ###############

  static function changes_get_static($data) {
    $return = [];
    if (!empty($data['changes'])) {
      foreach ($data['changes'] as $c_module_id => $c_changes) {
        foreach ($c_changes as $c_id => $c_change) {
          $return[$c_change->action][$c_id] = $c_change;
          $return[$c_change->action][$c_id]->module_id = $c_module_id;
        }
      }
    }
    return $return;
  }

  static function changes_get_dynamic() {
    $return = [];
    $files = files::get_all(dir_dynamic, '%^.*\/changes(--.+|)\.data$%');
    foreach ($files as $c_file) {
      $c_parsed = static::settings_to_code($c_file->load());
      if (!empty($c_parsed->changes)) {
        foreach ($c_parsed->changes as $c_id => $c_change) {
          $return[$c_change->action][$c_id] = $c_change;
        }
      }
    }
    return $return;
  }

  static function changes_apply_to_settings($changes, &$data) {
    foreach ($changes as $changes_by_action) {
      foreach ($changes_by_action as $c_change) {
        $path_parts = explode('/', $c_change->npath);
        $child_name = array_pop($path_parts);
        $parent_obj = &factory::npath_get_pointer(implode('/', $path_parts), $data);
        switch ($c_change->action) {
          case 'insert':
            switch (gettype($parent_obj)) {
              case 'array' : $destination_obj = &$parent_obj[$child_name];   break;
              case 'object': $destination_obj = &$parent_obj->{$child_name}; break;
            }
            switch (gettype($destination_obj)) {
              case 'array' : foreach ($c_change->value as $key => $value) $destination_obj[$key]   = $value; break;
              case 'object': foreach ($c_change->value as $key => $value) $destination_obj->{$key} = $value; break;
            }
            break;
          case 'update':
            switch (gettype($parent_obj)) {
              case 'array' : $parent_obj[$child_name]   = $c_change->value; break;
              case 'object': $parent_obj->{$child_name} = $c_change->value; break;
            }
            break;
          case 'delete':
            switch (gettype($parent_obj)) {
              case 'array' : unset($parent_obj[$child_name]);   break;
              case 'object': unset($parent_obj->{$child_name}); break;
            }
            break;
        }
      }
    }
  }

  ##############
  ### parser ###
  ##############

  static function code_to_settings($data) {
  }

  static function settings_to_code($data) {
    $return = new \StdClass();
    $p = [-1 => &$return];
    foreach (explode(nl, $data) as $c_line) {
      $matches = [];
    # p.s. performance ~ 1'000'000 strings per second.
      preg_match('%(?<indent>[ ]*)'.
                  '(?<prefix>\- |)'.
                  '(?<name>[^\:\|]+)'.
                  '(?<class>\\|[a-z0-9_\\\\]+|)'.
                  '(?<delimiter>\: |)'.
                  '(?<value>.*|)%sS', $c_line, $matches);
      if ($matches['name']) {
        $depth = strlen($matches['indent'].$matches['prefix']) / 2;
        if ($matches['delimiter'] == ': ') {
          $value = $matches['value'];
          if (is_numeric($value)) $value += 0;
          if ($value === 'true')  $value = true;
          if ($value === 'false') $value = false;
          if ($value === 'null')  $value = null;
        } else {
          $class = !empty($matches['class']) ? str_replace('|', '\\effectivecore\\', $matches['class']) : '\StdClass';
          $value = new $class;
        }
      # add new item to tree
        if (is_array($p[$depth-1])) {
          $p[$depth-1][$matches['name']] = $value;
          $p[$depth] = &$p[$depth-1][$matches['name']];
        } else {
          $p[$depth-1]->{$matches['name']} = $value;
          $p[$depth] = &$p[$depth-1]->{$matches['name']};
        }
      # convert parent item to array
        if ($matches['prefix'] == '- ' && !is_array($p[$depth-1])) {
          $p[$depth-1] = (array)$p[$depth-1];
        }
      }
    }
    return $return;
  }

}}