<?php

namespace effectivecore {
          abstract class events_module extends events {

  static function on_init() {
    require_once('Cache.php');
    require_once('Factory.php');
    require_once('Files.php');
    require_once('File.php');
    spl_autoload_register('\effectivecore\factory::autoload');
    settings::init();
    translate::init();
    token::init();
    urls::init();
    events::init();
    core_factory::init();
  # init modules
    ob_start();
    console::set_log('init_core', '\effectivecore\events_module::on_init', 'Init calls');
    foreach (static::$data->on_init as $c_id => $c_event) {
      console::set_log($c_id, $c_event->handler, 'Init calls');
      call_user_func($c_event->handler);
    }
  }

}}