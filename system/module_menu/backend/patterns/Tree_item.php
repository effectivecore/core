<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Tree_item extends Node {

    public $template = 'tree_item';
    public $template_children = 'tree_item_children';
    public $element_attributes = [
        'role' => 'treeitem'];
    public $id;
    public $id_parent;
    public $id_tree;
    public $id_real;
    public $title;
    public $url;
    public $url_hidden;
    public $extra;
    public $access;
    public $origin = 'nosql'; # nosql | sql | dynamic
    public $cache_href;
    public $cache_href_hidden;

    function __construct($title = null, $id = null, $id_parent = null, $id_tree = null, $url = null, $access = null, $attributes = [], $element_attributes = [], $weight = +0) {
        if ($title             ) $this->title              = $title;
        if ($id                ) $this->id                 = $id;
        if ($id_parent         ) $this->id_parent          = $id_parent;
        if ($id_tree           ) $this->id_tree            = $id_tree;
        if ($url               ) $this->url                = $url;
        if ($access            ) $this->access             = $access;
        if ($element_attributes) $this->element_attributes = $element_attributes +
        $this->element_attributes;
        parent::__construct($attributes, [], $weight);
    }

    function build() {
        if (!$this->is_builded) {
            $this->attribute_insert('data-id', $this->id, 'attributes', true);
            foreach (static::select_all_by_id_tree($this->id_tree) as $c_item) {
                if ($c_item->id_parent === $this->id) {
                    $this->child_insert($c_item, $c_item->id);
                    $c_item->build(); }}
            $this->is_builded = true;
        }
    }

    function href_get       () {if ($this->cache_href        === null) $this->cache_href        = Token::apply($this->url       ); return $this->cache_href;       }
    function href_hidden_get() {if ($this->cache_href_hidden === null) $this->cache_href_hidden = Token::apply($this->url_hidden); return $this->cache_href_hidden;}

    function is_active() {
        $href = $this->href_get();
        if ($href && URL::is_active($href, 'path')) {
            return true;
        }
    }

    function is_active_trail() {
        $href        = $this->href_get       ();
        $href_hidden = $this->href_hidden_get();
        if ($href        && URL::is_active_trail($href       )) return true;
        if ($href_hidden && URL::is_active_trail($href_hidden)) return true;
    }

    function render() {
        $manage_mode = Tree::select($this->id_tree)->manage_mode;
        if (Access::check($this->access)) {
            $rendered_self = $manage_mode ?
                $this->render_self__in_manage_mode() :
                $this->render_self();
            $rendered_children = '';
            if ($manage_mode !== 'rearrange' && $this->children_select_count()) $rendered_children = (Template::make_new(Template::pick_name($this->template_children), ['children' => $this->render_children($this->children_select(true))                                                                             ] ))->render();
            if ($manage_mode === 'rearrange'                                  ) $rendered_children = (Template::make_new(Template::pick_name($this->template_children), ['children' => $this->render_children($this->children_select(true)), 'attributes' => ['data-rearrange-parent-id' => $this->id_real ?? $this->id]] ))->render();
            return (Template::make_new(Template::pick_name($this->template), [
                'attributes' => $this->render_attributes(),
                'self'       => $rendered_self,
                'children'   => $rendered_children
            ]))->render();
        }
    }

    function render_self() {
        $href = $this->href_get();
        $has_title = $this->attribute_select('title', 'element_attributes') !== null;
        if ($href && $has_title === false) $this->attribute_insert('title', new Text('click to open the menu item "%%_title"', ['title' => (new Text($this->title, [], true, true))->render() ]), 'element_attributes', true);
        if ($href                        ) $this->attribute_insert('href', $href,                 'element_attributes', true);
        if ($this->is_active      ()     ) $this->attribute_insert('aria-selected'      , 'true', 'element_attributes', true);
        if ($this->is_active_trail()     ) $this->attribute_insert('data-selected-trail', 'true', 'element_attributes', true);
        return (new Markup('a', $this->attributes_select('element_attributes'),
            new Text($this->title, [], true, true)
        ))->render();
    }

    function render_self__in_manage_mode() {
        if (!$this->url)
             $attributes = $this->attributes_select('element_attributes') + ['data-no-url' => true];
        else $attributes = $this->attributes_select('element_attributes');
        return (new Markup('x-item', $attributes, [
            new Markup('x-icon'),
            new Markup('x-info', [], [
                new Markup('x-title', [], $this->title),
                new Markup('x-extra', [], $this->extra), $this->url ? URL::url_to_markup($this->url) :
                new Markup('x-url', [], 'No URL.')
            ])
        ]))->render();
    }

    ###########################
    ### static declarations ###
    ###########################

    protected static $cache;
    protected static $is_init_nosql_by_tree = [];
    protected static $is_init___sql_by_tree = [];

    static function cache_cleaning() {
        static::$cache                 = null;
        static::$is_init_nosql_by_tree = [];
        static::$is_init___sql_by_tree = [];
    }

    static function init() {
        if (static::$is_init_nosql_by_tree === []) {
            foreach (Storage::get('data')->select_array('tree_items') as $c_module_id => $c_tree_items) {
                foreach ($c_tree_items as $c_row_id => $c_tree_item) {
                    if (isset(static::$cache[$c_tree_item->id])) Console::report_about_duplicate('tree_items', $c_tree_item->id, $c_module_id, static::$cache[$c_tree_item->id]);
                              static::$cache[$c_tree_item->id] = $c_tree_item;
                              static::$cache[$c_tree_item->id]->module_id = $c_module_id;
                              static::$cache[$c_tree_item->id]->origin = 'nosql';
                              static::$is_init_nosql_by_tree[$c_tree_item->id_tree] = true;
                }
            }
        }
    }

    static function init_sql($id_tree) {
        if (isset(static::$is_init_nosql_by_tree[$id_tree])) return;
        if (isset(static::$is_init___sql_by_tree[$id_tree])) return;
        if (Tree::select($id_tree) &&
            Tree::select($id_tree)->origin === 'sql') {
            static::$is_init___sql_by_tree[$id_tree] = true;
            $instances = Entity::get('tree_item')->instances_select([
                'where' => [
                    'id_tree_!f'       => 'id_tree',
                    'id_tree_operator' => '=',
                    'id_tree_!v'       => $id_tree]], 'id');
            foreach ($instances as $c_instance) {
                $c_tree_item = new static(
                    $c_instance->title,
                    $c_instance->id,
                    $c_instance->id_parent,
                    $c_instance->id_tree,
                    $c_instance->url,
                    $c_instance->access,
                    Widget_Attributes::value_to_attributes($c_instance->     attributes) ?? [],
                    Widget_Attributes::value_to_attributes($c_instance->link_attributes) ?? [],
                    $c_instance->weight);
                static::$cache[$c_tree_item->id] = $c_tree_item;
                static::$cache[$c_tree_item->id]->module_id = $c_instance->module_id;
                static::$cache[$c_tree_item->id]->origin = 'sql';
            }
        }
    }

    static function select_all_by_id_tree($id_tree) {
        static::init    (        );
        static::init_sql($id_tree);
        $result = [];
        if (is_array(static::$cache))
            foreach (static::$cache as $c_item)
                if ($c_item->id_tree === $id_tree)
                    $result[$c_item->id] = $c_item;
        return $result;
    }

    static function select($id, $id_tree) {
        static::init();
        if (empty(static::$cache[$id]) && $id_tree === null) {
            $instance = (new Instance('tree_item', [
                'id' => $id
            ]))->select();
            if (!empty($instance->id_tree)) $id_tree =
                       $instance->id_tree; }
        static::init_sql($id_tree);
        return static::$cache[$id] ?? null;
    }

    static function insert($title, $id, $id_parent, $id_tree, $url = null, $access = null, $attributes = [], $element_attributes = [], $weight = +0, $module_id = null) {
        static::init    (        );
        static::init_sql($id_tree);
        $new_item = new static($title, $id, $id_parent, $id_tree, $url, $access, $attributes, $element_attributes, $weight);
               static::$cache[$id] = $new_item;
               static::$cache[$id]->module_id = $module_id;
               static::$cache[$id]->origin = 'dynamic';
        return static::$cache[$id];
    }

    static function delete($id, $id_tree) {
        static::init    (        );
        static::init_sql($id_tree);
        unset(static::$cache[$id]);
    }

}
