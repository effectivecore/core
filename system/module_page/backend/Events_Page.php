<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\page;

use effcore\Access;
use effcore\Block_preset;
use effcore\Core;
use effcore\Entity;
use effcore\Markup;
use effcore\Message;
use effcore\Request;
use effcore\Selection;
use effcore\Text;
use effcore\Token;
use effcore\URL;

abstract class Events_Page {

    static function block_markup___messages($page, $args = []) {
        return new Message;
    }

    static function block_markup___title($page, $args = []) {
        return new Markup('h1', ['id' => 'title'],
            new Text($page->title, [], true, true)
        );
    }

    static function block_markup___page_actions($page, $args = []) {
        if (Access::check((object)['roles' => ['registered' => 'registered']])) {
            if ($page->origin === 'sql' || $page->origin === 'hybrid') {
                if (Access::check((object)['roles'             => [  'admins'            =>   'admins'],
                                           'permissions_match' => ['%^manage_data__.+$%' => '%^manage_data__.+$%']])) {
                    $url = clone URL::get_current();
                    $edit_mode = Request::value_get('manage_layout', 0, '_GET');
                    if ($edit_mode === 'true')
                         $url->query_arg_delete('manage_layout');
                    else $url->query_arg_insert('manage_layout', 'true');
                    $admin_actions = new Markup('x-admin-actions', ['data-entity_name' => 'page']);
                    if ($edit_mode !== 'true'                                                         ) $admin_actions->child_insert(new Markup('a', ['data-id' => 'manage-enter', 'href' => $url->relative_get()], 'enter edit mode'), 'manage_layout');
                    if ($edit_mode === 'true'                                                         ) $admin_actions->child_insert(new Markup('a', ['data-id' => 'manage-leave', 'href' => $url->relative_get()], 'leave edit mode'), 'manage_layout');
                    if ($edit_mode === 'true' && Access::check(Entity::get('page')->access->on_update)) $admin_actions->child_insert(new Markup('a', ['data-id' => 'update', 'title' => new Text('update'), 'href' => '/manage/data/content/page/'.$page->id.'/update?'.URL::back_part_make()], new Markup('x-action-title', ['data-action-title' => true], 'update')), 'update_page');
                    return $admin_actions;
                }
            }
        }
    }

    static function on_block_build_after($event, $block) {
        if (Request::value_get('manage_layout', 0, '_GET') === 'true') {
            if (Access::check((object)['roles' => ['registered' => 'registered']])) {
                if (!empty($block->has_admin_menu)) {
                    $instance_id = $block->args['instance_id'];
                    $entity_name = $block->args['entity_name'];
                    $entity = Entity::get($entity_name);
                    if (!empty($entity->access->on_update) && Access::check($entity->access->on_update)) {
                        $block->header = new Markup('x-admin-actions', ['data-entity_name' => $entity_name],
                            new Markup('a', ['data-id' => 'update', 'title' => new Text('update'), 'href' => '/manage/data/content/'.$entity_name.'/'.$instance_id.'/update?'.URL::back_part_make()],
                                new Markup('x-action-title', ['data-action-title' => true], 'update')
                            )
                        );
                    }
                }
            }
        }
    }

    static function on_block_presets_dynamic_build($event, $id = null) {
        if ($id === null                                                   ) {foreach (Entity::get('audio'  )->instances_select() as $c_item) Block_preset::insert('block__'.  'audio_sql__'.$c_item->id, 'Audios'   , $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Audio'  , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'audio'  ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.  'audio_sql__'.$c_item->id]], 0, 'page');}
        if ($id === null                                                   ) {foreach (Entity::get('gallery')->instances_select() as $c_item) Block_preset::insert('block__'.'gallery_sql__'.$c_item->id, 'Galleries', $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Gallery', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'gallery'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.'gallery_sql__'.$c_item->id]], 0, 'page');}
        if ($id === null                                                   ) {foreach (Entity::get('picture')->instances_select() as $c_item) Block_preset::insert('block__'.'picture_sql__'.$c_item->id, 'Pictures' , $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Picture', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'picture'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.'picture_sql__'.$c_item->id]], 0, 'page');}
        if ($id === null                                                   ) {foreach (Entity::get('text'   )->instances_select() as $c_item) Block_preset::insert('block__'.   'text_sql__'.$c_item->id, 'Texts'    , $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Text'   , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'text'   ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.   'text_sql__'.$c_item->id]], 0, 'page');}
        if ($id === null                                                   ) {foreach (Entity::get('video'  )->instances_select() as $c_item) Block_preset::insert('block__'.  'video_sql__'.$c_item->id, 'Videos'   , $c_item->description ?: 'NO DESCRIPTION', ['title' => 'Videos' , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item->id, 'entity_name' => 'video'  ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.  'video_sql__'.$c_item->id]], 0, 'page');}
        if ($id !== null && str_starts_with($id, 'block__'.  'audio_sql__')) {$c_item__id = substr($id, strlen('block__'.  'audio_sql__'));   Block_preset::insert('block__'.  'audio_sql__'.$c_item__id, 'Audios'   ,                         'NO DESCRIPTION', ['title' => 'Audio'  , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'audio'  ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.  'audio_sql__'.$c_item__id]], 0, 'page');}
        if ($id !== null && str_starts_with($id, 'block__'.'gallery_sql__')) {$c_item__id = substr($id, strlen('block__'.'gallery_sql__'));   Block_preset::insert('block__'.'gallery_sql__'.$c_item__id, 'Galleries',                         'NO DESCRIPTION', ['title' => 'Gallery', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'gallery'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.'gallery_sql__'.$c_item__id]], 0, 'page');}
        if ($id !== null && str_starts_with($id, 'block__'.'picture_sql__')) {$c_item__id = substr($id, strlen('block__'.'picture_sql__'));   Block_preset::insert('block__'.'picture_sql__'.$c_item__id, 'Pictures' ,                         'NO DESCRIPTION', ['title' => 'Picture', 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'picture'], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.'picture_sql__'.$c_item__id]], 0, 'page');}
        if ($id !== null && str_starts_with($id, 'block__'.   'text_sql__')) {$c_item__id = substr($id, strlen('block__'.   'text_sql__'));   Block_preset::insert('block__'.   'text_sql__'.$c_item__id, 'Texts'    ,                         'NO DESCRIPTION', ['title' => 'Text'   , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'text'   ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.   'text_sql__'.$c_item__id]], 0, 'page');}
        if ($id !== null && str_starts_with($id, 'block__'.  'video_sql__')) {$c_item__id = substr($id, strlen('block__'.  'video_sql__'));   Block_preset::insert('block__'.  'video_sql__'.$c_item__id, 'Videos'   ,                         'NO DESCRIPTION', ['title' => 'Video'  , 'title_is_visible' => false, 'type' => 'code', 'source' => '\\effcore\\modules\\page\\Events_Page::block_markup__selection_make', 'args' => ['instance_id' => $c_item__id, 'entity_name' => 'video'  ], 'has_admin_menu' => true, 'attributes' => ['data-id' => 'block__'.  'video_sql__'.$c_item__id]], 0, 'page');}
    }

    static function block_markup__selection_make($page, $args = []) {
        if (!empty($args['instance_id']) &&
            !empty($args['entity_name'])) {
            $selection = Selection::get($args['entity_name']);
            if ($selection) {
                Token::insert('selection__'.$args['entity_name'].'__id__context', 'text', $args['instance_id'], null, 'page');
                $selection = Core::deep_clone($selection);
                $selection->id = $args['entity_name'].'_'.$args['instance_id'];
                $selection->build();
                return $selection;
            }
        }
    }

}
