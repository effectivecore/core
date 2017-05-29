<?php

namespace effectivecore\modules\page {
          use const \effectivecore\br;
          use \effectivecore\factory;
          use \effectivecore\url_factory as urls;
          use \effectivecore\modules\user\access_factory as access;
          use \effectivecore\modules\storage\storage_factory as storages;
          abstract class page_factory {

  static $data = [];
  static $args = [];

  static function find_and_render() {
    # render page
    foreach (storages::get('settings')->select('pages') as $c_pages) {
      foreach ($c_pages as $c_page) {
        if (   isset($c_page->url->match) &&
          preg_match($c_page->url->match, urls::get_current()->path)) {
          if (!isset($c_page->access) ||
              (isset($c_page->access) && access::check($c_page->access))) {
            return $c_page->render();
          } else {
            factory::send_header_and_exit('access_denided',
              'Access denided!'
            );
          }
        }
      }
    }
    # no matches case
    factory::send_header_and_exit('not_found',
      'Page not found!'
    );
  }

}}