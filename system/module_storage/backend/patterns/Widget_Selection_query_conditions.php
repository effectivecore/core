<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use stdClass;

#[\AllowDynamicProperties]

class Widget_Selection_query_conditions extends Widget_Items {

    public $title = 'Conditions';
    public $item_title = 'condition';
    public $attributes = [
        'data-type' => 'items-query-conditions'];
    public $group_name = 'widget_selection_query_conditions';
    public $state = 'closed';
    public $_instance;

    function value_get_prepared($options = []) { # @return: array | serialize(array)
        $result = [];
        $items = $this->value_get($options);
        if (count($items)) {
            Core::array_sort_by_number($items);
            foreach ($items as $c_row_id => $c_item) {
                $c_field_name_info = Field_Select_entity_field::parse_value($c_item->field_name);
                $c_operator        = $c_item->operator;
                $c_value           = $c_item->value;
                if ($c_field_name_info !== null) {
                    $c_result_prefix = $this->_instance->main_entity_name === $c_field_name_info['entity_name'] ? $c_field_name_info['entity_field_name'] :     $c_field_name_info['entity_name'].'.'.$c_field_name_info['entity_field_name'];
                    $c_result_name   = $this->_instance->main_entity_name === $c_field_name_info['entity_name'] ? $c_field_name_info['entity_field_name'] : '~'.$c_field_name_info['entity_name'].'.'.$c_field_name_info['entity_field_name'];
                    switch ($c_operator) {
                        case     'is null':
                        case 'is not null':
                            $result['conjunction_!and'][$c_row_id] = [
                                $c_result_prefix.'_!f'       => $c_result_name,
                                $c_result_prefix.'_operator' => $c_operator];
                            break;
                        case     'in (':
                        case 'not in (':
                            $result['conjunction_!and'][$c_row_id] = [
                                $c_result_prefix.'_!f'                => $c_result_name,
                                $c_result_prefix.'_in_begin_operator' => $c_operator,
                                $c_result_prefix.'_in_!v'             => explode(', ', $c_value),
                                $c_result_prefix.'_in_end_operator'   => ')'];
                            break;
                        default:
                            $result['conjunction_!and'][$c_row_id] = [
                                $c_result_prefix.'_!f'       => $c_result_name,
                                $c_result_prefix.'_operator' => $c_operator,
                                $c_result_prefix.'_!v'       => $c_value
                            ];
                    }
                }
            }
        }
        if (!empty($options['return_serialized']))
             return serialize($result);
        else return           $result;
    }

    function value_set_prepared($value, $options = []) {
        $items = [];
        if (isset($value['conjunction_!and']) && is_array($value['conjunction_!and'])) {
            foreach ($value['conjunction_!and'] as $c_row_id => $c_condition) {
                $c_keys   = array_keys  ($c_condition);
                $c_values = array_values($c_condition);
                $c_count  =        count($c_condition);
                $c_weight = isset($c_weight) ? $c_weight - +5 : +0;
                $items[$c_row_id] = new stdClass;
                $items[$c_row_id]->weight = $c_weight;
                if ($c_count  >  1 && substr($c_keys[0], -2) === '!f'      ) $items[$c_row_id]->field_name = $c_values[0][0] === '~' ? ltrim($c_values[0], '~') : $this->_instance->main_entity_name.'.'.$c_values[0];
                if ($c_count  >  1 && substr($c_keys[1], -8) === 'operator') $items[$c_row_id]->operator   = $c_values[1];
                if ($c_count === 4 && substr($c_keys[2], -2) === '!v'      ) $items[$c_row_id]->value = implode(', ', $c_values[2]); /* case for: 'in (' + 'not in (' */
                if ($c_count === 3 && substr($c_keys[2], -2) === '!v'      ) $items[$c_row_id]->value =               $c_values[2];
                if ($c_count === 2                                         ) $items[$c_row_id]->value = 'n/a'; /* case for: 'is null' + 'is not null' */
            }
        }
        $this->value_set($items, $options);
    }

    ###########################
    ### static declarations ###
    ###########################

    static function widget_markup__item($widget, $item, $c_row_id) {
        $result = parent::widget_markup__item($widget, $item, $c_row_id);
        # control for condition field name
        $field_select_entity_field = new Field_Select_entity_field;
        $field_select_entity_field->cform = $widget->cform;
        $field_select_entity_field->attributes['data-role'] = 'field';
        $field_select_entity_field->attributes['data-style'] = 'inline';
        $field_select_entity_field->description_state = 'hidden';
        $field_select_entity_field->title = 'Field';
        $field_select_entity_field->build($widget->_instance->main_entity_name);
        $field_select_entity_field->name_set($widget->group_control_name_get([$c_row_id, 'field_name']));
        $field_select_entity_field->value_set($item->field_name ?? null);
        # control for condition operator
        $field_select_operator = new Field_Select;
        $field_select_operator->cform = $widget->cform;
        $field_select_operator->attributes['data-role'] = 'operator';
        $field_select_operator->attributes['data-style'] = 'inline';
        $field_select_operator->description_state = 'hidden';
        $field_select_operator->title = 'Operator';
        $field_select_operator->items_set([
            'not_selected' => '- select -',
            '<'            => '<',
            '<='           => '<=',
            '='            => '=',
            '>'            => '>',
            '>='           => '>=',
            '<>'           => '<>',
            'in ('         => 'IN',
            'not in ('     => 'NOT IN',
            'like'         => 'LIKE',
            'not like'     => 'NOT LIKE',
            'is null'      => 'IS NULL',
            'is not null'  => 'IS NOT NULL']);
        $field_select_operator->build();
        $field_select_operator->name_set($widget->group_control_name_get([$c_row_id, 'operator']));
        $field_select_operator->value_set($item->operator ?? null);
        # control for condition value
        $field_text_value = new Field_Text;
        $field_text_value->attributes['data-role'] = 'value';
        $field_text_value->attributes['data-style'] = 'inline';
        $field_text_value->description_state = 'hidden';
        $field_text_value->title = 'Value';
        $field_text_value->build();
        $field_text_value->name_set($widget->group_control_name_get([$c_row_id, 'value']));
        $field_text_value->minlength_set(0);
        $field_text_value->maxlength_set(10000);
        $field_text_value->value_set($item->value ?? null);
        # relate new controls with the widget
        $widget->controls['#field_name__'.$c_row_id] = $field_select_entity_field;
        $widget->controls['#operator__'  .$c_row_id] = $field_select_operator;
        $widget->controls['#value__'     .$c_row_id] = $field_text_value;
        $result->child_select('body')->child_insert($field_select_entity_field, 'field_select_entity_field');
        $result->child_select('body')->child_insert($field_select_operator    , 'field_select_operator');
        $result->child_select('body')->child_insert($field_text_value         , 'field_text_value');
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
        $new_item->operator   = '';
        $new_item->value      = '';
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
            $c_item->operator   =      $widget->controls['#operator__'  .$c_row_id]->value_get();
            $c_item->value      =      $widget->controls['#value__'     .$c_row_id]->value_get(); }
        $widget->items_set($items);
    }

}
