<?php

##################################################################
### Copyright © 2017—2023 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use stdClass;

#[\AllowDynamicProperties]

class Group_Palette extends Group_Radiobuttons {

    public $title_tag_name = 'label';
    public $required_any = true;
    public $attributes = [
        'data-type' => 'palette',
        'role'      => 'group'
    ];

    function build() {
        if (!$this->is_builded) {
            foreach (Color::get_all() as $c_color) {
                $c_color_value_hex = $c_color->value_hex ?: '#ffffff';
                $c_color_value     = $c_color->value_hex ?: 'transparent';
                $this->items[$c_color->id] = new stdClass;
                $this->items[$c_color->id]->title = null;
                $this->items[$c_color->id]->description = null;
                $this->items[$c_color->id]->weight = +0;
                $this->items[$c_color->id]->group = $c_color->group;
                $this->items[$c_color->id]->element_attributes = [
                    'value' => $c_color->id,
                    'title' => new Text('color ID = "%%_id" and value = "%%_value"', ['id' => $c_color->id, 'value' => $c_color_value]),
                    'style' => ['background-color: '.$c_color_value_hex]
                ];
            }
            $fields = [];
            foreach ($this->items as $c_value => $c_info) {
                $c_field                     = new $this->field_class;
                $c_field->tag_name           =     $this->field_tag_name;
                $c_field->title_tag_name     =     $this->field_title_tag_name;
                $c_field->title_position     =     $this->field_title_position;
                $c_field->title              = $c_info->title;
                $c_field->description        = $c_info->description;
                $c_field->weight             = $c_info->weight;
                $c_field->element_attributes = $c_info->element_attributes + $this->attributes_select('element_attributes') + $c_field->attributes_select('element_attributes');
                $c_field->build();
                $c_field->required_set(isset($this->required[$c_value]));
                $c_field-> checked_set(isset($this->checked [$c_value]));
                $c_field->disabled_set(isset($this->disabled[$c_value]));
                if (!isset($fields[$c_info->group]))
                           $fields[$c_info->group] = new Markup('x-sub-group', ['data-sub-group' => true, 'data-sub-group' => $c_info->group]);
                $fields[$c_info->group]->child_insert($c_field, $c_value);
            }
            $this->children_update($fields);
            $this->is_builded = true;
        }
    }

    function render_self() {
        $html_name = 'f_opener_'.$this->name_get_complex();
        if ($this->title && (bool)$this->title_is_visible !== true) return $this->render_opener().(new Markup($this->title_tag_name, $this->title_attributes + ['for' => $html_name, 'aria-hidden' => 'true'], is_string($this->title) ? new Text($this->title, [], $this->title_is_apply_translation, $this->title_is_apply_tokens) : $this->title))->render();
        if ($this->title && (bool)$this->title_is_visible === true) return $this->render_opener().(new Markup($this->title_tag_name, $this->title_attributes + ['for' => $html_name                         ], is_string($this->title) ? new Text($this->title, [], $this->title_is_apply_translation, $this->title_is_apply_tokens) : $this->title))->render();
    }

    function render_opener() {
        $color_id        = $this->value_get() ?: 'white';
        $color_value_hex = Color::get($color_id)->value_hex ?: '#ffffff';
        $html_name       = 'f_opener_'.$this->name_get_complex();
        $form_id         = Request::value_get('form_id');
        $submit_value    = Request::value_get($html_name);
        $has_error       = $this->has_error_in();
        if ($form_id === ''                                                      ) /*               default = closed */ return (new Markup_simple('input', ['type' => 'checkbox', 'role' => 'button', 'data-opener-type' => 'palette', 'title' => new Text('press to show or hide available colors'), 'name' => $html_name, 'id' => $html_name, 'checked' => true,                           'value' => $color_id, 'style' => ['background: '.$color_value_hex]]))->render();
        if ($form_id !== '' && $has_error !== true && $submit_value !== $color_id) /* no error + no checked = opened */ return (new Markup_simple('input', ['type' => 'checkbox', 'role' => 'button', 'data-opener-type' => 'palette', 'title' => new Text('press to show or hide available colors'), 'name' => $html_name, 'id' => $html_name, 'checked' => null,                           'value' => $color_id, 'style' => ['background: '.$color_value_hex]]))->render();
        if ($form_id !== '' && $has_error !== true && $submit_value === $color_id) /* no error +    checked = closed */ return (new Markup_simple('input', ['type' => 'checkbox', 'role' => 'button', 'data-opener-type' => 'palette', 'title' => new Text('press to show or hide available colors'), 'name' => $html_name, 'id' => $html_name, 'checked' => true,                           'value' => $color_id, 'style' => ['background: '.$color_value_hex]]))->render();
        if ($form_id !== '' && $has_error === true && $submit_value !== $color_id) /*    error + no checked = opened */ return (new Markup_simple('input', ['type' => 'checkbox', 'role' => 'button', 'data-opener-type' => 'palette', 'title' => new Text('press to show or hide available colors'), 'name' => $html_name, 'id' => $html_name, 'checked' => null, 'aria-invalid' => 'true', 'value' => $color_id, 'style' => ['background: '.$color_value_hex]]))->render();
        if ($form_id !== '' && $has_error === true && $submit_value === $color_id) /*    error +    checked = opened */ return (new Markup_simple('input', ['type' => 'checkbox', 'role' => 'button', 'data-opener-type' => 'palette', 'title' => new Text('press to show or hide available colors'), 'name' => $html_name, 'id' => $html_name, 'checked' => null, 'aria-invalid' => 'true', 'value' => $color_id, 'style' => ['background: '.$color_value_hex]]))->render();
    }

}
