<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Widget_Files_audios extends Widget_Files {

    use Widget_Files_audios__Shared;

    public $title = 'Audios';
    public $item_title = 'Audio';
    public $attributes = [
        'data-type' => 'items-files-audios'];
    public $group_name = 'widget_files_audios';
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    public $upload_dir = 'audios/';
    public $fixed_name = 'audio-multiple-%%_item_id_context';
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    public $max_file_size = '10M';
    public $types_allowed = [
        'mp3' => 'mp3'
    ];

    ###########################
    ### static declarations ###
    ###########################

    static function value_to_markup($value) {
        $decorator = new Decorator;
        $decorator->id = 'widget_files-audios-items';
        $decorator->view_type = 'template';
        $decorator->template = 'content';
        $decorator->template_item = 'gallery_item';
        $decorator->mapping = Core::array_keys_map(['num', 'type', 'children']);
        if ($value) {
            Core::array_sort_by_number($value);
            foreach ($value as $c_row_id => $c_item) {
                if (Media::media_class_get($c_item->object->type) === 'audio') {
                    $decorator->data[$c_row_id] = [
                        'type'     => ['value' => 'audio'  , 'is_apply_translation' => false],
                        'num'      => ['value' => $c_row_id, 'is_apply_translation' => false],
                        'children' => ['value' => static::render_item($c_item, $c_row_id)]
                    ];
                }
            }
        }
        return $decorator;
    }

    static function render_item($item, $row_id) {
        $result = '';
        $url = Core::to_url_from_path($item->object->get_current_path(true));
        if ($item->settings['data-cover-is-embedded']) {
            $result = Template::make_new(Template::pick_name('picture'), [
                'id'         => $row_id,
                'src'        => $url ? $url.'?cover=middle' : '',
                'attributes' => [
                    'alt'       => new Text('cover'),
                    'width'     => '300',
                    'height'    => '300',
                    'data-type' => 'cover'
                ]
            ])->render();
            $result.= Template::make_new(Template::pick_name('audio'), [
                'id'         => $row_id,
                'src'        => $url,
                'attributes' => [
                    'data-path-cover-small'  => $url ? $url.'?cover=small'  : '',
                    'data-path-cover-middle' => $url ? $url.'?cover=middle' : '',
                    'data-path-cover-big'    => $url ? $url.'?cover=big'    : '',
                ] + $item->settings
            ])->render();
        } else {
            $result = Template::make_new(Template::pick_name('audio'), [
                'id'         => $row_id,
                'src'        => $url,
                'attributes' => $item->settings
            ])->render();
        }
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function widget_markup__item($widget, $item, $c_row_id) {
        $result = parent::widget_markup__item($widget, $item, $c_row_id);
        $result->child_select('head')->child_select('button_delete')->_kind = 'audio';
        $result->attribute_insert('data-is-new', $item->object->get_current_state() === 'pre' ? 'true' : 'false');
        if (Media::media_class_get($item->object->type) === 'audio') {
            if (!empty($widget->audio_player_on_manage_is_visible)) {
                $result->child_select('body')->child_insert(new Markup('audio', [
                    'src' => Core::to_url_from_path($item->object->get_current_path(true))] + $widget->audio_player_on_manage_settings, [
                ], +500), 'player'); }
            if (!empty($item->settings['data-cover-is-embedded'])) {
                $result->child_select('body')->child_select('info')->child_select('title')->child_select('text')->text_append(
                    ' + '.(new Text('cover'))->render());
                $result->child_select('body')->child_insert(new Markup_simple('img', [
                    'data-type' => 'thumbnail',
                    'src'       => Core::to_url_from_path($item->object->get_current_path(true)).'?cover=small',
                    'alt'       => new Text('thumbnail'),
                    'width'     => '44',
                    'height'    => '44',
                ], +450), 'thumbnail');
            }
        }
        return $result;
    }

    static function widget_markup__insert($widget, $group = '') {
        $result = new Markup('x-widget', ['data-type' => 'insert']);
        # control for upload new audio
        $field_file_audio = new Field_File_audio;
        $field_file_audio->title             = 'Audio';
        $field_file_audio->max_file_size     = $widget->{($group ? $group.'_' : '').'max_file_size'};
        $field_file_audio->types_allowed     = $widget->{($group ? $group.'_' : '').'types_allowed'};
        $field_file_audio->cform             = $widget->cform;
        $field_file_audio->min_files_number  = null;
        $field_file_audio->max_files_number  = null;
        $field_file_audio->has_widget_insert = false;
        $field_file_audio->has_widget_manage = false;
        $field_file_audio->build();
        $field_file_audio->name_set(
            $widget->group_control_name_get(['file', $group])
        );
        # control for upload new audio cover
        $field_file_picture_cover = new Field_File_picture;
        $field_file_picture_cover->title             = 'Cover';
        $field_file_picture_cover->max_file_size     = $widget->cover_max_file_size;
        $field_file_picture_cover->types_allowed     = $widget->cover_types_allowed;
        $field_file_picture_cover->cform             = $widget->cform;
        $field_file_picture_cover->min_files_number  = null;
        $field_file_picture_cover->max_files_number  = null;
        $field_file_picture_cover->has_widget_insert = false;
        $field_file_picture_cover->has_widget_manage = false;
        $field_file_picture_cover->build();
        $field_file_picture_cover->name_set(
            $widget->group_control_name_get(['cover', $group])
        );
        # button for insertion of the new item
        $button_insert = new Button(null, ['data-style' => 'insert', 'title' => new Text('insert')]);
        $button_insert->break_on_validate = true;
        $button_insert->build();
        $button_insert->value_set(
            $widget->group_control_name_get(['insert', $group])
        );
        $button_insert->_type = 'insert';
        $button_insert->_kind = 'audio';
        # relate new controls with the widget
        if (true                     ) $widget->controls[  '#file'.($group ? '_'.$group : '')] = $field_file_audio;
        if ($widget->cover_is_allowed) $widget->controls['#cover'                            ] = $field_file_picture_cover;
        if (true                     ) $widget->controls['~insert'.($group ? '_'.$group : '')] = $button_insert;
        if (true                     ) $result->child_insert($field_file_audio        , 'field_file_audio');
        if ($widget->cover_is_allowed) $result->child_insert($field_file_picture_cover, 'field_file_picture_cover');
        if (true                     ) $result->child_insert($button_insert           , 'button_insert');
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function on_file_prepare($widget, $form, $npath, $button, &$items, &$new_item) {
        if (parent::on_file_prepare($widget, $form, $npath, $button,  $items,  $new_item)) {
            $new_item->settings = $widget->audio_player_default_settings;
            $new_item->settings['data-cover-is-embedded'] = false;
            if ($widget->cover_is_allowed) {
                if (Media::media_class_get($new_item->object->type) === 'audio') {
                    $values = Event::start_local('on_values_validate', $widget, ['form' => $form, 'npath' => $npath, 'button' => $button, 'name' => '#cover']);
                    $cover = reset($values);
                    if ($cover instanceof File_history) {
                        if (Media::media_class_get($cover->type) === 'picture') {
                            if (Media::is_type_for_thumbnail($cover->type)) {
                                $pre_path = $new_item->object->get_current_path();
                                if ($cover->move_tmp_to_pre($pre_path.'.'.$cover->type)) {
                                    if ($new_item->object->container_audio_make($widget->cover_thumbnails, $cover->get_current_path())) {
                                        $new_item->settings['data-cover-is-embedded'] = true;
                                        File::delete($pre_path.'.'.$cover->type);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return true;
        }
    }

    static function on_button_click_insert($widget, $form, $npath, $button) {
        if ($widget->cover_is_allowed) {
            $values       = Event::start_local('on_values_validate', $widget, ['form' => $form, 'npath' => $npath, 'button' => $button, 'name' => '#file' ]);
            $values_cover = Event::start_local('on_values_validate', $widget, ['form' => $form, 'npath' => $npath, 'button' => $button, 'name' => '#cover']);
            if (!$widget->controls['#file']->has_error() &&                                              count($values) === 0) {$widget->controls['#file']->error_set('Field "%%_title" cannot be blank!', ['title' => (new Text($widget->controls['#file']->title))->render() ]); return;}
            if (!$widget->controls['#file']->has_error() && !$widget->controls['#cover']->has_error() && count($values) !== 0)
               return Widget_Files::on_button_click_insert($widget, $form, $npath, $button);
        } else return Widget_Files::on_button_click_insert($widget, $form, $npath, $button);
    }

}
