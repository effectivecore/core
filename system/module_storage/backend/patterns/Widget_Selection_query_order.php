<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use stdClass;

#[\AllowDynamicProperties]

class Widget_Selection_query_order extends Widget_Items {

    public $title = 'Sequence';
    public $item_title = 'order';
    public $attributes = [
        'data-type' => 'items-query-order'];
    public $group_name = 'widget_selection_query_order';
    public $state = 'closed';
    public $_instance;

    function value_get_prepared($options = []) { # @return: array | serialize(array)
        $result = [];
        $items = $this->value_get($options);
        if (count($items)) {
            Core::array_sort_by_number($items);
            foreach ($items as $c_row_id => $c_item) {
                $c_field_name_info = Field_Select_entity_field::parse_value($c_item->field_name);
                $c_type            = $c_item->type;
                if ($c_field_name_info !== null) {
                    $c_result_prefix = $this->_instance->main_entity_name === $c_field_name_info['entity_name'] ? $c_field_name_info['entity_field_name'] :     $c_field_name_info['entity_name'].'.'.$c_field_name_info['entity_field_name'];
                    $c_result_name   = $this->_instance->main_entity_name === $c_field_name_info['entity_name'] ? $c_field_name_info['entity_field_name'] : '~'.$c_field_name_info['entity_name'].'.'.$c_field_name_info['entity_field_name'];
                    $result['fields_!,'][$c_row_id] = [
                        $c_result_prefix.'_!f'   => $c_result_name,
                        $c_result_prefix.'_type' => $c_type
                    ];
                }
            }
        }
        if (!empty($options['return_serialized']))
             return serialize($result);
        else return           $result;
    }

    function value_set_prepared($value, $options = []) {
        $items = [];
        if (isset($value['fields_!,']) && is_array($value['fields_!,'])) {
            foreach ($value['fields_!,'] as $c_row_id => $c_order) {
                $c_keys   = array_keys  ($c_order);
                $c_values = array_values($c_order);
                $c_count  =        count($c_order);
                $c_weight = isset($c_weight) ? $c_weight - +5 : +0;
                $items[$c_row_id] = new stdClass;
                $items[$c_row_id]->weight = $c_weight;
                if ($c_count === 2 && substr($c_keys[0], -2) === '!f'   ) $items[$c_row_id]->field_name = $c_values[0][0] === '~' ? ltrim($c_values[0], '~') : $this->_instance->main_entity_name.'.'.$c_values[0];
                if ($c_count === 2 && substr($c_keys[1], -5) === '_type') $items[$c_row_id]->type       = $c_values[1];
            }
        }
        $this->value_set($items, $options);
    }

    ###########################
    ### static declarations ###
    ###########################

    static function widget_markup__item($widget, $item, $c_row_id) {
        $result = parent::widget_markup__item($widget, $item, $c_row_id);
        # control for order field name
        $field_select_field = new Field_Select_entity_field;
        $field_select_field->cform = $widget->cform;
        $field_select_field->attributes['data-role'] = 'field';
        $field_select_field->attributes['data-style'] = 'inline';
        $field_select_field->description_state = 'hidden';
        $field_select_field->title = 'Field';
        $field_select_field->build($widget->_instance->main_entity_name);
        $field_select_field->name_set($widget->group_control_name_get([$c_row_id, 'field_name']));
        $field_select_field->value_set($item->field_name ?? null);
        # control for order type
        $field_select_type = new Field_Select;
        $field_select_type->cform = $widget->cform;
        $field_select_type->attributes['data-role'] = 'type';
        $field_select_type->attributes['data-style'] = 'inline';
        $field_select_type->description_state = 'hidden';
        $field_select_type->title = 'Type';
        $field_select_type->items_set([
            'not_selected' => '- select -',
            'asc'          => 'ascending',
            'desc'         => 'descending']);
        $field_select_type->build();
        $field_select_type->name_set($widget->group_control_name_get([$c_row_id, 'type']));
        $field_select_type->value_set($item->type ?? null);
        # relate new controls with the widget
        $widget->controls['#field_name__'.$c_row_id] = $field_select_field;
        $widget->controls['#type__'      .$c_row_id] = $field_select_type;
        $result->child_select('body')->child_insert($field_select_field, 'field_select_field');
        $result->child_select('body')->child_insert($field_select_type , 'field_select_type');
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function on_button_click_insert($widget, $form, $npath, $button) {
        $min_weight = +0;
        $items = $widget->items_get();
        foreach ($items as $c_row_id => $c_item)
            $min_weight = min($min_weight, $c_item->weight);
        $new_item = new stdClass;
        $new_item->weight = count($items) ? $min_weight - +5 : +0;
        $new_item->field_name = '';
        $new_item->type       = '';
        $items[] = $new_item;
        $widget->items_set($items);
        Message::insert(new Text_multiline([
            'Item of type "%%_type" was appended.',
            'Do not forget to save the changes!'], [
            'type' => (new Text($widget->item_title))->render() ]));
        return true;
    }

    static function on_request_value_set($widget, $form, $npath) {
        $items = $widget->items_get();
        foreach ($items as $c_row_id => $c_item) {
            $c_item->weight     = (int)$widget->controls['#weight__'    .$c_row_id]->value_get();
            $c_item->field_name =      $widget->controls['#field_name__'.$c_row_id]->value_get();
            $c_item->type       =      $widget->controls['#type__'      .$c_row_id]->value_get(); }
        $widget->items_set($items);
    }

}
