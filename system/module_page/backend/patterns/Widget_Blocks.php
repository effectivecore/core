<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Widget_Blocks extends Widget_Items {

    public $attributes = [
        'data-type' => 'items-blocks',
        'data-with-settings' => true
    ];

    public $title;
    public $title__not_selected__widget_insert = '- select -';
    public $item_title = 'Block';

    public $content_tag_name = null;

    public $group_name = 'widget_blocks';
    public $area_id;

    function __construct($area_id, $attributes = [], $weight = +0) {
        $this->area_id = $area_id;
        parent::__construct($attributes, $weight);
        static::$instances[$area_id] = $this;
    }

    ###########################
    ### static declarations ###
    ###########################

    static $instances = [];

    static function widget_markup__items_group($widget) {
        return new Markup(
            'x-widgets-group', ['data-rearrange-parent-id' => $widget->area_id] + $widget->attributes_item_group,
        );
    }

    static function widget_markup__no_items($widget) {
        return new Node;
    }

    static function widget_markup__item($widget, $item, $c_row_id) {
        $result = parent::widget_markup__item($widget, $item, $c_row_id);
        # control for parent
        $field_parent = new Field_Hidden(
            $widget->group_control_name_get([$c_row_id, 'parent']), $widget->area_id ?? null, ['data-role' => 'parent']
        );
        # info markup
        $presets = Block_preset::select_all();
        $title_markup = isset($presets[$item->id]) ?
                             [$presets[$item->id]->managing_group, ': ',
                              $presets[$item->id]->managing_title] : 'ORPHANED BLOCK';
        $info_markup = new Markup('x-info' , [], [
            'title' => new Markup('x-title', [], $title_markup),
            'id'    => new Markup('x-id'   , [], new Text_simple($item->id) ) ]);
        # create widget_settings and prepare item (copy properties from Block_preset to Block_preset_link)
        if ($item instanceof Block_preset_link) {
            if (!isset($item->title) ||
                !isset($item->attributes)) {
                $preset = $item->preset_make();
                $item->title                      = $preset->title;
                $item->title_is_visible           = $preset->title_is_visible;
                $item->title_is_apply_translation = $preset->title_is_apply_translation ?? true;
                $item->title_is_apply_tokens      = $preset->title_is_apply_tokens ?? false;
                $item->attributes                 = $preset->attributes; }}
        $widget_settings = new Widget_Block_settings($widget, $item, $c_row_id);
        $widget_settings->build();
        # relate new controls with the widget
        $widget->controls['#parent__'.$c_row_id] = $field_parent;
        $result->child_select('body')->child_insert($field_parent   , 'field_parent');
        $result->child_select('body')->child_insert($info_markup    , 'info');
        $result->child_select('foot')->child_insert($widget_settings, 'settings');
        return $result;
    }

    static function widget_markup__insert($widget) {
        $result = new Markup('x-widget', ['data-type' => 'insert']);
        # control with type of new item
        $field_select_block_preset = new Field_Select_block_preset('Insert block');
        $field_select_block_preset->cform = $widget->cform;
        $field_select_block_preset->title__not_selected = $widget->title__not_selected__widget_insert;
        $field_select_block_preset->build();
        $field_select_block_preset->name_set($widget->group_control_name_get(['insert']));
        $field_select_block_preset->required_set(false);
        # button for insertion of the new item
        $button_insert = new Button(null, ['data-style' => 'insert', 'title' => new Text('insert')]);
        $button_insert->break_on_validate = true;
        $button_insert->build();
        $button_insert->value_set($widget->group_control_name_get(['insert']));
        $button_insert->_type = 'insert';
        # relate new controls with the widget
        $widget->controls['#insert'] = $field_select_block_preset;
        $widget->controls['~insert'] = $button_insert;
        $result->child_insert($field_select_block_preset, 'field_select_block_preset');
        $result->child_insert($button_insert            , 'button_insert');
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function on_button_click_insert($widget, $form, $npath, $button) {
        $widget->controls['#insert']->required_set(true);
        $result_validation = Field_Select::on_validate($widget->controls['#insert'], $form, $npath);
        $widget->controls['#insert']->required_set(false);
        if ($result_validation) {
            $min_weight = +0;
            $items = $widget->items_get();
            foreach ($items as $c_row_id => $c_item)
                $min_weight = min($min_weight, $c_item->weight);
            $new_item = new Block_preset_link($widget->controls['#insert']->value_get());
            $new_item->weight = count($items) ? $min_weight - +5 : +0;
            $items[] = $new_item;
            $widget->items_set($items);
            $widget->controls['#insert']->value_set('');
            Message::insert(new Text_multiline([
                'Item of type "%%_type" with ID = "%%_id" was appended.',
                'Do not forget to save the changes!'], [
                'type' => (new Text($widget->item_title))->render(),
                'id'   => $new_item->id ]));
            return true;
        }
    }

    static function on_request_value_set_after($widget, $form, $npath) {
        $items = $widget->items_get();
        foreach ($items as $c_row_id => $c_item) {
            if (isset($widget->controls['#parent__'.$c_row_id])) {
                $c_area_id = $widget->controls['#parent__'.$c_row_id]->value_request_get();
                if ($c_area_id && $c_area_id !== $widget->area_id) {
                    if (isset($widget->cform->_area_list[$c_area_id])) {
                        if (isset(static::$instances[$c_area_id])) {
                            # transfer item to other widget (area)
                            $destination_items = static::$instances[$c_area_id]->items_get();
                            $destination_items[]= $c_item;
                            unset($items[$c_row_id]);
                            static::$instances[$c_area_id]->items_set(
                                $destination_items
                            );
                            # fix request values
                            $c_old_prefix = 'widget_blocks__'.$widget->area_id.'__'.$c_row_id.'__';
                            $c_new_prefix = 'widget_blocks__'.$c_area_id.'__'.array_key_last($destination_items).'__';
                            Request::values_clone(
                                $c_old_prefix,
                                $c_new_prefix
                            );
                        }
                    }
                }
            }
        }
        $widget->items_set($items);
    }

}
