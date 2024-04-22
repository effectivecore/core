
//////////////////////////////////////////////////////////////////
/// Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ///
//////////////////////////////////////////////////////////////////

'use strict';

import AudioPlayerComponent from '/system/module_page/frontend/components/audio_player/AudioPlayerComponent.js';
import GalleryComponent from '/system/module_page/frontend/components/gallery_player/GalleryComponent.js';
import Token from '/system/module_core/frontend/components/Token.jsd';

document.addEventListener('DOMContentLoaded', () => {

    document.addEventListener('touchstart', () => {
        // activate hover state on iOS devices
    });

    ////////////////////
    /// audio player ///
    ////////////////////

    document.querySelectorAll('audio[data-player-name="default"]:not([data-player-audio-default-is-processed])').forEach((c_audio) => {
        (new AudioPlayerComponent(c_audio)).mount();
    });

    //////////////////////
    /// gallery player ///
    //////////////////////

    document.querySelectorAll('x-galleries-group[data-player-name="default"] x-gallery:not([data-player-is-processed])').forEach((c_gallery) => {
        let data = [];

        c_gallery.querySelectorAll('x-item').forEach((c_item, num) => {
            switch (c_item.getAttribute('data-type')) {
                case 'picture':
                    let picture = c_item.querySelector('img');
                    data.push({
                        'type'     : 'picture',
                        'thumbnail': picture.getAttribute('data-path-thumb-small'),
                        'picture'  : {
                            'src': picture.getAttribute('data-path-thumb-big')
                        }
                    });
                    break;
                case 'audio':
                    let audio = c_item.querySelector('audio');
                    let audio_thumbnail = audio.getAttribute('data-path-cover-small');
                    let audio_thumbnail_default = '/' + Token.get('thumbnail_path_cover_default');
                    let audio_cover = audio.getAttribute('data-path-cover-middle');
                    data.push({
                        'type'     : 'audio',
                        'thumbnail': audio_thumbnail ? audio_thumbnail : audio_thumbnail_default,
                        'picture'  : audio_cover ? {'src': audio_cover} : null,
                        'audio'    : {
                            'src'             : audio.getAttribute('src'),
                            'data-player-name': audio.getAttribute('data-player-name'),
                            'preload'         : 'metadata',
                            'controls'        : true
                        }
                    });
                    break;
                case 'video':
                    let video = c_item.querySelector('video');
                    let video_thumbnail = video.getAttribute('data-path-poster-small');
                    let video_thumbnail_default = '/' + Token.get('thumbnail_path_poster_default');
                    let video_poster = video.getAttribute('data-path-poster-middle');
                    let video_poster_default = '/' + Token.get('thumbnail_path_poster_default');
                    data.push({
                        'type'     : 'video',
                        'thumbnail': video_thumbnail ? video_thumbnail : video_thumbnail_default,
                        'picture'  : null,
                        'video'    : {
                            'src'     : video.getAttribute('src'),
                            'poster'  : video_poster ? video_poster : video_poster_default,
                            'preload' : 'metadata',
                            'controls': true
                        }
                    });
                    break;
            }

            c_item.addEventListener('click', () => {
                event.preventDefault();
                event.stopPropagation();
                c_js_gallery.show(num);
            }, true);
        });

        let c_js_gallery = new GalleryComponent(data, (type, centerer) => {
            if (type === 'audio') {
                centerer.querySelectorAll('audio[data-player-name="default"]').forEach((c_audio) => {
                    (new AudioPlayerComponent(c_audio)).mount();
                });
            }
        });

        c_js_gallery.mount(document.body);
        c_gallery.setAttribute('data-player-is-processed', 'true');
    });

});
