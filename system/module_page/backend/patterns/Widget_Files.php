<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use stdClass;

#[\AllowDynamicProperties]

class Widget_Files extends Widget_Items {

    public $title = 'Files';
    public $item_title = 'File';
    public $attributes = [
        'data-type' => 'items-files'];
    public $group_name = 'widget_files';
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    public $upload_dir = '';
    public $fixed_name = 'file-multiple-%%_item_id_context';
    public $fixed_type;
    public $max_file_size = '5K';
    public $types_allowed = [
        'txt' => 'txt'
    ];

    function value_get($options = []) { # @return: array | serialize(array)
        $is_relative = array_key_exists('is_relative', $options) && $options['is_relative'] === false ? false : true;
        $items = $this->items_get();
        foreach ($items as $c_item) {
            if (empty($c_item->object->tmp_path) === true                         ) unset($c_item->object->tmp_path);
            if (empty($c_item->object->pre_path) === true                         ) unset($c_item->object->pre_path);
            if (empty($c_item->object->fin_path) !== true && $is_relative === true)       $c_item->object->fin_path = (new File($c_item->object->fin_path))->path_get_relative();
            if (empty($c_item->object->fin_path) !== true && $is_relative !== true)       $c_item->object->fin_path = (new File($c_item->object->fin_path))->path_get_absolute();
        }
        if (!empty($options['return_serialized']))
             return serialize($items);
        else return           $items;
    }

    function value_set($value, $options = []) {
        $this->value_set_initial($value);
        $is_absolute = array_key_exists('is_absolute', $options) && $options['is_absolute'] === false ? false : true;
        if (Core::data_is_serialized($value)) $value = unserialize($value);
        if ($value === null) $value = [];
        if ($value ===  '' ) $value = [];
        if (is_array($value)) {
            foreach ($value as $c_item)
                if (empty($c_item->object->fin_path) !== true)
                    if ($is_absolute)
                         $c_item->object->fin_path = (new File($c_item->object->fin_path))->path_get_absolute();
                    else $c_item->object->fin_path = (new File($c_item->object->fin_path))->path_get_relative();
            $this->items_set($value, !empty($options['once']));
        }
    }

    ###########################
    ### static declarations ###
    ###########################

    static function value_to_markup($value) {
        $decorator = new Decorator('ul');
        $decorator->id = 'widget_files-items';
        if ($value) {
            Core::array_sort_by_number($value);
            foreach ($value as $c_row_id => $c_item) {
                $decorator->data[$c_row_id] = [
                    'path' => ['title' => 'Path', 'value' => $c_item->object->get_current_path(true), 'is_apply_translation' => false],
                    'type' => ['title' => 'Type', 'value' => $c_item->object->mime                  , 'is_apply_translation' => false],
                    'size' => ['title' => 'Size', 'value' => $c_item->object->size                  , 'is_apply_translation' => false]
                ];
            }
        }
        return $decorator;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function widget_markup__item($widget, $item, $c_row_id) {
        $result = parent::widget_markup__item($widget, $item, $c_row_id);
        # info markup
        $file = new File($item->object->get_current_path());
        $id_markup = $item->object->get_current_state() === 'pre' ?
            new Text_multiline(['new item', '…'], [], '') :
            new Text($file->file_get());
        $info_markup = new Markup('x-info' , [], [
            'title' => new Markup('x-title', [], ['text' => $item->title ?? $item->object->file]),
            'id'    => new Markup('x-id'   , [], ['text' => $id_markup])]);
        # grouping of previous elements in widget 'manage'
        $result->child_select('body')->child_insert($info_markup, 'info');
        return $result;
    }

    static function widget_markup__insert($widget) {
        $result = new Markup('x-widget', ['data-type' => 'insert']);
        # control for upload new file
        $field_file = new Field_File;
        $field_file->title = 'File';
        $field_file->max_file_size     = $widget->max_file_size;
        $field_file->types_allowed     = $widget->types_allowed;
        $field_file->cform             = $widget->cform;
        $field_file->min_files_number  = null;
        $field_file->max_files_number  = null;
        $field_file->has_widget_insert = false;
        $field_file->has_widget_manage = false;
        $field_file->build();
        $field_file->multiple_set();
        $field_file->name_set(
            $widget->group_control_name_get(['file'], '[]')
        );
        # button for insertion of the new item
        $button_insert = new Button(null, ['data-style' => 'insert', 'title' => new Text('insert')]);
        $button_insert->break_on_validate = true;
        $button_insert->build();
        $button_insert->value_set(
            $widget->group_control_name_get(['insert'])
        );
        $button_insert->_type = 'insert';
        # relate new controls with the widget
        $widget->controls['#file'  ] = $field_file;
        $widget->controls['~insert'] = $button_insert;
        $result->child_insert($field_file   , 'field_file');
        $result->child_insert($button_insert, 'button_insert');
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function on_values_validate($widget, $form, $npath, $button, $name) {
        return Field_File::on_validate_manual($widget->controls[$name], $form, $npath);
    }

    static function on_file_prepare($widget, $form, $npath, $button, &$items, &$new_item) {
        $last_key = count($items) ? array_key_last($items) + 1 : 0;
        $pre_path = Temporary::DIRECTORY.'validation/'.$form->validation_cache_date_get().'/'.$form->validation_id.'-'.$widget->group_name_get().'-'.$last_key.'.'.$new_item->object->type;
        if ($new_item->object->move_tmp_to_pre($pre_path)) {
            return true;
        }
    }

    static function on_button_click_insert($widget, $form, $npath, $button) {
        $values = Event::start_local('on_values_validate', $widget, ['form' => $form, 'npath' => $npath, 'button' => $button, 'name' => '#file']);
        if (!$widget->controls['#file']->has_error() && count($values) === 0) {$widget->controls['#file']->error_set('Field "%%_title" cannot be blank!', ['title' => (new Text($widget->controls['#file']->title))->render() ]); return;}
        if (!$widget->controls['#file']->has_error() && count($values) !== 0) {
            $has_error = false;
            $items = $widget->items_get();
            foreach ($values as $c_value) {
                $min_weight = +0;
                foreach ($items as $c_row_id => $c_item)
                    $min_weight = min($min_weight, $c_item->weight);
                $c_new_item = new stdClass;
                $c_new_item->is_deleted = false;
                $c_new_item->weight = count($items) ? $min_weight - +5 : +0;
                $c_new_item->title = $c_value->file;
                $c_new_item->object = $c_value;
                if (Event::start_local('on_file_prepare', $widget, ['form' => $form, 'npath' => $npath, 'button' => $button, 'items' => &$items, 'new_item' => &$c_new_item])) {
                    $items[] = $c_new_item;
                    $widget->items_set($items);
                    Message::insert(new Text(
                        'File of type "%%_type" with title "%%_title" was appended.', [
                        'type'  => (new Text($widget->item_title))->render(),
                        'title' => $c_new_item->object->file]));
                } else {
                    $has_error = true;
                }
            }
            if ($has_error) {
                $widget->error_set_in();
            } else {
                Message::insert('Do not forget to save the changes!');
                return true;
            }
        }
    }

    static function on_button_click_delete($widget, $form, $npath, $button) {
        $items = $widget->items_get();
        $item_title = $items[$button->_id]->object->file;
        switch ($items[$button->_id]->object->get_current_state()) {
            case 'pre':
                if ($items[$button->_id]->object->delete_pre()) {
                    unset($items[$button->_id]);
                    $widget->items_set($items);
                    Message::insert(new Text_multiline([
                        'File of type "%%_type" with title "%%_title" was deleted physically.',
                        'Do not forget to save the changes!'], [
                        'type'  => (new Text($widget->item_title))->render(),
                        'title' => $item_title ]));
                    return true;
                } else {
                    $widget->error_set_in();
                }
                break;
            case 'fin':
                $items[$button->_id]->is_deleted = true;
                $widget->items_set($items);
                Message::insert(new Text_multiline([
                    'File of type "%%_type" with title "%%_title" was deleted.',
                    'Do not forget to save the changes!'], [
                    'type'  => (new Text($widget->item_title))->render(),
                    'title' => $item_title ]));
                return true;
        }
    }

    static function on_validate_final($widget, $form, $npath) {
        if (!$form->has_error()) {
            $has_error = false;
            $items = $widget->items_get();
            foreach ($items as $c_row_id => $c_item) {
                switch ($c_item->object->get_current_state()) {
                    case 'pre': # moving of 'pre' items into the directory 'files'
                        Token::insert('item_id_context', 'text', $c_row_id, null, 'page');
                        $c_result = $c_item->object->move_pre_to_fin(Dynamic::DIR_FILES.$widget->upload_dir.$c_item->object->file, $widget->fixed_name, $widget->fixed_type);
                        if ($c_result) {
                            Message::insert(new Text(
                                'File of type "%%_type" with title "%%_title" has been saved.', [
                                'type'  => (new Text($widget->item_title))->render(),
                                'title' => $c_item->object->file
                            ]));
                        } else {
                            $has_error = true;
                        }
                        break;
                    case 'fin': # deletion of 'fin' items which marked as 'deleted'
                        if (!empty($c_item->is_deleted)) {
                            $c_result = $c_item->object->delete_fin();
                            if ($c_result) {
                                unset($items[$c_row_id]);
                                Message::insert(new Text_multiline([
                                    'File of type "%%_type" with title "%%_title" was deleted physically.'], [
                                    'type'  => (new Text($widget->item_title))->render(),
                                    'title' => $c_item->object->file
                                ]));
                            } else {
                                $has_error = true;
                            }
                        }
                        break;
                    case null: # cache cleaning for lost files
                        unset($items[$c_row_id]);
                        break;
                }
            }
            $widget->items_set($items);
            $widget->build(true);
            if ($has_error) {
                $widget->error_set_in();
            }
            return !$has_error;
        }
    }

}
