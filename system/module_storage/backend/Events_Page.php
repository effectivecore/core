<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\storage;

use effcore\Block_preset;
use effcore\Page;
use effcore\Selection;
use effcore\Tab_item;
use effcore\Tabs;
use effcore\URL;

abstract class Events_Page {

    static function on_breadcrumbs_build_before($event, $breadcrumbs) {
        if (Page::get_current()->id === 'instance_select' ||
            Page::get_current()->id === 'instance_insert' ||
            Page::get_current()->id === 'instance_update' ||
            Page::get_current()->id === 'instance_delete') {
            $breadcrumbs->is_remove_last_link = false;
            $tab_data = Tabs::select('data');
            $tab_data->build();
            foreach ($tab_data->children_select_recursive() as $c_child) {
                if ($c_child instanceof Tab_item) {
                    if ($c_child->is_active      () ||
                        $c_child->is_active_trail()) {
                        $breadcrumbs->link_insert(
                            $c_child->id,
                            $c_child->title,
                            $c_child->href_get_default()
                        );
                    }
                }
            }
        }
    }

    static function on_breadcrumbs_build_after_apply_back_return($event, $breadcrumbs) {
        if (Page::get_current()->id === 'instance_select' ||
            Page::get_current()->id === 'instance_insert' ||
            Page::get_current()->id === 'instance_update' ||
            Page::get_current()->id === 'instance_delete') {
            $back_return_0 = Page::get_current()->args_get('back_return_0');
            $back_return_n = Page::get_current()->args_get('back_return_n');
            $row_ids = array_keys($breadcrumbs->link_select_all());
            $row_id_last = array_pop($row_ids);
            if ($row_id_last) {
                $breadcrumbs->link_update($row_id_last,                        null,
                    $back_return_0 ?: (URL::back_url_get() ?: ($back_return_n ?: null))
                );
            }
        }
    }

    static function on_block_presets_dynamic_build($event, $id = null) {
        if ($id === null                                                  ) {foreach (Selection::get_all('sql') as $c_item)               Block_preset::insert('block__selection_sql__'.$c_item->id, 'Selections', $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Selection', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\storage\\Events_Page::block_markup__selection_get', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'selection'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__selection_sql__'.$c_item->id]], 0, 'storage');}
        if ($id !== null && str_starts_with($id, 'block__selection_sql__')) {$c_item__id = substr($id, strlen('block__selection_sql__')); Block_preset::insert('block__selection_sql__'.$c_item__id, 'Selections',                         'NO DESCRIPTION', ['title' => 'Selection', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\storage\\Events_Page::block_markup__selection_get', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'selection'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__selection_sql__'.$c_item__id]], 0, 'storage');}
    }

    static function block_markup__selection_get($page, $args) {
        if (!empty($args['instance_id'])) {
            return Selection::get($args['instance_id']);
        }
    }

}
