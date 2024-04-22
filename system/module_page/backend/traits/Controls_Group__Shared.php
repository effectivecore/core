<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

trait Controls_Group__Shared {

    function group_name_get() {
        return $this->group_name;
    }

    function group_control_name_get($parts = [], $suffix = '') {
        return $this->group_name.'__'.implode('__', array_filter($parts, 'strlen')).$suffix;
    }

}
