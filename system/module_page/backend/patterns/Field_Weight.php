<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_Weight extends Field_Number {

    const INPUT_MIN_WEIGHT = -1000;
    const INPUT_MAX_WEIGHT = +1000;

    public $title = 'Weight';
    public $attributes = [
        'data-type' => 'weight'];
    public $element_attributes = [
        'type'      => 'number',
        'data-role' => 'weight',
        'name'      => 'weight',
        'required'  => true,
        'min'       => self::INPUT_MIN_WEIGHT,
        'max'       => self::INPUT_MAX_WEIGHT,
        'step'      => 1,
        'value'     => 0
    ];

}
