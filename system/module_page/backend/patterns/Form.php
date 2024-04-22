<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use DateTime;
use stdClass;

#[\AllowDynamicProperties]

class Form extends Markup implements has_Data_cache {

    public $tag_name = 'form';
    public $template = 'form';

    public $attributes = [
        'accept-charset' => 'UTF-8'
    ];

    public $title;
    public $title_tag_name = 'h2';
    public $title_is_visible = 1;
    public $title_attributes = [
        'data-form-title' => true
    ];

    public $clicked_button;
    public $number;

    public $validation_id;
    public $validation_cache;
    public $validation_cache_hash;
    public $validation_cache_is_persistent = false;

    public $has_no_fields = false;
    public $has_no_items = false;
    public $has_error_on_build = false;

    public $env = [];
    protected $items = [];

    function build() {
        $id = $this->id_get();
        if (!$id) {
            Message::insert('Form ID is required!', 'warning');
            $this->children_delete();
            return;
        }
        if (!$this->is_builded) {

            # variables for validation
            $this->number        = static::current_number_generate();
            $this->validation_id = static::validation_id_get($this);

            # hidden fields
            $this->child_insert(new Field_Hidden('form_id'      , $id                 ), 'hidden_id_form'      );
            $this->child_insert(new Field_Hidden('validation_id', $this->validation_id), 'hidden_id_validation');

            # send test headers 'x-form-validation-id--form_id: validation_id'
            if (Module::is_enabled('test')) {
                header('x-form-validation-id--'.$id.': '.$this->validation_id);
            }

            $this->components_build();
            $this->components_init();

            # if user submit this form (note: dynamic buttons should be inserted before)
            if ($this->is_submitted()) {
                $this->clicked_button = $this->clicked_button_get();
                if ($this->clicked_button) {

                    # call "on_request_value_set" method
                    if (empty($this->clicked_button->break_on_request_value_set)) {

                        # field "on_request_value_set"
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
                            if (is_object($c_child) && method_exists($c_child, 'on_request_value_set')) {
                                $c_result = Event::start_local('on_request_value_set', $c_child, ['form' => $this, 'npath' => $c_npath]);
                                Console::log_insert('form',    'on_request_value_set', $c_npath);
                            }
                        }

                        # field "on_request_value_set_after"
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
                            if (is_object($c_child) && method_exists($c_child, 'on_request_value_set_after')) {
                                $c_result = Event::start_local('on_request_value_set_after', $c_child, ['form' => $this, 'npath' => $c_npath]);
                                Console::log_insert('form',    'on_request_value_set_after', $c_npath);
                            }
                        }

                    }

                    # call "on_validate" handlers (parent should be at the end)
                    if (empty($this->clicked_button->break_on_validate)) {

                        # field "on_validate"
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
                            if (is_object($c_child) && method_exists($c_child, 'on_validate')) {
                                $c_result = Event::start_local('on_validate', $c_child, ['form' => $this, 'npath' => $c_npath]);
                                Console::log_insert('form',    'on_validate', $c_npath, $c_result ? 'ok' : 'warning');
                            }
                        }

                        # field "on_validate_after" (for example, to avoid making requests if the "Nickname" field has an incorrect value)
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
                            if (is_object($c_child) && method_exists($c_child, 'on_validate_after')) {
                                $c_result = Event::start_local('on_validate_after', $c_child, ['form' => $this, 'npath' => $c_npath]);
                                Console::log_insert('form',    'on_validate_after', $c_npath, $c_result ? 'ok' : 'warning');
                            }
                        }

                        # form "on_form_validate"
                        Event::start('on_form_validate', $id, [
                            'form' => &$this, 'items' => &$this->items]
                        );

                        # field "on_validate_final" (for example, for files)
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
                            if (is_object($c_child) && method_exists($c_child, 'on_validate_final')) {
                                $c_result = Event::start_local('on_validate_final', $c_child, ['form' => $this, 'npath' => $c_npath]);
                                Console::log_insert('form',    'on_validate_final', $c_npath, $c_result ? 'ok' : 'warning');
                            }
                        }
                    }

                    # send test headers 'x-form-submit-errors-count: N' (before a possible redirect)
                    if (Module::is_enabled('test')) {
                        header('x-form-submit-errors-count: '.count(static::$errors));
                    }

                    # show errors before submit (before a possible redirect after submit)
                    $this->errors_show();

                    # call "on_submit" handlers (if no errors)
                    if (!$this->has_error()) {
                        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child)
                            if (is_object($c_child) && method_exists($c_child, 'on_submit')) {
                                Event::start_local('on_submit', $c_child, ['form' => $this, 'npath' => $c_npath]); Console::log_insert('form', 'submission', $c_npath); }
                        Event::start('on_form_submit', $id, ['form' => &$this, 'items' => &$this->items]);
                        # show errors after call "on_submit" handlers for buttons with 'break_on_validate' (will not be shown if a redirect has occurred)
                        $this->errors_show();
                    }

                    # update or delete validation cache (will not be deleted if redirection has occurred)
                    if ($this->validation_cache !== null && $this->validation_cache_is_persistent !== false &&                                Security::hash_get($this->validation_cache) !== $this->validation_cache_hash) $this->validation_cache_storage_update();
                    if ($this->validation_cache !== null && $this->validation_cache_is_persistent === false && $this->has_error() === true && Security::hash_get($this->validation_cache) !== $this->validation_cache_hash) $this->validation_cache_storage_update();
                    if ($this->validation_cache !== null && $this->validation_cache_is_persistent === false && $this->has_error() !== true                                                                                ) $this->validation_cache_storage_delete();

                }
            }

            $this->is_builded = true;
        }
    }

    function components_build() {
        # call "build" handlers
        Event::start('on_form_build', $this->id_get(), ['form' => &$this]);
        # resolve form plugins
        foreach ($this->children_select_recursive() as $c_npath => $c_child) {
            if ($c_child instanceof Form_plugin) {
                $c_npath_parts     = explode('/', $c_npath);
                $c_npath_last_part = end($c_npath_parts);
                $c_pointers        = Core::npath_get_pointers($this, $c_npath);
                if ($c_child->is_available()) $c_pointers[$c_npath_last_part] = $c_child->object_get();
                else                    unset($c_pointers[$c_npath_last_part]);
            }
        }
        # set cform → build → set cform (note: for new items after build)
        foreach ($this->children_select_recursive() as $c_child) if (          $c_child instanceof Control                  ) $c_child->cform = $this;
        foreach ($this->children_select_recursive() as $c_child) if (is_object($c_child) && method_exists($c_child, 'build')) $c_child->build();
        foreach ($this->children_select_recursive() as $c_child) if (          $c_child instanceof Control                  ) $c_child->cform = $this;
    }

    function components_init() {
        $this->items_update();
        Event::start('on_form_init', $this->id_get(), ['form' => &$this, 'items' => &$this->items]);
        $this->items_update();
    }

    function render() {
        $this->build();
        return parent::render();
    }

    function render_self() {
        if ($this->title && (bool)$this->title_is_visible !== true) return (new Markup($this->title_tag_name, $this->title_attributes + ['aria-hidden' => 'true'], $this->title))->render();
        if ($this->title && (bool)$this->title_is_visible === true) return (new Markup($this->title_tag_name, $this->title_attributes + [                       ], $this->title))->render();
    }

    # ─────────────────────────────────────────────────────────────────────
    # shared functionality
    # ─────────────────────────────────────────────────────────────────────

    function is_submitted() {
        # check if 'form_id' is match
        if ($this->child_select('hidden_id_form')->value_request_get(0, $this->source_get()) ===
            $this->child_select('hidden_id_form')->value_get()) {
            # check if form 'number' + 'created' + 'ip' + 'uagent' + 'random' is match
            if (static::validation_id_check(static::validation_id_get_raw($this), $this)) {
                return true;
            }
        }
    }

    function id_get() {
        return $this->attribute_select('id');
    }

    function source_get() {
        return $this->attribute_select('method') === 'get' ? '_GET' : '_POST';
    }

    function clicked_button_get() {
        foreach ($this->children_select_recursive() as $c_child) {
            if ($c_child instanceof Button &&
                $c_child->is_clicked(0, $this->source_get())) {
                return $c_child;
            }
        }
    }

    function items_update() {
        $this->items = [];
        $groups      = [];
        foreach ($this->children_select_recursive(null, '', true) as $c_npath => $c_child) {
            if ($c_child instanceof Container                                   ) $this->items[    $c_npath                                              ] = $c_child;
            if ($c_child instanceof Button                                      ) $this->items['~'.$c_child->value_get     ()                            ] = $c_child;
            if ($c_child instanceof Field_Hidden                                ) $this->items['!'.$c_child->name_get      ()                            ] = $c_child;
            if ($c_child instanceof Field                                       ) $groups     ['#'.$c_child->name_get      ()                          ][] = $c_child;
            if ($c_child instanceof Field_Radiobutton                           ) $groups     ['#'.$c_child->name_get      ().':'.$c_child->value_get()][] = $c_child;
            if ($c_child instanceof Controls_Group && $c_child->group_name_get()) $groups     ['*'.$c_child->group_name_get()                          ][] = $c_child;
        }
        foreach ($groups as $c_name => $c_group) {
            if (count($c_group) === 1) $this->items[$c_name] = reset($c_group);
            if (count($c_group)  >  1) $this->items[$c_name] =       $c_group;
        }
        return $this->items;
    }

    # ─────────────────────────────────────────────────────────────────────
    # functionality for errors
    # ─────────────────────────────────────────────────────────────────────

    function has_error() {
        return (bool)count(static::$errors);
    }

    function error_set($message = null, $args = []) {
        $new_error = new stdClass;
        $new_error->message = $message;
        $new_error->args    = $args;
        $new_error->pointer = &$this;
        static::$errors[] = $new_error;
    }

    function errors_show() {
        if ($this->has_error()) {
            $this->attribute_insert('aria-invalid', 'true');
            foreach (static::$errors as $c_error) {
                switch (gettype($c_error->message)) {
                    case 'string': Message::insert(new Text($c_error->message, $c_error->args), 'error'); break;
                    case 'object': Message::insert(         $c_error->message,                  'error'); break;
                }
            }
        }
    }

    # ─────────────────────────────────────────────────────────────────────
    # functionality for validation cache
    # ─────────────────────────────────────────────────────────────────────

    function validation_cache_date_get($format = 'Y-m-d') {
        $timestmp = static::validation_id_extract_created($this->validation_id);
        return DateTime::createFromFormat('U', $timestmp)->format($format);
    }

    function validation_cache_init() {
        if ($this->validation_cache === null) {
            $instance = (new Instance('cache_validation', ['id' => $this->validation_id]))->select();
            $this->validation_cache = $instance ? $instance->data : [];
            $this->validation_cache_hash = Security::hash_get($this->validation_cache);
        }
    }

    function validation_cache_get($id) {
        $this->validation_cache_init();
        return $this->validation_cache[$id] ?? null;
    }

    function validation_cache_set($id, $data) {
        $this->validation_cache_init();
        $this->validation_cache[$id] = $data;
    }

    function validation_cache_storage_update() {
        $instance = new Instance('cache_validation', ['id' => $this->validation_id]);
        if ($instance->select()) {$instance->data = $this->validation_cache; return $instance->update();}
        else                     {$instance->data = $this->validation_cache; return $instance->insert();}
    }

    function validation_cache_storage_delete() {
        return (new Instance('cache_validation', [
            'id' => $this->validation_id
        ]))->delete();
    }

    static function validation_cleaning($files_limit = 5000) {
        # delete items from the storage
        Entity::get('cache_validation')->instances_delete([
            'where' => [
                'updated_!f'       => 'updated',
                'updated_operator' => '<',
                'updated_!v'       => time() - Core::DATE_PERIOD_D
        ]]);
        # delete temporary files
        Directory::items_delete_by_date(
            Temporary::DIRECTORY.'validation/', $files_limit, Core::date_get()
        );
    }

    ###########################
    ### static declarations ###
    ###########################

    public static $errors = [];

    static function not_external_properties_get() {
        return [];
    }

    protected static $c_form_number = 0;

    static function current_number_generate() {
        return static::$c_form_number++;
    }

    static function is_posted() {
        return Request::value_get('form_id', 0, '_POST', false) !== false;
    }

    # ─────────────────────────────────────────────────────────────────────
    # functionality for validation_id
    # ─────────────────────────────────────────────────────────────────────

    static function validation_id_generate($form) {
        $hex_number        = static::validation_id_get_hex_number       ($form->number);
        $hex_created       = static::validation_id_get_hex_created      (             );
        $hex_ip            = static::validation_id_get_hex_ip           (             );
        $hex_uagent_hash_8 = static::validation_id_get_hex_uagent_hash_8(             );
        $hex_random        = static::validation_id_get_hex_random       (             );
        $validation_id = $hex_number.        # strlen === 2
                         $hex_created.       # strlen === 8
                         $hex_ip.            # strlen === 32
                         $hex_uagent_hash_8. # strlen === 8
                         $hex_random;        # strlen === 8
        $validation_id.= User::signature_get($validation_id, 'form', 8);
        return $validation_id;
    }

    static function validation_id_get($form) {
        if (static::validation_id_check(static::validation_id_get_raw ($form), $form))
             return                     static::validation_id_get_raw ($form);
        else return                     static::validation_id_generate($form);
    }

    static function validation_id_get_raw($form) {
        $source = $form->source_get();
        global ${$source};
        return ${$source}['validation_id'] ?? '';
    }

    static function validation_id_get_hex_number($number) {return str_pad(dechex($number), 2, '0', STR_PAD_LEFT);}
    static function validation_id_get_hex_created      () {return dechex(time());}
    static function validation_id_get_hex_ip           () {return Core::ip_to_hex(Request::addr_remote_get());}
    static function validation_id_get_hex_uagent_hash_8() {return Security::hash_get_mini(Request::user_agent_get());}
    static function validation_id_get_hex_random       () {return str_pad(dechex(random_int(0, PHP_INT_32_MAX)), 8, '0', STR_PAD_LEFT);}
    static function validation_id_get_hex_signature ($id) {return User::signature_get(substr($id, 0, 58), 'form', 8);}

    static function validation_id_extract_number           ($id) {return hexdec(static::validation_id_extract_hex_number ($id));}
    static function validation_id_extract_created          ($id) {return hexdec(static::validation_id_extract_hex_created($id));}
    static function validation_id_extract_hex_number       ($id) {return substr($id,  0,  2);}
    static function validation_id_extract_hex_created      ($id) {return substr($id,  2,  8);}
    static function validation_id_extract_hex_ip           ($id) {return substr($id, 10, 32);}
    static function validation_id_extract_hex_uagent_hash_8($id) {return substr($id, 42,  8);}
    static function validation_id_extract_hex_random       ($id) {return substr($id, 50,  8);}
    static function validation_id_extract_hex_signature    ($id) {return substr($id, 58,  8);}

    static function validation_id_check($id, $form) {
        if (Security::validate_hash($id, 66)) {
            $number            = static::validation_id_extract_number           ($id);
            $created           = static::validation_id_extract_created          ($id);
            $hex_ip            = static::validation_id_extract_hex_ip           ($id);
            $hex_uagent_hash_8 = static::validation_id_extract_hex_uagent_hash_8($id);
            $hex_signature     = static::validation_id_extract_hex_signature    ($id);
            if ($created <= time()                                                   &&
                $created >= time() - Core::DATE_PERIOD_D                             &&
                $form->number      === $number                                       &&
                $hex_ip            === static::validation_id_get_hex_ip           () &&
                $hex_uagent_hash_8 === static::validation_id_get_hex_uagent_hash_8() &&
                $hex_signature     === static::validation_id_get_hex_signature($id)) {
                return true;
            }
        }
    }

}
