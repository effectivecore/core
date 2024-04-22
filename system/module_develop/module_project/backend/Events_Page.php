<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\project;

use effcore\Block_preset;
use effcore\Core;
use effcore\Entity;
use effcore\Project;
use effcore\Selection;
use effcore\Token;

abstract class Events_Page {

    static function on_block_presets_dynamic_build($event, $id = null) {
        if ($id === null                                                                ) {foreach (Entity::get('project')->instances_select() as $c_item)            Block_preset::insert('block__project_release_current_sql__'.$c_item->id, 'Project release (current)', 'id_project = '.$c_item->id, ['title' => 'Current release of the project %%_project__title__context', 'title_is_visible' => true, 'title_is_apply_translation' => true, 'title_is_apply_tokens' => true, 'type' => 'code', 'source' => '\\effcore\\modules\\project\\Events_Page::block_markup__selection_project_release_current_make', 'args' => ['id_project' => $c_item->id], 'has_admin_menu' => false, 'attributes' => ['data-id' => 'block__project_release_current_sql__'.$c_item->id]], 0, 'project');}
        if ($id === null                                                                ) {foreach (Entity::get('project')->instances_select() as $c_item)            Block_preset::insert('block__project_releases_sql__'       .$c_item->id, 'Project releases'         , 'id_project = '.$c_item->id, ['title' => 'Releases of the project %%_project__title__context'       , 'title_is_visible' => true, 'title_is_apply_translation' => true, 'title_is_apply_tokens' => true, 'type' => 'code', 'source' => '\\effcore\\modules\\project\\Events_Page::block_markup__selection_project_releases_make'       , 'args' => ['id_project' => $c_item->id], 'has_admin_menu' => false, 'attributes' => ['data-id' => 'block__project_releases_sql__'       .$c_item->id]], 0, 'project');}
        if ($id !== null && str_starts_with($id, 'block__project_release_current_sql__')) {$c_item__id = substr($id, strlen('block__project_release_current_sql__')); Block_preset::insert('block__project_release_current_sql__'.$c_item__id, 'Project release (current)', 'id_project = '.$c_item__id, ['title' => 'Current release of the project %%_project__title__context', 'title_is_visible' => true, 'title_is_apply_translation' => true, 'title_is_apply_tokens' => true, 'type' => 'code', 'source' => '\\effcore\\modules\\project\\Events_Page::block_markup__selection_project_release_current_make', 'args' => ['id_project' => $c_item__id], 'has_admin_menu' => false, 'attributes' => ['data-id' => 'block__project_release_current_sql__'.$c_item__id]], 0, 'project');}
        if ($id !== null && str_starts_with($id, 'block__project_releases_sql__'       )) {$c_item__id = substr($id, strlen('block__project_releases_sql__'       )); Block_preset::insert('block__project_releases_sql__'       .$c_item__id, 'Project releases'         , 'id_project = '.$c_item__id, ['title' => 'Releases of the project %%_project__title__context'       , 'title_is_visible' => true, 'title_is_apply_translation' => true, 'title_is_apply_tokens' => true, 'type' => 'code', 'source' => '\\effcore\\modules\\project\\Events_Page::block_markup__selection_project_releases_make'       , 'args' => ['id_project' => $c_item__id], 'has_admin_menu' => false, 'attributes' => ['data-id' => 'block__project_releases_sql__'       .$c_item__id]], 0, 'project');}
    }

    static function block_markup__selection_project_release_current_make($page, $args = []) {
        if (!empty($args['id_project'])) {
            $project = Project::select($args['id_project']);
            if ($project) {
                Token::insert('project__id__context'         , 'text', $project->id         , null, 'project');
                Token::insert('project__title__context'      , 'text', $project->title      , null, 'project');
                Token::insert('project__description__context', 'text', $project->description, null, 'project');
                Token::insert('project__created__context'    , 'text', $project->created    , null, 'project');
                Token::insert('project__updated__context'    , 'text', $project->updated    , null, 'project');
                $selection = Core::deep_clone(Selection::get('project_release_current'));
                if ($selection) {
                    $selection->build();
                    return $selection;
                }
            }
        }
    }

    static function block_markup__selection_project_releases_make($page, $args = []) {
        if (!empty($args['id_project'])) {
            $project = Project::select($args['id_project']);
            if ($project) {
                Token::insert('project__id__context'         , 'text', $project->id         , null, 'project');
                Token::insert('project__title__context'      , 'text', $project->title      , null, 'project');
                Token::insert('project__description__context', 'text', $project->description, null, 'project');
                Token::insert('project__created__context'    , 'text', $project->created    , null, 'project');
                Token::insert('project__updated__context'    , 'text', $project->updated    , null, 'project');
                $selection = Core::deep_clone(Selection::get('project_releases'));
                if ($selection) {
                    $selection->build();
                    return $selection;
                }
            }
        }
    }

}
