<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Color_profile {

    const PROFILE_DEFAULT = 'dark_blue';

    public $id;
    public $title;
    public $is_dark = false;
    public $groups = [];
    public $colors = [];

    ###########################
    ### static declarations ###
    ###########################

    protected static $current;
    protected static $cache;

    static function cache_cleaning() {
        static::$current = null;
        static::$cache   = null;
    }

    static function init() {
        if (static::$cache === null) {
            foreach (Storage::get('data')->select_array('color_profiles') as $c_module_id => $c_profiles) {
                foreach ($c_profiles as $c_row_id => $c_profile) {
                    if (isset(static::$cache[$c_profile->id])) Console::report_about_duplicate('color_profiles', $c_profile->id, $c_module_id, static::$cache[$c_profile->id]);
                              static::$cache[$c_profile->id] = $c_profile;
                              static::$cache[$c_profile->id]->module_id = $c_module_id;
                }
            }
        }
    }

    static function get_current($ignore_GET = false) {
        static::init();
        if (!static::$current) {
            # trying to return profile by value from cookie
            if (isset($_COOKIE['color_profile'])) {
                $profile_id = $_COOKIE['color_profile'];
                if (is_string($profile_id) && strlen($profile_id) && isset(static::$cache[$profile_id])) {
                    static::$current = static::$cache[$profile_id];
                    return static::$current;
                }
            }
            # trying to return profile by value from user settings
            $user = User::get_current();
            $profile_id = $user->id ? $user->color_profile : '';
            if (isset(static::$cache[$profile_id])) {
                static::$current = static::$cache[$profile_id];
                return static::$current;
            }
            # trying to return profile by value from global settings
            $settings = Module::settings_get('page');
            $profile_id = $settings->color_profile;
            if (isset(static::$cache[$profile_id])) {
                static::$current = static::$cache[$settings->color_profile];
                return static::$current;
            }
            # return default value
            static::$current = static::$cache[static::PROFILE_DEFAULT];
            return static::$current;
        }
        return static::$current;
    }

    static function set_current($id) {
        if ($id === static::PROFILE_DEFAULT)
             return Storage::get('data')->changes_unregister('page', 'update', 'settings/page/color_profile');
        else return Storage::get('data')->changes_register  ('page', 'update', 'settings/page/color_profile', $id);
    }

    static function get($id) {
        static::init();
        return static::$cache[$id] ?? null;
    }

    static function get_all() {
        static::init();
        return static::$cache;
    }

    static function get_color_info($profile_id = self::PROFILE_DEFAULT, $scope = 'main') {
        $profile = static::get($profile_id);
        if ($profile) {
            foreach ($profile->groups as $c_group) {
                foreach ($c_group->colors as $c_scope => $c_color_info) {
                    if ($c_scope === $scope) {
                        return $c_color_info;
                    }
                }
            }
        }
    }

}
