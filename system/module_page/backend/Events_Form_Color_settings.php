<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\page;

abstract class Events_Form_Color_settings {

    static function on_init($event, $form, $items) {
    }

    static function on_submit($event, $form, $items) {
        switch ($form->clicked_button->value_get()) {
            case 'save':
                break;
            case 'reset':
                break;
        }
    }

}
