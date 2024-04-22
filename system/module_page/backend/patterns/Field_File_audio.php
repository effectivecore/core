<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

#[\AllowDynamicProperties]

class Field_File_audio extends Field_File {

    public $title = 'Audio';
    public $item_title = 'Audio';
    public $attributes = [
        'data-type' => 'file-audio'];
    public $element_attributes = [
        'type' => 'file',
        'name' => 'audio'];
    public $max_file_size = '10M';
    public $types_allowed = [
        'mp3' => 'mp3'];
    public $audio_player_on_manage_is_visible = true;
    public $audio_player_on_manage_settings = [
        'data-player-name' => 'default',
        'autoplay'         => false,
        'controls'         => true,
        'crossorigin'      => null,
        'loop'             => false,
        'muted'            => false,
        'preload'          => 'metadata'
    ];

    ###########################
    ### static declarations ###
    ###########################

    static function controls_markup__manage__item_title($field, $item, $id, $scope) {
        if ($field->audio_player_on_manage_is_visible) {
            $player_markup = new Markup('audio', ['src' => Core::to_url_from_path($item->get_current_path(true))] + $field->audio_player_on_manage_settings, [], +450);
               return new Node([], [$player_markup, new Text('audio "%%_audio"', ['audio' => $item->file])]);
        } else return new Node([], [                new Text('audio "%%_audio"', ['audio' => $item->file])]);
    }

}
