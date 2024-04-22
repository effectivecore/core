<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\storage;

use effcore\Control;
use effcore\Core;
use effcore\Entity;
use effcore\Field_Checkbox;
use effcore\Field_Hidden;
use effcore\Instance;
use effcore\Markup;
use effcore\Message;
use effcore\Page;
use effcore\Security;
use effcore\Text_multiline;
use effcore\Text;
use effcore\URL;
use ReflectionClass;

abstract class Events_Form_Instance_update {

    static function on_build($event, $form) {
        if (!$form->managing_group_id) $form->managing_group_id = Page::get_current()->args_get('managing_group_id');
        if (!$form->entity_name      ) $form->entity_name       = Page::get_current()->args_get('entity_name');
        if (!$form->instance_id      ) $form->instance_id       = Page::get_current()->args_get('instance_id');
        $entity = Entity::get($form->entity_name);
        $groups = Entity::get_managing_group_ids();
        if ($form->managing_group_id === null || isset($groups[$form->managing_group_id])) {
            if ($entity) {
                if ($entity->managing_is_enabled) {
                    if ($form->instance_id !== null) {
                        $id_keys   = $entity->id_get();
                        $id_values = explode('+', $form->instance_id);
                        if (count($id_keys) ===
                            count($id_values)) {
                            $conditions = array_combine($id_keys, $id_values);
                            $form->_instance = new Instance($form->entity_name, $conditions);
                            if ($form->_instance->select()) {
                                $form->attribute_insert('data-entity_name', $form->entity_name);
                                $form->attribute_insert('data-instance_id', $form->instance_id);
                                $form->child_select('fields')->children_delete();
                                foreach ($entity->fields as $c_name => $c_field) {
                                    if (!empty($c_field->managing->is_enabled_on_update) &&
                                        !empty($c_field->managing->control->class)) {
                                        $c_control = new $c_field->managing->control->class;
                                        $c_control->cform = $form;
                                        $c_control->title = $c_field->title;
                                        $c_control->element_attributes['name'] = $c_name;
                                        $c_control->element_attributes = ($c_field->managing->control->element_attributes           ?? []) + $c_control->element_attributes;
                                        $c_control->element_attributes = ($c_field->managing->control->element_attributes_on_update ?? []) + $c_control->element_attributes;
                                        if (isset($c_field->managing->control->properties          ) && is_array($c_field->managing->control->properties          )) foreach ($c_field->managing->control->properties           as $c_prop_name => $c_prop_value) $c_control->{$c_prop_name} = $c_prop_value;
                                        if (isset($c_field->managing->control->properties_on_update) && is_array($c_field->managing->control->properties_on_update)) foreach ($c_field->managing->control->properties_on_update as $c_prop_name => $c_prop_value) $c_control->{$c_prop_name} = $c_prop_value;
                                        $c_control->entity_name = $entity->name;
                                        $c_control->entity_field_name = $c_name;
                                        $form->child_select('fields')->child_insert($c_control, $c_name);
                                    }
                                }
                                # form messages
                                if ($form->child_select('fields')->children_select_count() === 0) {
                                    $form->child_select('fields')->child_insert(new Markup('x-no-items', [], 'No fields.'), 'message_no_fields');
                                    $form->has_no_fields = true;
                                }
                                # prevent parallel update (organizational methods, not secure)
                                if ($form->has_no_fields === false && $entity->has_parallel_checking && $entity->field_get('updated')) {
                                    $form->child_insert(new Field_Hidden('old_updated'), 'hidden_old_updated');
                                }
                            } else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('wrong instance key'                         )), 'message_error'); $form->has_error_on_build = true;}
                        }     else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('wrong number of instance keys'              )), 'message_error'); $form->has_error_on_build = true;}
                    }         else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('wrong instance key (not present)'           )), 'message_error'); $form->has_error_on_build = true;}
                }             else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('management for this entity is not available')), 'message_error'); $form->has_error_on_build = true;}
            }                 else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('wrong entity name'                          )), 'message_error'); $form->has_error_on_build = true;}
        }                     else {$form->child_select('fields')->child_insert(new Markup('p', [], new Text('wrong management group'                     )), 'message_error'); $form->has_error_on_build = true;}
    }

    static function on_init($event, $form, $items) {
        if ($form->has_error_on_build === false &&
            $form->has_no_fields      === false) {
            $entity = Entity::get($form->entity_name);
            if (isset($items['~update'])) $items['~update']->disabled_set(false);
            if (isset($items['~cancel'])) $items['~cancel']->disabled_set(false);
            foreach ($entity->fields as $c_name => $c_field) {
                if (!empty($c_field->managing->is_enabled_on_update) &&
                    !empty($c_field->managing->control->class)) {
                    $c_reflection = new ReflectionClass($c_field->managing->control->class);
                    $c_prefix = $c_reflection->implementsInterface('\\effcore\\Controls_Group') ? '*' : '#';
                    $c_control = $items[$c_prefix.$c_name];
                    $c_control->value_set_initial($form->_instance->{$c_name}, true);
                    if     (empty($c_field->managing->control->value_manual_set) && is_object($c_control) && $c_control instanceof Control && $c_control instanceof Field_Checkbox !== true) $c_control->  value_set($form->_instance->{$c_name}, ['once' => true]);
                    elseif (empty($c_field->managing->control->value_manual_set) && is_object($c_control) && $c_control instanceof Control && $c_control instanceof Field_Checkbox === true) $c_control->checked_set($form->_instance->{$c_name});
                }
            }
            # prevent parallel update
            if ($form->has_no_fields === false && $entity->has_parallel_checking && $entity->field_get('updated')) {
                $items['!old_updated']->value_set( # form value ?: storage value
                    Security::sanitize_datetime($items['!old_updated']->value_request_get()) ?: $form->_instance->updated
                );
            }
        }
    }

    static function on_validate($event, $form, $items) {
        $entity = Entity::get($form->entity_name);
        switch ($form->clicked_button->value_get()) {
            case 'update':
                # prevent parallel update
                if ($entity->has_parallel_checking && $entity->field_get('updated')) {
                    $new_updated = Core::deep_clone($form->_instance)->select()->updated; # storage value
                    $old_updated = $items['!old_updated']->value_get();                   # form    value
                    if ($new_updated !== $old_updated) {
                        $form->error_set(new Text_multiline([
                            'While editing this form, someone made changes in parallel and saved them!',
                            'Reload this page and make changes again to prevent inconsistency.']));
                        return;
                    }
                }
                break;
        }
    }

    static function on_submit($event, $form, $items) {
        $entity = Entity::get($form->entity_name);
        switch ($form->clicked_button->value_get()) {
            case 'update':
                # transfer new values to instance
                foreach ($entity->fields as $c_name => $c_field) {
                    if (!empty($c_field->managing->is_enabled_on_update) &&
                        !empty($c_field->managing->control->class)) {
                        $c_value = null;
                        $c_reflection = new ReflectionClass($c_field->managing->control->class);
                        $c_prefix = $c_reflection->implementsInterface('\\effcore\\Controls_Group') ? '*' : '#';
                        $c_control = $items[$c_prefix.$c_name];
                        if     (is_object($c_control) && $c_control instanceof Control && $c_control instanceof Field_Checkbox !== true) $c_value = $c_control->  value_get();
                        elseif (is_object($c_control) && $c_control instanceof Control && $c_control instanceof Field_Checkbox === true) $c_value = $c_control->checked_get() ? 1 : 0;
                        if (!empty($c_field->managing->control->value_manual_get_if_empty) && empty($c_value)) continue;
                        if (!empty($c_field->managing->control->value_manual_get         )                   ) continue;
                        $form->_instance->{$c_name} = $c_value;
                    }
                }
                # update action
                $form->_result = $form->_instance->update();
                # show messages
                if ($form->is_show_result_message && $form->_result !== null) Message::insert(new Text('Item of type "%%_type" with ID = "%%_id" was changed.'    , ['type' => (new Text($entity->title))->render(), 'id' => implode('+', $form->_instance->values_id_get()) ])           );
                if ($form->is_show_result_message && $form->_result === null) Message::insert(new Text('Item of type "%%_type" with ID = "%%_id" was not changed!', ['type' => (new Text($entity->title))->render(), 'id' => implode('+', $form->_instance->values_id_get()) ]), 'warning');
                # update 'updated' value
                if ($form->_result && $entity->has_parallel_checking && $entity->field_get('updated')) {
                    $items['!old_updated']->value_set(
                        $form->_instance->updated
                    );
                }
                # redirect if no error
                if ($form->_result !== null) {
                    if ($form->is_redirect_enabled) {
                        URL::go(URL::back_url_get() ?: $entity->make_url_for_select_multiple());
                    }
                }
                break;
            case 'cancel':
                if ($form->is_redirect_enabled) {
                    URL::go(URL::back_url_get() ?: $entity->make_url_for_select_multiple());
                }
                break;
        }
    }

}
