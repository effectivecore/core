<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\page;

use effcore\Area;
use effcore\Core;
use effcore\Layout;
use effcore\Markup;
use effcore\Message;
use effcore\Page;
use effcore\Request;
use effcore\Storage;

abstract class Events_Form_Layout {

    static function on_build($event, $form) {
        $layout_id = Page::get_current()->args_get('id');
        if (Layout::select($layout_id)) {
            $form->_layout = Core::deep_clone(Layout::select ($layout_id) );
            $form->_layout_settings = Layout::select_settings($layout_id);
            foreach ($form->_layout->children_select_recursive() as $c_area) {
                if ($c_area instanceof Area) {
                    $c_area->manage_mode_enable('customizable');
                    $c_area->states_set(
                        $c_area->id &&
                        isset($form->_layout_settings['states'][$c_area->id]) ?
                              $form->_layout_settings['states'][$c_area->id] : []);
                    $c_area->build();
                }
            }
            $form->child_select('data')->child_insert(
                new Markup('x-layout-manager', [
                    'data-layout-id' => $form->_layout->id], [
                    'manager'        => $form->_layout]), 'layout_manager'
            );
        }
    }

    static function on_init($event, $form, $items) {
        if (isset($form->_layout) && $form->_layout->is_manageable) {
            $items['~save' ]->disabled_set(false);
            $items['~reset']->disabled_set(false);
        }
    }

    static function on_submit($event, $form, $items) {
        switch ($form->clicked_button->value_get()) {
            case 'save':
                foreach ($form->_layout->children_select_recursive() as $c_area) {
                    if ($c_area instanceof Area) {
                        if ($c_area->id) {
                            $c_settings = $c_area->states_get();
                            if ($c_settings) {
                                $form->_layout_settings['states'][$c_area->id] = $c_settings;
                            }
                        }
                    }
                }
                $module_id = $form->_layout_settings['module_id'] ?? 'page';
                $result = Storage::get('data')->changes_register($module_id, 'update', 'layouts_settings/'.$module_id.'/'.$form->_layout->id, $form->_layout_settings['states']);
                if ($result)
                     Message::insert('Changes was saved.'             );
                else Message::insert('Changes was not saved!', 'error');
                break;
            case 'reset':
                $module_id = $form->_layout_settings['module_id'] ?? 'page';
                $result = Storage::get('data')->changes_unregister($module_id, 'update', 'layouts_settings/'.$module_id.'/'.$form->_layout->id);
                if ($result) Message::insert('Changes was deleted.'             );
                else         Message::insert('Changes was not deleted!', 'error');
                Layout::cache_cleaning();
                Request::values_reset('_POST');
                static::on_build(null, $form);
                break;
        }
    }

}
