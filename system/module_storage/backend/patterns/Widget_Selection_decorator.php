<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Widget_Selection_decorator extends Control implements Controls_Group {

    use Controls_Group__Shared;

    public $tag_name = 'x-widget';
    public $title_tag_name = 'label';
    public $title_attributes = [
        'data-widget-title' => true];
    public $content_tag_name = 'x-widget-content';
    public $content_attributes = [
        'data-widget-content' => true,
        'data-nested-content' => true];
    public $group_name = 'decorator_settings';
    public $attributes = [
        'data-type' => 'decorator-settings',
        'role'      => 'group'];
    public $_instance;

    function build() {
        if (!$this->is_builded) {
            $this->child_insert(static::widget_markup($this), 'manage');
            $this->is_builded = true;
        }
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    function value_get($options = []) { # @return: array | serialize(array)
        $result = [];
        $result['view_type'         ] = $this->controls['#view_type'         ]->     value_get();
        $result['template_selection'] = $this->controls['#template_selection']->     value_get();
        $result['template_decorator'] = $this->controls['#template_decorator']->     value_get();
        $result['template_item'     ] = $this->controls['#template_item'     ]->     value_get();
        $result['mapping'           ] = $this->controls['#mapping'           ]->value_data_get()->mapping ?? [];
        if (!empty($options['return_serialized']))
             return serialize($result);
        else return           $result;
    }

    function value_set($value) {
        $this->value_set_initial($value);
        if (Core::data_is_serialized($value)) $value = unserialize($value);
        if ($value === null) $value = [];
        if ($value ===  '' ) $value = [];
        if (is_array($value)) {
            if (!empty($value['view_type'         ])) $this->controls['#view_type'         ]->     value_set($value['view_type']);
            if (!empty($value['template_selection'])) $this->controls['#template_selection']->     value_set($value['template_selection']);
            if (!empty($value['template_decorator'])) $this->controls['#template_decorator']->     value_set($value['template_decorator']);
            if (!empty($value['template_item'     ])) $this->controls['#template_item'     ]->     value_set($value['template_item']);
            if (!empty($value['mapping'           ])) $this->controls['#mapping'           ]->value_data_set($value['mapping'], 'mapping');
        }
    }

    function disabled_get() {
        return false;
    }

    ###########################
    ### static declarations ###
    ###########################

    static function widget_markup($widget) {
        $result = new Node;

        # control for type of view
        $field_select_view_type = new Field_Select;
        $field_select_view_type->cform = $widget->cform;
        $field_select_view_type->attributes['data-role'] = 'view-type';
        $field_select_view_type->title = 'View type';
        $field_select_view_type->disabled['tree'] = 'tree';
        $field_select_view_type->items_set([
            'not_selected'   => '- select -',
            'table'          => 'Table',
            'table-adaptive' => 'Table (adaptive)',
            'table-dl'       => 'Table (DL)',
            'ul'             => 'Unordered list',
            'dl'             => 'Definition list',
            'template'       => 'Template',
            'tree'           => 'Tree']);
        $field_select_view_type->build();
        $field_select_view_type->name_set($widget->group_control_name_get(['view_type']));
        $field_select_view_type->value_set('table');

        ##############################
        ### controls for templates ###
        ##############################

        $fieldset_template_settings = new Fieldset('Template settings');
        $fieldset_template_settings->state = 'closed';

        $templates_list = [];
        $templates = Template::get_all();
        foreach ($templates as $c_template) {
            if (!empty($c_template->is_ready_for_selection)) {
                $c_name = preg_replace('%__embedded$%S', '', $c_template->name);
                $templates_list[$c_name] = ' '.$c_name; }}
        Core::array_sort($templates_list);

        $field_select_template_selection = new Field_Select;
        $field_select_template_selection->cform = $widget->cform;
        $field_select_template_selection->attributes['data-role'] = 'template-selection';
        $field_select_template_selection->title = 'Template for selection';
        $field_select_template_selection->items_set(['not_selected' => '- select -'] + $templates_list);
        $field_select_template_selection->build();
        $field_select_template_selection->name_set($widget->group_control_name_get(['template_selection']));
        $field_select_template_selection->value_set('markup_html');

        $field_select_template_decorator = new Field_Select;
        $field_select_template_decorator->cform = $widget->cform;
        $field_select_template_decorator->attributes['data-role'] = 'template-decorator';
        $field_select_template_decorator->title = 'Template for decorator';
        $field_select_template_decorator->items_set(['not_selected' => '- select -'] + $templates_list);
        $field_select_template_decorator->build();
        $field_select_template_decorator->name_set($widget->group_control_name_get(['template_decorator']));
        $field_select_template_decorator->value_set('markup_html');

        $field_select_template_item = new Field_Select;
        $field_select_template_item->cform = $widget->cform;
        $field_select_template_item->attributes['data-role'] = 'template-item';
        $field_select_template_item->title = 'Template for item';
        $field_select_template_item->items_set(['not_selected' => '- select -'] + $templates_list);
        $field_select_template_item->build();
        $field_select_template_item->name_set($widget->group_control_name_get(['template_item']));
        $field_select_template_item->value_set('content');

        # control for mapping
        $field_textarea_data_mapping = new Field_Textarea_data;
        $field_textarea_data_mapping->cform = $widget->cform;
        $field_textarea_data_mapping->attributes['data-role'] = 'data-mapping';
        $field_textarea_data_mapping->title = 'Relations for item template: Variable in template ← Field in DB';
        $field_textarea_data_mapping->data_validator_id = 'mapping';
        $field_textarea_data_mapping->element_attributes['rows'] = 17;
        $field_textarea_data_mapping->build();
        $field_textarea_data_mapping->name_set($widget->group_control_name_get(['mapping']));
        $field_textarea_data_mapping->required_set(false);
        $field_textarea_data_mapping->maxlength_set(0xffff);
        $field_textarea_data_mapping->value_data_set([
            'id'              => 'id',
            'id_tree'         => 'id_tree',
            'id_parent'       => 'id_parent',
            'description'     => 'description',
            'title'           => 'title',
            'url'             => 'url',
            'src'             => 'path',
            'path'            => 'path',
            'poster'          => 'poster_path',
            'text'            => 'text',
            'attributes'      => 'this_attributes',
            'link_attributes' => 'link_attributes',
            'created'         => 'created',
            'updated'         => 'updated',
            'is_embedded'     => 'is_embedded',
            'weight'          => 'weight',
            'children'        => 'items',
            'items'           => 'items',
        ], 'mapping');

        # relate new controls with the widget
        $widget->controls['#view_type'         ] = $field_select_view_type;
        $widget->controls['#template_selection'] = $field_select_template_selection;
        $widget->controls['#template_decorator'] = $field_select_template_decorator;
        $widget->controls['#template_item'     ] = $field_select_template_item;
        $widget->controls['#mapping'           ] = $field_textarea_data_mapping;
        $fieldset_template_settings->child_insert($field_select_template_selection, 'field_select_template_selection');
        $fieldset_template_settings->child_insert($field_select_template_decorator, 'field_select_template_decorator');
        $fieldset_template_settings->child_insert($field_select_template_item     , 'field_select_template_item');
        $fieldset_template_settings->child_insert($field_textarea_data_mapping    , 'field_textarea_data_mapping');
        $result->child_insert($field_select_view_type     , 'field_select_view_type');
        $result->child_insert($fieldset_template_settings , 'fieldset_template_settings');
        return $result;
    }

}
