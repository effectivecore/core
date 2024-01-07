<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Layout extends Node implements has_Data_cache {

    public $id;
    public $title;
    public $is_manageable = false;

    function render_self() {
        return '';
    }

    ###########################
    ### static declarations ###
    ###########################

    protected static $cache;
    protected static $cache_for_settings;

    static function not_external_properties_get() {
        return [
            'id' => 'id'
        ];
    }

    static function cache_cleaning() {
        static::$cache              = null;
        static::$cache_for_settings = null;
    }

    static function init() {
        if (static::$cache === null) {
            foreach (Storage::get('data')->select_array('layouts') as $c_module_id => $c_layouts) {
                foreach ($c_layouts as $c_id => $c_layout) {
                    if (isset(static::$cache[$c_id])) Console::report_about_duplicate('layouts', $c_id, $c_module_id, static::$cache[$c_id]);
                              static::$cache[$c_id] = $c_layout;
                              static::$cache[$c_id]->module_id = $c_module_id;
                }
            }
            foreach (Storage::get('data')->select_array('layouts_settings') as $c_module_id => $c_settings) {
                foreach ($c_settings as $c_id => $c_states) {
                    if (isset(static::$cache_for_settings[$c_id])) Console::report_about_duplicate('layouts_settings', $c_id, $c_module_id, static::$cache_for_settings[$c_id]);
                              static::$cache_for_settings[$c_id]['module_id'] = $c_module_id;
                              static::$cache_for_settings[$c_id]['states'   ] = $c_states;
                }
            }
        }
    }

    static function select_all($load = true) {
        static::init();
        if ($load)
            foreach (static::$cache as $id => $c_item)
                if (static::$cache[$id] instanceof External_cache)
                    static::$cache[$id] =
                    static::$cache[$id]->load_from_nosql_storage();
        return static::$cache;
    }

    static function select($id, $load = true) {
        static::init();
        if (isset(static::$cache[$id]) === false) return;
        if (static::$cache[$id] instanceof External_cache && $load)
               static::$cache[$id] =
               static::$cache[$id]->load_from_nosql_storage();
        return static::$cache[$id];
    }

    static function select_settings($id) {
        static::init();
        return static::$cache_for_settings[$id] ?? [];
    }

}
