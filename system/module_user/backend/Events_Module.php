<?php

  #############################################################
  ### Copyright © 2017 Maxim Rysevets. All rights reserved. ###
  #############################################################

namespace effectivecore\modules\user {
          use \effectivecore\entity as entity;
          use \effectivecore\factory as factory;
          use \effectivecore\instance as instance;
          use \effectivecore\translation as translation;
          use \effectivecore\message_factory as message;
          use \effectivecore\modules\user\user as user;
          use \effectivecore\modules\user\session_factory as session;
          abstract class events_module extends \effectivecore\events_module {

  static function on_start() {
    $session = session::select();
    if ($session &&
        $session->id_user) {
      user::init($session->id_user);
    }
  }

  static function on_install() {
  # install entities
    foreach (entity::get_all_by_module('user') as $c_entity) {
      if ($c_entity->install()) message::insert(translation::get('Entity %%_name was installed.',     ['name' => $c_entity->get_name()]));
      else                      message::insert(translation::get('Entity %%_name was not installed!', ['name' => $c_entity->get_name()]), 'error');
    }
  # insert instances
    foreach (instance::get_by_module('user') as $c_instance) {
      if ($c_instance->entity_name == 'user') $c_instance->created = factory::datetime_get();
      if ($c_instance->insert()) message::insert(translation::get('Instances of entity %%_name was added.',     ['name' => $c_entity->get_name()]));
      else                       message::insert(translation::get('Instances of entity %%_name was not added!', ['name' => $c_entity->get_name()]), 'error');
    }
  }

}}