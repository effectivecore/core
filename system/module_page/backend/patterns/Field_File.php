<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_File extends Field {

    # FORM SUBMIT #1                                                    ◦ FORM SUBMIT #2
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    # on_value_init                                                     ◦
    #                                     ╔════════ form ════════╗      ◦      ╔═ pool fin ═╗       ╔════════ form ════════╗
    #                                     ║ ┌── upload field ──┐ ║      ◦  ┌──▶║ old file 1 ║       ║ ┌── upload field ──┐ ║
    #                                     ║ │------------------│ ║      ◦  │   ╚════════════╝       ║ │------------------│ ║
    #                                 ┌─────│   + new file 1   │ ║      ◦  │          │        ┌──────│   + new file 2   │ ║
    #                                 │   ║ └──────────────────┘ ║      ◦  │          │        │    ║ └──────────────────┘ ║
    #                                 │   ╚══════════════════════╝      ◦  │          └────────│─────▶ ▣ delete old file 1 ────┐
    #                                 │                                 ◦  │                   │    ╚══════════════════════╝   │
    #                                 │                                 ◦  │          ┌────────┘                               │
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    # on_button_click_insert          │                                 ◦  │          │                                        │
    # on_button_click_delete          ▼                                 ◦  │          ▼                                        ▼
    #                          ╔═ pool pre ═╗                           ◦  │   ╔═ pool pre ═╗                      ╔═ pool fin_to_delete ═╗
    #                          ║ new file 1 ║                        ┌─────┘   ║ new file 2 ║                      ║       old file 1     ║
    #                          ╚════════════╝                        │  ◦      ╚════════════╝                      ╚══════════════════════╝
    #                                 │                              │  ◦             │                                        │
    #                                 │   ╔════════ form ════════╗   │  ◦             │             ╔════════ form ════════╗   │
    #                                 │   ║ ┌── upload field ──┐ ║   │  ◦             │             ║ ┌── upload field ──┐ ║   │
    #                                 │   ║ │------------------│ ║   │  ◦             │             ║ │------------------│ ║   │
    #                                 │   ║ └──────────────────┘ ║   │  ◦             │             ║ └──────────────────┘ ║   │    ┌ ─ ─ ─ ─ ─ ─ ─ ─
    #                                 ├────◇ □ delete new file 1 ║   │  ◦             └──────────────▶ ▣ delete new file 2 ────│───▶  delete process │
    #                                 │   ╚══════════════════════╝   │  ◦                           ╚══════════════════════╝   │    └ ─ ─ ─ ─ ─ ─ ─ ─
    #                                 │                              │  ◦                                                      │
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦│◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    # on_validate_final               │                              │  ◦                                                      │
    #                                 ▼                              │  ◦                                                      ▼
    #                          ╔══ storage ══╗                       │  ◦                                             ┌ ─ ─ ─ ─ ─ ─ ─ ─
    #                          ║    file 1   ║───────────────────────┘  ◦                                               delete process │
    #                          ╚═════════════╝                          ◦                                             └ ─ ─ ─ ─ ─ ─ ─ ─

    public $title = 'File';
    public $item_title = 'File';
    public $attributes = [
        'data-type' => 'file'];
    public $element_attributes = [
        'type' => 'file',
        'name' => 'file'];
    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    public $upload_dir = '';
    public $fixed_name;
    public $fixed_type;
    public $max_file_size = '5K';
    public $min_files_number = 0;
    public $max_files_number = 1;
    public $max_length_name = 227; # = 255 - strlen('-TimestmpRandom_v.') - max_length_type
    public $max_length_type = 10;
    public $characters_allowed = 'a-zA-Z0-9_\\-\\.';
    public $characters_allowed_for_decsription = '"a-z", "A-Z", "0-9", "_", "-", "."';
    public $types_allowed = [
        'txt' => 'txt'];
    public $has_controls_insert = true;
    public $has_controls_manage = true;
    public $result;
    public $is_debug_mode = false;

    function build($rebuild = false) {
        if (!$this->is_builded || $rebuild) {
            parent::build();
            if ($this->is_debug_mode)
                $this->attribute_insert('data-debug', true);
            if ($this->has_controls_insert)
                $this->child_insert(static::controls_markup__insert($this), 'insert');
            if ($this->has_controls_manage)
                static::controls_markup__manage($this);
            $this->accept_set($this->render_attribut_accept());
        }
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    function value_get($options = []) { # @return: null | string | array | serialize(array)
        $this->result = [];
        foreach ($this->items_get('fin') as $c_item) {
            $this->result[] = $c_item->get_current_path(true);
        }
        if ($this->multiple_get() !== true) return is_array($this->result) && count($this->result) ? reset($this->result) : null;
        if ($this->multiple_get() === true) {
            if (!empty($options['return_serialized']))
                 return serialize($this->result);
            else return           $this->result;
        }
    }

    function value_set($value) {
        $this->value_set_initial($value);
        if (is_null  ($value)) $value = [];
        if (is_string($value)) $value = [$value];
        if (is_array ($value)) {
            $fin_to_delete = $this->items_get('fin_to_delete');
            foreach ($value as $c_id => $c_path_relative)
                if (!isset($fin_to_delete[$c_id])) {
                    $c_item = new File_history;
                    if ($c_item->init_from_fin($c_path_relative)) {
                        $fin_items[$c_id] = $c_item;
                        $this->items_set('fin', $fin_items); }}
            $this->build(true);
        }
    }

    function items_get($scope)  {
        return $this->cform->validation_cache_get($this->name_get().'__items__'.$scope) ?: [];
    }

    function items_set($scope, $items) {
        $this->cform->validation_cache_is_persistent = true;
        $this->cform->validation_cache_set($this->name_get().'__items__'.$scope, $items);
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    function file_size_max_get() {
        $system = Core::is_abbreviated_bytes($this->max_file_size) ?
                  Core::abbreviated_to_bytes($this->max_file_size) : (int)$this->max_file_size;
        $php__1 = Core::php_upload_max_filesize_bytes_get();
        $php__2 = Core::      php_post_max_size_bytes_get();
        return min($system, $php__1, $php__2);
    }

    function file_size_max_has_php_restriction() {
        $system = Core::is_abbreviated_bytes($this->max_file_size) ?
                  Core::abbreviated_to_bytes($this->max_file_size) : (int)$this->max_file_size;
        $php__1 = Core::php_upload_max_filesize_bytes_get();
        $php__2 = Core::      php_post_max_size_bytes_get();
        return $system > $php__1 ||
               $system > $php__2;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    function render_attribut_accept() {
        $accept_types = [];
        foreach ($this->types_allowed as $c_type) $accept_types[] = '.'.$c_type;
        return implode(',', $accept_types);
    }

    function render_description() {
        $this->description = static::description_prepare($this->description);
        if ($this->min_files_number !== null && $this->min_files_number !== $this->max_files_number) $this->description['file-number-min'        ] = $this->render_description_file_min_number();
        if ($this->max_files_number !== null && $this->min_files_number !== $this->max_files_number) $this->description['file-number-max'        ] = $this->render_description_file_max_number();
        if ($this->min_files_number !== null && $this->min_files_number === $this->max_files_number) $this->description['file-number-mid'        ] = $this->render_description_file_mid_number();
                                                                                                     $this->description['file-size-max'          ] = $this->render_description_file_size_max();
        if ($this->types_allowed                                                                   ) $this->description['file-allowed-types'     ] = $this->render_description_file_types_allowed();
        if ($this->characters_allowed_for_decsription                                              ) $this->description['file-allowed-characters'] = $this->render_description_file_characters_allowed_for_decsription();
        return parent::render_description();
    }

    function render_description_file_size_max                          () {return new Markup('p', ['data-id' => 'file-size-max'          ], new Text($this->file_size_max_has_php_restriction() ? 'File can have a maximum size: %%_value (PHP restriction)' : 'File can have a maximum size: %%_value', ['value' => Locale::format_bytes($this->file_size_max_get())]));}
    function render_description_file_min_number                        () {return new Markup('p', ['data-id' => 'file-number-min'        ], new Text('Field can contain a minimum of %%_number file%%_plural(number|s).', ['number'     => $this->min_files_number                  ]));}
    function render_description_file_max_number                        () {return new Markup('p', ['data-id' => 'file-number-max'        ], new Text('Field can contain a maximum of %%_number file%%_plural(number|s).', ['number'     => $this->max_files_number                  ]));}
    function render_description_file_mid_number                        () {return new Markup('p', ['data-id' => 'file-number-mid'        ], new Text('Field can contain only %%_number file%%_plural(number|s).'        , ['number'     => $this->min_files_number                  ]));}
    function render_description_file_types_allowed                     () {return new Markup('p', ['data-id' => 'file-allowed-types'     ], new Text('File can only be of the next types: %%_types'                     , ['types'      => $this->render_attribut_accept()          ]));}
    function render_description_file_characters_allowed_for_decsription() {return new Markup('p', ['data-id' => 'file-allowed-characters'], new Text('File name can contain only the next characters: %%_characters'    , ['characters' => $this->characters_allowed_for_decsription]));}

    ###########################
    ### static declarations ###
    ###########################

    static function controls_markup__insert($field) {
        $button = new Button(null, ['data-style' => 'insert', 'title' => new Text('insert')]);
        $button->break_on_validate = true;
        $button->build();
        $button->disabled_set($field->disabled_get());
        $button->value_set($field->name_get().'__insert');
        $button->_type = 'insert';
        $field->controls['~insert'] = $button;
        return $button;
    }

    static function controls_markup__manage($field) {
        $field->child_delete('manager_fin');
        $field->child_delete('manager_pre');
        $field->controls['*manager_fin'] = new Markup('x-controls', ['data-type' => 'delete+fin']);
        $field->controls['*manager_pre'] = new Markup('x-controls', ['data-type' => 'delete+pre']);
        $field->child_insert($field->controls['*manager_fin'], 'manager_fin');
        $field->child_insert($field->controls['*manager_pre'], 'manager_pre');
        $items_fin = $field->items_get('fin');
        $items_pre = $field->items_get('pre');
        foreach ($items_fin as $c_id => $c_item) static::controls_markup__manage__item($field, $c_item, $c_id, 'fin');
        foreach ($items_pre as $c_id => $c_item) static::controls_markup__manage__item($field, $c_item, $c_id, 'pre');
        # insert reaction
        if ($field->disabled_get() === false) {
            $is_over = count($items_fin) + count($items_pre) >= $field->max_files_number;
            if ($field->is_debug_mode !== true                     ) $field->controls['~insert']->disabled_set($is_over);
            if ($field->is_debug_mode === true && $is_over === true) $field->controls['~insert']->attribute_insert('data-is-over', true);
            if ($field->is_debug_mode === true && $is_over !== true) $field->controls['~insert']->attribute_delete('data-is-over');
        }
    }

    static function controls_markup__manage__item($field, $item, $id, $scope) {
        $button_delete = new Button(null, ['data-style' => 'delete little', 'title' => new Text('delete')], +500);
        $button_delete->break_on_validate = true;
        $button_delete->build();
        $button_delete->disabled_set($field->disabled_get());
        $button_delete->value_set($field->name_get().'__delete__'.$scope.'__'.$id);
        $button_delete->_type = 'delete';
        $button_delete->_scope = $scope;
        $button_delete->_id = $id;
        $field->controls['~delete_'.$scope.'_'.$id] = $button_delete;
        if ($scope === 'fin') $field->controls['*manager_fin']->child_insert(new Markup('x-item', ['data-id' => $id], [$button_delete, static::controls_markup__manage__item_title($field, $item, $id, $scope)]), $id);
        if ($scope === 'pre') $field->controls['*manager_pre']->child_insert(new Markup('x-item', ['data-id' => $id], [$button_delete, static::controls_markup__manage__item_title($field, $item, $id, $scope)]), $id);
    }

    static function controls_markup__manage__item_title($field, $item, $id, $scope) {
        return new Markup('x-title', [], new Text('file "%%_file"', ['file' => $item->file]));
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function sanitize($field, $form, $element, &$new_values) {
        foreach ($new_values as $c_value) {
            $c_value->sanitize_tmp(
                $field->characters_allowed,
                $field->max_length_name,
                $field->max_length_type
            );
        }
    }

    static function on_button_click_insert($field, $form, $npath, $button) {
        $values = static::on_validate_manual($field, $form, $npath);
        if (!$field->has_error() && static::validate_required($field, $form, $field->child_select('element'), $values)) {
            $has_error = false;
            $items_pre = $field->items_get('pre');
            foreach ($values as $c_new_item) {
                $items_pre[] = $c_new_item;
                $c_new_row_id = array_key_last($items_pre);
                if ($c_new_item->move_tmp_to_pre(Temporary::DIRECTORY.'validation/'.$field->cform->validation_cache_date_get().'/'.$field->cform->validation_id.'-'.$field->name_get().'-'.$c_new_row_id.'.'.$c_new_item->type)) {
                    $field->items_set('pre', $items_pre);
                    Message::insert(new Text(
                        'File of type "%%_type" with title "%%_title" was appended.', [
                        'type'  => (new Text($field->item_title))->render(),
                        'title' => $c_new_item->file
                    ]));
                } else {
                    $has_error = true;
                }
            }
            if ($has_error) {
                $field->error_set();
                return;
            }
        }
    }

    static function on_button_click_delete($field, $form, $npath, $button) {
        switch ($button->_scope) {
            case 'pre':
                $items_pre = $field->items_get('pre');
                if (isset($items_pre[$button->_id])) {
                    if ($items_pre[$button->_id]->delete_pre()) {
                        $item_title = $items_pre[$button->_id]->file;
                        unset($items_pre[$button->_id]);
                        $field->items_set('pre', $items_pre);
                        Message::insert(new Text(
                            'File of type "%%_type" with title "%%_title" was deleted physically.', [
                            'type'  => (new Text($field->item_title))->render(),
                            'title' => $item_title
                        ]));
                    } else {
                        $field->error_set();
                        return;
                    }
                }
                break;
            case 'fin':
                $items_fin = $field->items_get('fin');
                if (isset($items_fin[$button->_id])) {
                    $item_title = $items_fin[$button->_id]->file;
                    $fin_to_delete = $field->items_get('fin_to_delete');
                    $fin_to_delete[$button->_id] = $items_fin[$button->_id];
                    unset($items_fin[$button->_id]);
                    $field->items_set('fin_to_delete', $fin_to_delete);
                    $field->items_set('fin', $items_fin);
                    Message::insert(new Text(
                        'File of type "%%_type" with title "%%_title" was deleted.', [
                        'type'  => (new Text($field->item_title))->render(),
                        'title' => $item_title
                    ]));
                }
                break;
        }
    }

    static function on_validate_manual($field, $form, $npath) {
        $element = $field->child_select('element');
        $name = $field->name_get();
        $type = $field->type_get();
        if ($name && $type) {
            if ($field->disabled_get()) return [];
            $new_values = Request::files_get($name);
            static::sanitize($field, $form, $element, $new_values);
            $result = static::validate_multiple($field, $form, $element, $new_values) &&
                      static::validate_upload  ($field, $form, $element, $new_values);
            if ($result) return $new_values;
            else         return [];
        }
    }

    static function on_validate_final($field, $form, $npath) {
        if (!$form->has_error()) {
            $has_error = false;
            $fin_to_delete = $field->items_get('fin_to_delete');
            foreach ($fin_to_delete as $c_id => $c_item) {
                if ($c_item->delete_fin()) {
                    $c_title_for_message = $c_item->file;
                    unset($fin_to_delete[$c_id]);
                    $field->items_set('fin_to_delete', $fin_to_delete);
                    Message::insert(new Text(
                        'File of type "%%_type" with title "%%_title" was deleted physically.', [
                        'type'  => (new Text($field->item_title))->render(),
                        'title' => $c_title_for_message
                    ]));
                } else {
                    $has_error = true;
                }
            }
            # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
            if (!$has_error) {
                $items_pre = $field->items_get('pre');
                $items_fin = $field->items_get('fin');
                foreach ($items_pre as $c_id => $c_item) {
                    if ($c_item->move_pre_to_fin(Dynamic::DIR_FILES.$field->upload_dir.$c_item->file, $field->fixed_name, $field->fixed_type)) {
                        $items_fin[] = $c_item;
                        unset($items_pre[$c_id]);
                        $field->items_set('pre', $items_pre);
                        $field->items_set('fin', $items_fin);
                        Message::insert(new Text(
                            'File of type "%%_type" with title "%%_title" has been saved.', [
                            'type'  => (new Text($field->item_title))->render(),
                            'title' => $c_item->file
                        ]));
                    } else {
                        $has_error = true;
                    }
                }
            }
            # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
            $field->build(true);
            static::debug_info_show($field, 'on_validate_final');
            if ($has_error) {
                $field->error_set();
            }
            return !$has_error;
        }
    }

    static function on_submit($field, $form, $npath) {
        if (!empty($field->controls)) {
            foreach ($field->controls as $c_button) {
                if ($c_button instanceof Button && $c_button->is_clicked()) {
                    if (isset($c_button->_type) && $c_button->_type === 'insert') {static::debug_info_show($field, 'on_button_click_insert'); $result = Event::start_local('on_button_click_insert', $field, ['form' => $form, 'npath' => $npath, 'button' => $c_button]); $field->build(true); static::debug_info_show($field, 'on_button_click_insert'); return $result;}
                    if (isset($c_button->_type) && $c_button->_type === 'delete') {static::debug_info_show($field, 'on_button_click_delete'); $result = Event::start_local('on_button_click_delete', $field, ['form' => $form, 'npath' => $npath, 'button' => $c_button]); $field->build(true); static::debug_info_show($field, 'on_button_click_delete'); return $result;} }}}
        static::debug_info_show($field, 'on_submit');
    }

    static function validate_upload($field, $form, $element, &$new_values) {
        $items_fin = $field->items_get('fin');
        $items_pre = $field->items_get('pre');
        $count_all = count($items_fin) + count($items_pre) + count($new_values);
        $count_cur = count($items_fin) + count($items_pre);
        if ($field->min_files_number !== null && $field->min_files_number !== $field->max_files_number && $count_all < $field->min_files_number) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'You are trying to upload too few files!' , 'Field can contain a minimum of %%_number file%%_plural(number|s).', 'You have already uploaded %%_current_number file%%_plural(current_number|s).'], ['title' => (new Text($field->title))->render(), 'number' => $field->min_files_number, 'current_number' => $count_cur] )); return;}
        if ($field->max_files_number !== null && $field->min_files_number !== $field->max_files_number && $count_all > $field->max_files_number) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'You are trying to upload too much files!', 'Field can contain a maximum of %%_number file%%_plural(number|s).', 'You have already uploaded %%_current_number file%%_plural(current_number|s).'], ['title' => (new Text($field->title))->render(), 'number' => $field->max_files_number, 'current_number' => $count_cur] )); return;}
        if ($field->min_files_number !== null && $field->min_files_number === $field->max_files_number && $count_all < $field->min_files_number) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'You are trying to upload too few files!' , 'Field can contain only %%_number file%%_plural(number|s).'        , 'You have already uploaded %%_current_number file%%_plural(current_number|s).'], ['title' => (new Text($field->title))->render(), 'number' => $field->min_files_number, 'current_number' => $count_cur] )); return;}
        if ($field->max_files_number !== null && $field->min_files_number === $field->max_files_number && $count_all > $field->max_files_number) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'You are trying to upload too much files!', 'Field can contain only %%_number file%%_plural(number|s).'        , 'You have already uploaded %%_current_number file%%_plural(current_number|s).'], ['title' => (new Text($field->title))->render(), 'number' => $field->max_files_number, 'current_number' => $count_cur] )); return;}
        # validate each item
        $max_size = $field->file_size_max_get();
        foreach ($new_values as $c_new_value) {
            if (count($field->types_allowed) &&
               !isset($field->types_allowed[$c_new_value->type])) {
                $field->error_set(
                    'Field "%%_title" does not support uploading a file of this type!', ['title' => (new Text($field->title))->render() ]
                );
                return;
            }
            switch ($c_new_value->error) {
                case UPLOAD_ERR_INI_SIZE  : $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'Size of uploaded file greater than %%_size.'                                       ], ['title' => (new Text($field->title))->render(), 'size' => Locale::format_bytes($max_size) ])); return;
                case UPLOAD_ERR_FORM_SIZE : $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'Size of uploaded file greater than MAX_FILE_SIZE (MAX_FILE_SIZE is not supported).'], ['title' => (new Text($field->title))->render()                                            ])); return;
                case UPLOAD_ERR_PARTIAL   : $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'File was only partially uploaded.'                                                 ], ['title' => (new Text($field->title))->render()                                            ])); return;
                case UPLOAD_ERR_NO_TMP_DIR: $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'Directory for temporary upload is missing.'                                        ], ['title' => (new Text($field->title))->render()                                            ])); return;
                case UPLOAD_ERR_CANT_WRITE: $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'File was not written to disc!'                                                     ], ['title' => (new Text($field->title))->render()                                            ])); return;
                case UPLOAD_ERR_EXTENSION : $field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'File upload was stopped by PHP extension.'                                         ], ['title' => (new Text($field->title))->render()                                            ])); return;
            }
            if ($c_new_value->error !== UPLOAD_ERR_OK) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'Error: %%_error'                            ], ['title' => (new Text($field->title))->render(),      'error' => $c_new_value->error       ])); return;}
            if ($c_new_value->size  === 0            ) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'File is empty.'                             ], ['title' => (new Text($field->title))->render()                                            ])); return;}
            if ($c_new_value->size   >  $max_size    ) {$field->error_set(new Text_multiline(['Field "%%_title" contains an error!', 'Size of uploaded file greater than %%_size.'], ['title' => (new Text($field->title))->render(), 'size' => Locale::format_bytes($max_size) ])); return;}
        }
        return true;
    }

    static function validate_multiple($field, $form, $element, &$new_values) {
        if (!$field->multiple_get() && count($new_values) > 1) {
            $field->error_set(
                'Field "%%_title" does not support multiple select!', ['title' => (new Text($field->title))->render() ]
            );
        } else {
            return true;
        }
    }

    static function validate_required($field, $form, $element, &$new_value) {
        if (count($new_value) === 0) {
            $field->error_set(
                'Field "%%_title" cannot be blank!', ['title' => (new Text($field->title))->render() ]
            );
        } else {
            return true;
        }
    }

    static function debug_info_show($field, $source = '') {
        if ($field->is_debug_mode) {
            $space = '&nbsp;&nbsp;&nbsp;';
            if ($source) $result = 'DEBUG INFO POOL STATE for "'.$field->title.'" ('.$source.')'.BR;
            else         $result = 'DEBUG INFO POOL STATE for "'.$field->title.'"'              .BR;
            $result.= 'pool "fin_to_delete":'.BR; $items = $field->items_get('fin_to_delete'); if (count($items)) foreach ($items as $c_id => $c_item) $result.= $space.$c_id.': '.$c_item->name.BR; else $result.= $space.'- - -'; $result.= BR;
            $result.= 'pool "fin":'          .BR; $items = $field->items_get('fin')          ; if (count($items)) foreach ($items as $c_id => $c_item) $result.= $space.$c_id.': '.$c_item->name.BR; else $result.= $space.'- - -'; $result.= BR;
            $result.= 'pool "pre":'          .BR; $items = $field->items_get('pre')          ; if (count($items)) foreach ($items as $c_id => $c_item) $result.= $space.$c_id.': '.$c_item->name.BR; else $result.= $space.'- - -'; $result.= BR;
            Message::insert($result, 'notice');
        }
    }

}
