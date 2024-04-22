<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

Timer::tap('total');

# ─────────────────────────────────────────────────────────────────────
# prepare incoming parameters
# ─────────────────────────────────────────────────────────────────────

global $_GET;
global $_GET_ORIGINAL;
global $_POST;
global $_POST_ORIGINAL;
global $_FILES;
global $_FILES_ORIGINAL;

$_GET_ORIGINAL   = $_GET;
$_POST_ORIGINAL  = $_POST;
$_FILES_ORIGINAL = $_FILES;

$_GET   = Request::sanitize_structure('_GET');
$_GET   = Request::sanitize_args('_GET');
$_POST  = Request::sanitize_structure('_POST');
$_FILES = Request::sanitize_structure_FILES();

# ─────────────────────────────────────────────────────────────────────
# redirect on invalid requests (for example: send the value "http://домен/путь?запрос" over the socket instead of "http://xn--d1acufc/%D0%BF%D1%83%D1%82%D1%8C?%D0%B7%D0%B0%D0%BF%D1%80%D0%BE%D1%81" through the browser)
# ─────────────────────────────────────────────────────────────────────

$raw_url = Request::scheme_get().'://'.
           Request::host_get(false).
           Request::URI_get();
if (Security::sanitize_url($raw_url) !== $raw_url || Security::validate_url($raw_url, FILTER_FLAG_PATH_REQUIRED) === false || URL::get_current()->has_error === true) {
    Response::send_header_and_exit('bad_request');
}

# ─────────────────────────────────────────────────────────────────────
# redirect on invalid arguments
# ─────────────────────────────────────────────────────────────────────

if (count($_GET_ORIGINAL)) {
    if (Security::hash_get($_GET) !== Security::hash_get($_GET_ORIGINAL)) {
        Response::send_header_and_exit('redirect', null, null, count($_GET) ?
            Request::scheme_get().'://'.Request::host_get(false).Request::path_get().'?'.http_build_query($_GET, '', '&', PHP_QUERY_RFC3986) :
            Request::scheme_get().'://'.Request::host_get(false).Request::path_get()
        );
    }
}

# ─────────────────────────────────────────────────────────────────────
# redirect to url without leading slash
# ─────────────────────────────────────────────────────────────────────

if (Request::URI_get()     !== '/' &&
    Request::URI_get()[-1] === '/') {
    $new_url = rtrim(Request::URI_get(), '/');
    Response::send_header_and_exit('redirect', null, null,
        $new_url === '' ? '/' :
        $new_url
    );
}

# note:
# ════════════════╦════════════════════════════════════════════════════
# url /           ║ is page 'page-front'
# url /page       ║ is page 'page'
# url /file       ║ is page 'file'
# url /file.type  ║ is file 'file.type'
# ────────────────╨────────────────────────────────────────────────────

#######################
### return the FILE ###
#######################

$file = URL::get_current()->file_info_get();
if ($file instanceof File && $file->type) {

    $file_types = File::types_get();

    # ─────────────────────────────────────────────────────────────────────
    # case for any system file ('.type', '.name.type'…) - show 'forbidden' even if it does not exist!
    # ─────────────────────────────────────────────────────────────────────

    if ( ($file->name !== '' && $file->name[0] === '.') ||
         ($file->type !== '' && $file->name === '') ) {
        Response::send_header_and_exit('file_access_forbidden', null, new Text_multiline([
            'file of this type is protected',
            'go to <a href="/">front page</a>'
        ], [], BR.BR));
    }

    # ─────────────────────────────────────────────────────────────────────
    # case for protected file - show 'forbidden' even if it does not exist!
    # ─────────────────────────────────────────────────────────────────────

    if (isset($file_types[$file->type]->kind) &&
              $file_types[$file->type]->kind === 'protected') {
        Response::send_header_and_exit('file_access_forbidden', null, new Text_multiline([
            'file of this type is protected',
            'go to <a href="/">front page</a>'
        ], [], BR.BR));
    }

    # ─────────────────────────────────────────────────────────────────────
    # case for virtual file
    # ─────────────────────────────────────────────────────────────────────

    if (isset($file_types[$file->type]->kind) &&
              $file_types[$file->type]->kind === 'virtual') {
        $type = $file_types[$file->type];
        Event::start('on_file_load', 'virtual', ['type_info' => &$type, 'file' => &$file]);
        exit();
    }

    # ─────────────────────────────────────────────────────────────────────
    # protecting files from attacks
    # ─────────────────────────────────────────────────────────────────────

    $type = $file_types[$file->type] ?? (object)['type' => $file->type, 'module_id' => null];
    $real_path = Security::validate_realpath($file->path_get());
    if ($real_path === false)                   {Event::start('on_file_load', 'not_found', ['type_info' => &$type, 'file' => &$file, 'real_path' => $real_path, 'phase' => 1]); exit();} # object does not really exist or object is inaccessible to the web server by rights
    if ($real_path !== $file->path_get())       {Event::start('on_file_load', 'not_found', ['type_info' => &$type, 'file' => &$file, 'real_path' => $real_path, 'phase' => 2]); exit();} # resolved path is not the same as the original
    if (!str_starts_with($real_path, DIR_ROOT)) {Event::start('on_file_load', 'not_found', ['type_info' => &$type, 'file' => &$file, 'real_path' => $real_path, 'phase' => 3]); exit();} # object is outside the web root
    if (!is_file    ($file->path_get()))        {Event::start('on_file_load', 'not_found', ['type_info' => &$type, 'file' => &$file, 'real_path' => $real_path, 'phase' => 4]); exit();} # object exists, but it is not a file
    if (!is_readable($file->path_get())) Response::send_header_and_exit('file_access_forbidden'); # object is inaccessible to the web server by rights

    # ─────────────────────────────────────────────────────────────────────
    # case for dynamic file
    # ─────────────────────────────────────────────────────────────────────

    if (isset($file_types[$file->type]->kind) &&
              $file_types[$file->type]->kind === 'dynamic') {
        $type = $file_types[$file->type];
        Event::start('on_file_load', 'dynamic', ['type_info' => &$type, 'file' => &$file]);
        exit();

    # ─────────────────────────────────────────────────────────────────────
    # case for static file
    # ─────────────────────────────────────────────────────────────────────

    } else {
        $type = $file_types[$file->type] ?? (object)['type' => $file->type, 'module_id' => null];
        Event::start('on_file_load', 'static', ['type_info' => &$type, 'file' => &$file]);
        exit();
    }

}

#######################
### return the PAGE ###
#######################

if (!Storage::get('sql')->is_installed()) {
    if (!preg_match('%'.'^/install$'.'|'.'^/install/[a-z]{2}$'.'|'.'^/api/.*$'.'%', URL::get_current()->path)) {
        URL::go('/install/en');
    }
}

# ─────────────────────────────────────────────────────────────────────
# cron autorun
# ─────────────────────────────────────────────────────────────────────

if (Storage::get('sql')->is_installed()) {
    if (Cron::get_auto_run_frequency()) {
        if (!Cron::is_runned()) {
            if (!preg_match('%^/api/core/cron/run/.*$%', URL::get_current()->path)) {
                Cron::run();
            }
        }
    }
}

# ─────────────────────────────────────────────────────────────────────
# page search and display
# ─────────────────────────────────────────────────────────────────────

ob_start();
$result = '';
foreach (Event::start('on_module_start') as $c_results) {
    foreach ($c_results as $c_result) {
        if ($c_result) {
            $result.= $c_result;
        }
    }
}
Timer::tap('total');
if (Console::visible_mode_get()) {
    $result = str_replace('</body>', Console::markup_get()->render().'</body>', $result);
}
if (Module::is_enabled('test')) {
    header('x-web-server-name: '.Request::web_server_get_info()->name);
    header('x-time-total: '.Timer::period_get('total', 0, 1));
    header('x-php-memory-usage: '.memory_get_usage(true));
    header('x-return-level: system-page');
}
header('cache-control: private, no-cache');
header('content-length: '.strlen($result));
print $result;
exit();
