<?php

##################################################################
### Copyright © 2017—2023 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_Radiobutton extends Field {

    public $title;
    public $title_position = 'bottom';
    public $attributes = ['data-type' => 'radiobutton'];
    public $element_attributes = [
        'type' => 'radio',
        'name' => 'radio'
    ];

    function auto_id_generate() {
        $name = $this->name_get();
        if ($name !== null) {
            static::$auto_ids[$name] = isset(static::$auto_ids[$name]) ? ++static::$auto_ids[$name] : 1;
            return 'auto_id-'.$name.'-'.$this->value_get();
        }
    }

    ###########################
    ### static declarations ###
    ###########################

    static function on_request_value_set($field, $form, $npath) {
        $name = $field->name_get();
        $type = $field->type_get();
        if ($name && $type) {
            if ($field->disabled_get()) return true;
            $new_values = Request::values_get($name, $form->source_get());
            $field->checked_set(Core::in_array($field->value_get(), $new_values));
        }
    }

    static function on_validate($field, $form, $npath) {
        $element = $field->child_select('element');
        $name = $field->name_get();
        $type = $field->type_get();
        if ($name && $type) {
            if ($field->disabled_get()) return true;
            $new_values = Request::values_get($name, $form->source_get());
            $result = static::validate_required($field, $form, $element, $new_values);
            $field->checked_set(Core::in_array($field->value_get(), $new_values));
            return $result;
        }
    }

    static function validate_required($field, $form, $element, &$new_values) {
        if ($field->required_get() && !Core::in_array($field->value_get(), $new_values)) {
            $field->error_set(
                'Field "%%_title" should be enabled!', ['title' => (new Text($field->title))->render() ]
            );
        } else {
            return true;
        }
    }

}
