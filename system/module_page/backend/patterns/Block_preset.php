<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use stdClass;

#[\AllowDynamicProperties]

class Block_preset {

    public $id;                      # copy to: (new Block)->_preset->id
    public $managing_group = 'Text'; # copy to: (new Block)->_preset->managing_group
    public $managing_title;          # copy to: (new Block)->_preset->managing_title
    public $origin = 'nosql';        # copy to: (new Block)->_preset->origin
    public $module_id;               # copy to: (new Block)->_preset->module_id
    public $weight = +0;             # copy to: (new Block)->weight

    function __construct($id = null, $managing_group = null, $managing_title = null, $weight = +0) {
        if ($id            ) $this->id             = $id;
        if ($managing_group) $this->managing_group = $managing_group;
        if ($managing_title) $this->managing_title = $managing_title;
        if ($weight        ) $this->weight         = $weight;
    }

    function block_make() {
        $block = new Block;
        $block->_preset = new stdClass;
        foreach ($this as $c_key => $c_value) {
            if ($c_key === 'attributes'    ) {$block->attributes             += $this->attributes;     continue;}
            if ($c_key === 'id'            ) {$block->_preset->id             = $this->id;             continue;}
            if ($c_key === 'managing_group') {$block->_preset->managing_group = $this->managing_group; continue;}
            if ($c_key === 'managing_title') {$block->_preset->managing_title = $this->managing_title; continue;}
            if ($c_key === 'module_id'     ) {$block->_preset->module_id      = $this->module_id;      continue;}
            if ($c_key === 'origin'        ) {$block->_preset->origin         = $this->origin;         continue;}
            $block->{$c_key} = $this->{$c_key}; }
        return $block;
    }

    ###########################
    ### static declarations ###
    ###########################

    protected static $cache;
    protected static $is_init_nosql   = false;
    protected static $is_init_dynamic = false;

    static function cache_cleaning() {
        static::$cache           = null;
        static::$is_init_nosql   = false;
        static::$is_init_dynamic = false;
    }

    static function init() {
        if (!static::$is_init_nosql) {
             static::$is_init_nosql = true;
            foreach (Storage::get('data')->select_array('block_presets') as $c_module_id => $c_presets) {
                foreach ($c_presets as $c_preset) {
                    if (isset(static::$cache[$c_preset->id])) Console::report_about_duplicate('block_presets', $c_preset->id, $c_module_id, static::$cache[$c_preset->id]);
                              static::$cache[$c_preset->id] = $c_preset;
                              static::$cache[$c_preset->id]->origin = 'nosql';
                              static::$cache[$c_preset->id]->module_id = $c_module_id;
                }
            }
        }
    }

    static function init_dynamic($id = null) {
        if ($id === null && !static::$is_init_dynamic) {static::$is_init_dynamic = true; Event::start('on_block_presets_dynamic_build', null               );}
        if ($id !== null                             ) {                                 Event::start('on_block_presets_dynamic_build', null, ['id' => $id]);}
    }

    static function select_all($origin = null) {
        if ($origin === 'nosql'  ) {static::init();                        }
        if ($origin === 'dynamic') {static::init(); static::init_dynamic();}
        if ($origin ===  null    ) {static::init(); static::init_dynamic();}
        return static::$cache;
    }

    static function select($id) {
        static::init();
        if (isset(static::$cache[$id]) === false) static::init_dynamic($id);
        return static::$cache[$id] ?? null;
    }

    static function insert($id, $managing_group = null, $managing_title = null, $extra = [], $weight = +0, $module_id = null) {
        static::init();
        $new_preset = new static($id, $managing_group, $managing_title, $weight);
        $new_preset->origin = 'dynamic';
        $new_preset->module_id = $module_id;
        foreach ($extra as $c_key => $c_value) $new_preset->{$c_key} = $c_value;
               static::$cache[$id] = $new_preset;
        return static::$cache[$id];
    }

}
