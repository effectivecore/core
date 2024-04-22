<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore\modules\user;

use effcore\Core;
use effcore\Mail;
use effcore\Message;
use effcore\Module;
use effcore\Session;
use effcore\Text_multiline;
use effcore\Text;
use effcore\URL;
use effcore\User;

abstract class Events_Form_Registration {

    static function on_build($event, $form) {
        $form->env['session'] = Session::select();
    }

    static function on_init($event, $form, $items) {
        $settings = Module::settings_get('user');
        $items['#session_params:is_long_session']->attributes['title'] = new Text_multiline([
            'Short session: %%_short day%%_plural(short|s) | long session: %%_long day%%_plural(long|s)'], [
            'short' => $settings->session_duration_short,
            'long'  => $settings->session_duration_long], '', true, true);
        $items['#password']->disabled_set((bool)$settings->send_password_to_email);
        $items['#email'   ]->value_set('');
        $items['#nickname']->value_set('');
        if (!$form->env['session']) {
            $items['~register']->disabled_set(false);
        }
    }

    static function on_submit($event, $form, $items) {
        switch ($form->clicked_button->value_get()) {
            case 'register':
                $settings = Module::settings_get('user');

                # ────────────────────────────────────────────────────────────────────────────────────────────────────
                # registration via EMail address: a password is generated and sent to the user-specified EMail address
                # ────────────────────────────────────────────────────────────────────────────────────────────────────

                if ($settings->send_password_to_email) {
                    $new_password = User::password_generate();
                    $user = User::insert([
                        'email'         => $items['#email'   ]->value_get(),
                        'nickname'      => $items['#nickname']->value_get(),
                        'timezone'      => $items['#timezone']->value_get(),
                        'password_hash' => User::password_hash($new_password)
                    ]);
                    if ($user) {
                        $domain = URL::get_current()->domain;
                        if (Mail::send('registration', 'no-reply@'.$domain, $user, ['domain' => $domain], ['domain' => $domain, 'new_password' => $new_password], $form, $items)) {
                            Message::insert('A new password was sent to the selected EMail address.');
                            URL::go(URL::back_url_get() ?: '/login');
                        }
                    } else {
                        Message::insert(
                            'User was not registered!', 'error'
                        );
                    }
                } else {

                    # ─────────────────────────────────────────────────────────────────────
                    # standard registration: the user sets his own password
                    # ─────────────────────────────────────────────────────────────────────

                    $user = User::insert([
                        'email'         => $items['#email'   ]->value_get(),
                        'nickname'      => $items['#nickname']->value_get(),
                        'timezone'      => $items['#timezone']->value_get(),
                        'password_hash' => $items['#password']->value_get()
                    ]);
                    if ($user) {
                        Session::insert($user->id,
                            Core::array_keys_map($items['*session_params']->value_get())
                        );
                        Message::insert(
                            new Text('Welcome, %%_nickname!', ['nickname' => $user->nickname])
                        );
                        URL::go(URL::back_url_get() ?: '/user/'.$user->nickname);
                    } else {
                        Message::insert(
                            'User was not registered!', 'error'
                        );
                    }
                }
                break;
        }
    }

}
