<?php

namespace effectivecore\modules\storage {
          use \effectivecore\settings;
          use \effectivecore\factory;
          abstract class events extends \effectivecore\events {

  static function on_init() {
    $is_init = db::init(
      settings::$data['db']['module_storage']->prod->driver,
      settings::$data['db']['module_storage']->prod->host,
      settings::$data['db']['module_storage']->prod->database_name,
      settings::$data['db']['module_storage']->prod->username,
      settings::$data['db']['module_storage']->prod->password,
      settings::$data['db']['module_storage']->prod->table_prefix
    );
    if (!$is_init) {
      factory::send_header_and_exit('access_denided',
        'Database is unavailable!'
      );
    }
  }

}}