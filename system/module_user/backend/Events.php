<?php

namespace effectivecore\modules\user {
          use \effectivecore\html;
          use \effectivecore\factory;
          use \effectivecore\html_table;
          use \effectivecore\html_pager;
          use \effectivecore\url;
          use \effectivecore\message;
          use \effectivecore\modules\storage\db;
          use \effectivecore\modules\page\page;
          abstract class events extends \effectivecore\events {

  static function on_init() {
    session::init();
  }

  static function on_install() {
    db::transaction_begin(); # @todo: test transactions
    try { 
      table_session::install();
      table_user::install();
      table_role::install();
      table_permission::install();
      table_role_by_user::install();
      table_role_by_permission::install();
      db::transaction_commit();
      message::set('Database for module "user" was installed');
    } catch (\Exception $e) {
      db::transaction_roll_back();
    }
  }

  static function on_token_replace($match) {
    switch ($match) {
      case '%%_user_id': return user::$current->id;
      case '%%_user_email': return user::$current->email;
      case '%%_profile_title':
        if (user::$current->id == url::$current->args('2')) {
          return 'My profile';
        } else {
          $info = table_user::select_first(['email'], ['id' => url::$current->args('2')]);
          return 'Profile of '.$info['email'];
        }
      case '%%_profile_edit_title':
        if (user::$current->id == url::$current->args('2')) {
          return 'Edit my profile';
        } else {
          $info = table_user::select_first(['email'], ['id' => url::$current->args('2')]);
          return 'Edit profile of '.$info['email'];
        }
    }
  }

  static function on_page_admin_roles() {
    $data = table_role::select(['id', 'title', 'is_embed'], [], ['is_embed!']);
    foreach ($data as &$c_row) $c_row['is_embed'] = $c_row['is_embed'] ? 'Yes' : 'No';
    $data_markup = new html_table([], $data, ['ID', 'Title', 'Is embed']);
    page::add_element($data_markup);
  }

  static function on_page_admin_users() {
    $total_items = table_user::select_first(['count(id)'])['count(id)'];
    $items_per_page = 50; // # @todo: settings::$data['admin_users']['constants']['items_per_page'];
    $pager = new html_pager([], $total_items, $items_per_page);
    if ($pager->has_error) {
      factory::send_header_and_exit('not_found',
        'Page not found!'
      );
    } else {
      $data = table_user::select(['id', 'email', 'created', 'is_locked'], [], ['id'], $items_per_page, ($pager->c_page_num - 1) * $items_per_page);
      $url_back = urlencode(url::$current->full());
      foreach ($data as &$c_row) {
        $c_row['actions']['_attr']['class'][] = 'actions';
        $c_row['actions'][] = new html('a', ['href' => (new url('/user/'.$c_row['id']))->full()], 'view');
        $c_row['actions'][] = new html('a', ['href' => (new url('/user/'.$c_row['id'].'/edit?back='.$url_back))->full()], 'edit');
        if (empty($c_row['is_locked'])) $c_row['actions'][] = new html('a', ['href' => (new url('/admin/users/delete/'.$c_row['id'].'?back='.$url_back))->full()], 'delete');
        $c_row['is_locked'] = $c_row['is_locked'] ? 'Yes' : 'No';
      }
      $data_markup = new html_table([], $data, ['ID', 'EMail', 'Created', 'Is embed', '']);
      page::add_element($data_markup);
      page::add_element($pager);
    }
  }

  static function on_page_admin_users_delete_n($user_id) {
    $db_user = table_user::select_first(['email', 'is_locked'], ['id' => $user_id]);
    if ($db_user && empty($db_user['is_locked'])) {
      page::add_element(new form_user_n_delete([], new html('input', ['type' => 'hidden', 'name' => 'user_id', 'value' => $user_id])));
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_page_user_register() {
    $form = new form_user_register();
    page::add_element($form);
  }

  static function on_page_user_n($user_id) {
    $db_user = table_user::select_first(['*'], ['id' => $user_id]);
    $db_user_roles = table_role_by_user::select(['role_id'], ['user_id' => $user_id]);
    if ($db_user) {
      if ($db_user['id'] == user::$current->id || isset(user::$current->roles['admins'])) {
        unset($db_user['password_hash'], $db_user['is_locked']);
        page::add_element(new html_table([], factory::array_rotate([array_keys($db_user), array_values($db_user)]), ['Parameter', 'Value']));
        page::add_element(new html_table([], $db_user_roles, ['Roles']));
      } else {
        factory::send_header_and_exit('access_denided',
          'Access denided!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_page_user_n_edit($user_id) {
    $db_user = table_user::select_first(['*'], ['id' => $user_id]);
    if ($db_user) {
      if ($db_user['id'] == user::$current->id || isset(user::$current->roles['admins'])) {
        page::add_element(new form_user_n_edit([], new html('input', ['type' => 'hidden', 'name' => 'user_id', 'value' => $user_id])));
      } else {
        factory::send_header_and_exit('access_denided',
          'Access denided!'
        );
      }
    } else {
      factory::send_header_and_exit('not_found',
        'User not found!'
      );
    }
  }

  static function on_page_user_login() {
    $form = new form_user_login();
    page::add_element($form);
  }

  static function on_page_user_logout() {
    session::destroy(user::$current->id);
    url::go('/');
  }

}}