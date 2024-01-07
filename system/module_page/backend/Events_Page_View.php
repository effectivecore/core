<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\page;

use effcore\Color_profile;
use effcore\Core;
use effcore\Layout;
use effcore\Page;
use effcore\Tab_item;
use effcore\Text_multiline;
use effcore\Url;

abstract class Events_Page_View {

    static function on_redirect($event, $page) {
        $type = $page->args_get('type');
        $id   = $page->args_get('id');
        if ($type === null             ) {                                                                                                        Url::go($page->args_get('base').'/colors/settings'                      );}
        if ($type === 'colors'         ) {                                                                                                        Url::go($page->args_get('base').'/colors/settings'                      );}
        if ($type === 'colors/profiles') {$profiles = Color_profile::get_all(); Core::array_sort_by_string($profiles); if (empty($profiles[$id])) Url::go($page->args_get('base').'/colors/profiles/'.reset($profiles)->id);}
        if ($type === 'layouts'        ) {$layouts  = Layout::select_all    (); Core::array_sort_by_string($layouts ); if (empty($layouts [$id])) Url::go($page->args_get('base').'/layouts/'        .reset($layouts )->id);}
    }

    static function on_tab_build_before($event, $tab) {
        $base = Page::get_current()->args_get('base');
        $type = Page::get_current()->args_get('type');
        if ($base === '/manage/view' && str_starts_with($type, 'colors/')) {
            $profiles = Color_profile::get_all();
            Core::array_sort_by_string($profiles);
            foreach ($profiles as $c_profile) {
                $c_profile_title = (new Text_multiline([
                    $c_profile->title, ' (', 'Module ID', ': '.
                    $c_profile->module_id.')'
                ], [], ''))->render();
                Tab_item::insert(                                      $c_profile_title,
                    'view_colors_profiles_'                           .$c_profile->id,
                    'view_colors_profiles', 'view', 'colors/profiles/'.$c_profile->id, null, [], [], false, 0, 'page'
                );
            }
        }
        if ($type === 'layouts') {
            $layouts = Layout::select_all();
            Core::array_sort_by_string($layouts);
            foreach ($layouts as $c_layout) {
                Tab_item::insert(                      $c_layout->title,
                    'view_layouts_'                   .$c_layout->id,
                    'view_layouts', 'view', 'layouts/'.$c_layout->id, null, [], [], false, 0, 'page'
                );
            }
        }
    }

}
