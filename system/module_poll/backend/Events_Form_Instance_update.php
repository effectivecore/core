<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\poll;

use effcore\Entity;
use effcore\Module;
use effcore\Poll;
use effcore\Text;
use effcore\URL;
use effcore\Widget_Texts;

abstract class Events_Form_Instance_update {

    static function on_build($event, $form) {
        if ($form->has_error_on_build === false &&
            $form->has_no_fields      === false) {
            $entity = Entity::get($form->entity_name);
            if ($entity->name === 'poll') {
                $form->_answers = Poll::answers_by_poll_id_select($form->_instance->id);
                $widget_answers = new Widget_Texts;
                $widget_answers->cform = $form;
                $widget_answers->group_name = 'widget_answers';
                $widget_answers->title = 'Answers';
                $widget_answers->item_title = 'Answer';
                $widget_answers->weight = +140;
                $form->child_select('fields')->child_insert($widget_answers, 'answers');
            }
        }
    }

    static function on_init($event, $form, $items) {
        if ($form->has_error_on_build === false &&
            $form->has_no_fields      === false) {
            $entity = Entity::get($form->entity_name);
            if ($entity->name === 'poll') {
                $form->is_redirect_enabled = false;
                $widget_items = [];
                foreach ($form->_answers as $c_answer) {
                    $widget_items[] = (object)[
                        'id'     => $c_answer->id,
                        'weight' => $c_answer->weight,
                        'text'   => $c_answer->answer]; }
                $items['*widget_answers']->value_set($widget_items, ['once' => true]);
            }
        }
    }

    static function on_validate($event, $form, $items) {
        $entity = Entity::get($form->entity_name);
        switch ($form->clicked_button->value_get()) {
            case 'update':
                if ($entity->name === 'poll') {
                    $settings = Module::settings_get('poll');
                    if (count($items['*widget_answers']->value_get()) < $settings->answers_min) $form->error_set('Group "%%_title" should contain a minimum %%_number item%%_plural(number|s)!', ['title' => (new Text($items['*widget_answers']->title))->render(), 'number' => $settings->answers_min]);
                    if (count($items['*widget_answers']->value_get()) > $settings->answers_max) $form->error_set('Group "%%_title" should contain a maximum %%_number item%%_plural(number|s)!', ['title' => (new Text($items['*widget_answers']->title))->render(), 'number' => $settings->answers_max]);
                }
                break;
        }
    }

    static function on_submit($event, $form, $items) {
        $entity = Entity::get($form->entity_name);
        switch ($form->clicked_button->value_get()) {
            case 'update':
                if ($entity->name === 'poll') {
                    if ($form->_result !== null) {
                        $used_ids = [];
                        foreach ($items['*widget_answers']->value_get() as $c_item) {
                            # insert new answer
                            if ($c_item->id === 0)
                                Poll::answer_insert($form->_instance->id, $c_item->text, $c_item->weight);
                            # update current answer
                            if ($c_item->id !== 0) {
                                $form->_answers[$c_item->id]->answer = $c_item->text;
                                $form->_answers[$c_item->id]->weight = $c_item->weight;
                                $form->_answers[$c_item->id]->update();
                                $used_ids[$c_item->id] =
                                          $c_item->id;
                            }
                        }
                        # delete old answers
                        foreach ($form->_answers as $c_answer) {
                            if (!isset($used_ids[$c_answer->id])) {
                                Poll::answer_delete($c_answer->id);
                            }
                        }
                        # reset not actual data (for load new IDs too)
                        $form->_answers = null;
                        $items['*widget_answers']->items_reset();
                        $form->components_build();
                        $form->components_init();
                        # redirect if no error
                        URL::go(URL::back_url_get() ?: $entity->make_url_for_select_multiple());
                    }
                }
                break;
            case 'cancel':
                if ($entity->name === 'poll') {
                    URL::go(URL::back_url_get() ?: $entity->make_url_for_select_multiple());
                }
                break;
        }
    }

}
