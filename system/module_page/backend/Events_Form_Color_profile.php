<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\page;

use effcore\Color_profile;
use effcore\Fieldset;
use effcore\Group_Palette;
use effcore\Message;
use effcore\Page;
use effcore\Storage;

abstract class Events_Form_Color_profile {

    static function on_build($event, $form) {
        $id = Page::get_current()->args_get('id');
        $form->_profile = Color_profile::get($id);
        if ($form->_profile) {
            setcookie('color_profile', $id, time() + 1, '/');
            foreach ($form->_profile->groups as $c_group_row_id => $c_group) {
                $c_fieldset = new Fieldset($c_group->title);
                foreach ($c_group->colors as $c_scope => $c_color_info) {
                    $c_palette = new Group_Palette;
                    $c_palette->title = $c_color_info->title;
                    $c_palette->element_attributes['name'] = 'color_'.$c_scope;
                    $c_fieldset->child_insert($c_palette, $c_scope);
                }
                $form->child_select('data')->child_insert(
                    $c_fieldset, $c_group_row_id
                );
            }
        }
    }

    static function on_init($event, $form, $items) {
        $id = Page::get_current()->args_get('id');
        $form->_profile = Color_profile::get($id);
        if ($form->_profile) {
            foreach ($form->_profile->groups as $c_group) {
                foreach ($c_group->colors as $c_scope => $c_color_info) {
                    $items['*color_'.$c_scope]->value_set(
                        $c_color_info->color_id
                    );
                }
            }
            $items['~save'    ]->disabled_set(false);
            $items['~reset'   ]->disabled_set(false);
            $items['~activate']->disabled_set(Color_profile::get_current()->id === $id);
        }
    }

    static function on_submit($event, $form, $items) {
        $id = Page::get_current()->args_get('id');
        switch ($form->clicked_button->value_get()) {
            case 'save':
                $result     = true;
                $profile_id = $form->_profile->id;
                $module_id  = $form->_profile->module_id;
                foreach ($form->_profile->groups as $c_group_row_id => $c_group) {
                    foreach ($c_group->colors as $c_scope => $c_color_info) {
                        $c_is_rebuild = end($form->_profile->groups) === $c_group &&
                                        end($c_group->colors       ) === $c_color_info;
                        $c_new_value = $items['*color_'.$c_scope]->value_get();
                        $result&= Storage::get('data')->changes_register($module_id, 'update',
                            'color_profiles/'.$module_id.
                                          '/'.$profile_id.
                                   '/groups/'.$c_group_row_id.
                                   '/colors/'.$c_scope.
                                 '/color_id', $c_new_value,
                                              $c_is_rebuild
                        );
                    }
                }
                if ($result) Message::insert('Changes was saved.'             );
                else         Message::insert('Changes was not saved!', 'error');
                break;
            case 'reset':
                $result     = true;
                $profile_id = $form->_profile->id;
                $module_id  = $form->_profile->module_id;
                foreach ($form->_profile->groups as $c_group_row_id => $c_group) {
                    foreach ($c_group->colors as $c_scope => $c_color_info) {
                        $c_is_rebuild = end($form->_profile->groups) === $c_group &&
                                        end($c_group->colors       ) === $c_color_info;
                        $result&= Storage::get('data')->changes_unregister($module_id, 'update',
                            'color_profiles/'.$module_id.
                                          '/'.$profile_id.
                                   '/groups/'.$c_group_row_id.
                                   '/colors/'.$c_scope.
                                 '/color_id', $c_is_rebuild
                        );
                    }
                }
                if ($result) Message::insert('Changes was deleted.'             );
                else         Message::insert('Changes was not deleted!', 'error');
                Color_profile::cache_cleaning();
                static::on_init(null, $form, $items);
                break;
            case 'activate':
                $result = Color_profile::set_current($id);
                if ($result) Message::insert('Color profile was activated.'             );
                else         Message::insert('Color profile was not activated!', 'error');
                Color_profile::cache_cleaning();
                static::on_init(null, $form, $items);
                break;
        }
    }

}
