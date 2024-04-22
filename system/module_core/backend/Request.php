<?php

##################################################################
### Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ###
##################################################################

namespace effcore;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use stdClass;

abstract class Request {

    const DEFAULT_ADDR = '127.0.0.1';
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 80;

    protected static $cache;
    protected static $allowed_args_in_get = [];

    static function init() {
        if (static::$cache === null) {
            foreach (Storage::get('data')->select_array('request_settings') as $c_module_id => $c_settings) {
                static::$allowed_args_in_get+= $c_settings->allowed_args_in_get;
                static::$cache[$c_module_id] = $c_settings;
            }
        }
    }

    static function allowed_args_in_GET_get() {
        static::init();
        return static::$allowed_args_in_get;
    }

    # ─────────────────────────────────────────────────────────────────────
    # sanitize_args:
    # ─────────────────────────────────────────────────────────────────────

    static function sanitize_args($source = '_GET') {
        $result = [];
        global ${$source};
        if (count(${$source})) {
            $allowed_args = static::allowed_args_in_GET_get();
            foreach (${$source} as $c_name => $c_value) {
                if (isset($allowed_args[$c_name])) {
                    $result[$c_name] = $c_value;
                }
            }
        }
        return $result;
    }

    # ─────────────────────────────────────────────────────────────────────
    # sanitize_structure:
    # ═════════════════════════════════════════════════════════════════════
    #     (string)key_0 => (string)value
    #     ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    #     (string)key_0 => [
    #         (string|int)key_1 => (string)value
    #     ]
    #     ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    #     (string)key_0 => [
    #         (string|int)key_1 => [
    #             (string|int)key_2 => (string)value
    #         ]
    #     ]
    #     ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    #     (string)key_0 => [
    #         (string|int)key_1 => [
    #             (string|int)key_2 => [
    #                 (string|int)key_3 => (string)value
    #             ]
    #         ]
    #     ]
    # ─────────────────────────────────────────────────────────────────────

    static function sanitize_structure($source = '_POST') {
        $result = [];
        global ${$source};
        if (count(${$source})) {
            # filtering by structure
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(${$source}));
            foreach ($iterator as $c_value) {
                $c_depth = $iterator->getDepth();
                $c_k0 = $iterator->getSubIterator(0) ? $iterator->getSubIterator(0)->key() : null;
                $c_k1 = $iterator->getSubIterator(1) ? $iterator->getSubIterator(1)->key() : null;
                $c_k2 = $iterator->getSubIterator(2) ? $iterator->getSubIterator(2)->key() : null;
                $c_k3 = $iterator->getSubIterator(3) ? $iterator->getSubIterator(3)->key() : null;
                if ($c_depth === 0 && is_string($c_k0) &&                                                             is_string($c_value)) $result[$c_k0]                      = $c_value;
                if ($c_depth === 1 && is_string($c_k0) &&    is_int($c_k1) &&                                         is_string($c_value)) $result[$c_k0][$c_k1]               = $c_value;
                if ($c_depth === 1 && is_string($c_k0) && is_string($c_k1) &&                                         is_string($c_value)) $result[$c_k0][$c_k1]               = $c_value;
                if ($c_depth === 2 && is_string($c_k0) &&    is_int($c_k1) &&    is_int($c_k2) &&                     is_string($c_value)) $result[$c_k0][$c_k1][$c_k2]        = $c_value;
                if ($c_depth === 2 && is_string($c_k0) && is_string($c_k1) &&    is_int($c_k2) &&                     is_string($c_value)) $result[$c_k0][$c_k1][$c_k2]        = $c_value;
                if ($c_depth === 2 && is_string($c_k0) &&    is_int($c_k1) && is_string($c_k2) &&                     is_string($c_value)) $result[$c_k0][$c_k1][$c_k2]        = $c_value;
                if ($c_depth === 2 && is_string($c_k0) && is_string($c_k1) && is_string($c_k2) &&                     is_string($c_value)) $result[$c_k0][$c_k1][$c_k2]        = $c_value;
                if ($c_depth === 3 && is_string($c_k0) &&    is_int($c_k1) &&    is_int($c_k2) &&    is_int($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) && is_string($c_k1) &&    is_int($c_k2) &&    is_int($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) &&    is_int($c_k1) && is_string($c_k2) &&    is_int($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) && is_string($c_k1) && is_string($c_k2) &&    is_int($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) &&    is_int($c_k1) &&    is_int($c_k2) && is_string($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) && is_string($c_k1) &&    is_int($c_k2) && is_string($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) &&    is_int($c_k1) && is_string($c_k2) && is_string($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
                if ($c_depth === 3 && is_string($c_k0) && is_string($c_k1) && is_string($c_k2) && is_string($c_k3) && is_string($c_value)) $result[$c_k0][$c_k1][$c_k2][$c_k3] = $c_value;
            }
        }
        return $result;
    }

    # ─────────────────────────────────────────────────────────────────────
    # sanitize_structure_FILES():
    # ═════════════════════════════════════════════════════════════════════
    #     (string)key_0 => [
    #         (string)key_1 => (string|int)value
    #     ]
    #     ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦
    #     (string)key_0 => [
    #         (string)key_1 => [
    #             (int=0)key_2 => (string|int)value,
    #             (int=1)key_2 => (string|int)value …
    #             (int=N)key_2 => (string|int)value
    #         ]
    #     ]
    # ─────────────────────────────────────────────────────────────────────

    static function sanitize_structure_FILES($source = '_FILES') {
        $result = [];
        global ${$source};
        if (count(${$source})) {
            # filtering by structure
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(${$source}));
            foreach ($iterator as $c_value) {
                $c_depth = $iterator->getDepth();
                $c_k0 = $iterator->getSubIterator(0) ? $iterator->getSubIterator(0)->key() : null;
                $c_k1 = $iterator->getSubIterator(1) ? $iterator->getSubIterator(1)->key() : null;
                $c_k2 = $iterator->getSubIterator(2) ? $iterator->getSubIterator(2)->key() : null;
                if ($c_depth === 1 && is_string($c_k0) && is_string($c_k1) &&                  is_string($c_value)) $result[$c_k0][$c_k1]   = $c_value;
                if ($c_depth === 1 && is_string($c_k0) && is_string($c_k1) &&                     is_int($c_value)) $result[$c_k0][$c_k1]   = $c_value;
                if ($c_depth === 2 && is_string($c_k0) && is_string($c_k1) && is_int($c_k2) &&    is_int($c_value)) $result[$c_k0][$c_k1][] = $c_value;
                if ($c_depth === 2 && is_string($c_k0) && is_string($c_k1) && is_int($c_k2) && is_string($c_value)) $result[$c_k0][$c_k1][] = $c_value;
            }
        }
        return $result;
    }

    # conversion matrix:
    # ┌──────────────────────────────────────────╥────────────────┐
    # │ input value (undefined | string | array) ║ result value   │
    # ╞══════════════════════════════════════════╬════════════════╡
    # │ source[field] === undefined              ║ return ''      │
    # │ source[field] === ''                     ║ return ''      │
    # │ source[field] === 'value'                ║ return 'value' │
    # ├──────────────────────────────────────────╫────────────────┤
    # │ source[field] === [undefined]            ║ return ''      │
    # │ source[field] === [0 => '']              ║ return ''      │
    # │ source[field] === [0 => 'value']         ║ return 'value' │
    # └──────────────────────────────────────────╨────────────────┘

    static function value_get($name, $number = 0, $source = '_POST', $default = '', $strict = true) {
        global ${$source};
        if (   !isset(${$source}[$name])) return $default;
        if (is_string(${$source}[$name])) return ${$source}[$name];
        if ( is_array(${$source}[$name])) {
            if (isset(${$source}[$name][$number])) {
                if ($strict !== true                                         ) return ${$source}[$name][$number];
                if ($strict === true && is_string(${$source}[$name][$number])) return ${$source}[$name][$number];
            }
        }
        return $default;
    }

    # conversion matrix:
    # ┌──────────────────────────────────────────╥──────────────────────────┐
    # │ input value (undefined | string | array) ║ result value             │
    # ╞══════════════════════════════════════════╬══════════════════════════╡
    # │ source[field] === undefined              ║ return []                │
    # │ source[field] === ''                     ║ return [0 => '']         │
    # │ source[field] === 'value'                ║ return [0 => 'value']    │
    # ├──────────────────────────────────────────╫──────────────────────────┤
    # │ source[field] === [undefined]            ║ return []                │
    # │ source[field] === [0 => '']              ║ return [0 => '']         │
    # │ source[field] === [0 => '', …]           ║ return [0 => '', …]      │
    # │ source[field] === [0 => 'value']         ║ return [0 => 'value']    │
    # │ source[field] === [0 => 'value', …]      ║ return [0 => 'value', …] │
    # └──────────────────────────────────────────╨──────────────────────────┘

    static function values_get($name, $source = '_POST', $default = [], $strict = true) {
        global ${$source};
        if (   !isset(${$source}[$name])) return $default;
        if (is_string(${$source}[$name])) return [${$source}[$name]];
        if ( is_array(${$source}[$name])) {
            if ($strict !== true) return ${$source}[$name];
            if ($strict === true) {
                foreach (${$source}[$name] as $c_value)
                    if (!is_string($c_value))
                        return $default;
                return ${$source}[$name];
            }
        }
        return $default;
    }

    static function values_set($name, $values, $source = '_POST') {
        global ${$source};
        ${$source}[$name] = $values;
    }

    static function value_reset($name, $source = '_POST') {
        global ${$source};
        unset(
            ${$source}[$name]
        );
    }

    static function values_reset($source = null) {
        if ($source === null) {
            global $_GET;
            global $_POST;
            global $_FILES;
            $_GET   = [];
            $_POST  = [];
            $_FILES = [];
        } else {
            global ${$source};
            ${$source} = [];
        }
    }

    static function values_clone($old_prefix, $new_prefix, $source = '_POST') {
        global ${$source};
        foreach (${$source} as $c_key => $c_value) {
            if (str_starts_with($c_key, $old_prefix)) {
                ${$source}[$new_prefix.substr($c_key, strlen($old_prefix))] = $c_value;
            }
        }
    }

    # conversion matrix:
    # ┌──────────────────────────────────────────────────────────╥───────────────────────────────────────────────────────────────────────┐
    # │ input value (undefined | array)                          ║ result value                                                          │
    # ╞══════════════════════════════════════════════════════════╬═══════════════════════════════════════════════════════════════════════╡
    # │ $_FILES[field] === undefined                             ║ return []                                                             │
    # │ $_FILES[field] === [error = 4]                           ║ return []                                                             │
    # │ $_FILES[field] === [name = 'file']                       ║ return [0 => (object)[name = 'file']]                                 │
    # │ $_FILES[field] === [name = [0 => 'file']]                ║ return [0 => (object)[name = 'file']]                                 │
    # │ $_FILES[field] === [name = [0 => 'file1', 1 => 'file2']] ║ return [0 => (object)[name = 'file1'], 1 => (object)[name = 'file2']] │
    # └──────────────────────────────────────────────────────────╨───────────────────────────────────────────────────────────────────────┘

    static function files_get($name, $return_class_name = 'File_history') {
        $result = [];
        if (isset($_FILES[$name]['name'    ]) &&
            isset($_FILES[$name]['type'    ]) &&
            isset($_FILES[$name]['size'    ]) &&
            isset($_FILES[$name]['tmp_name']) &&
            isset($_FILES[$name]['error'   ])) {
            $info = $_FILES[$name];
            # converting into a unified structure
            if (!is_array($info['name'    ])) $info['name'    ] = [$info['name'    ]];
            if (!is_array($info['type'    ])) $info['type'    ] = [$info['type'    ]];
            if (!is_array($info['size'    ])) $info['size'    ] = [$info['size'    ]];
            if (!is_array($info['tmp_name'])) $info['tmp_name'] = [$info['tmp_name']];
            if (!is_array($info['error'   ])) $info['error'   ] = [$info['error'   ]];
            # preparing the result
            foreach ($info['name'] as $c_number => $c_file) {
                $c_file     = trim($c_file); # note: "name.type"
                $c_mime     = $info['type'    ][$c_number];
                $c_size     = $info['size'    ][$c_number];
                $c_path_tmp = $info['tmp_name'][$c_number];
                $c_error    = $info['error'   ][$c_number];
                if ($c_error !== UPLOAD_ERR_NO_FILE) {
                    if ($return_class_name === 'File_history') {
                        $result[$c_number] = new File_history;
                        $result[$c_number]->init_from_tmp($c_file, $c_mime, $c_size, $c_path_tmp, $c_error);
                    } else {
                        $result[$c_number] = new $return_class_name;
                        $result[$c_number]->file     = $c_file;
                        $result[$c_number]->mime     = $c_mime;
                        $result[$c_number]->size     = $c_size;
                        $result[$c_number]->path_tmp = $c_path_tmp;
                        $result[$c_number]->error    = $c_error;
                    }
                }
            }
        }
        return $result;
    }

    # ┌─────────────────╥───────┬────────────────╥────────┐
    # │        ╲  modes ║       │                ║        │
    # │ server  ╲       ║ HTTPS │ REQUEST_SCHEME ║ result │
    # ╞═════════════════╬═══════╪════════════════╬════════╡
    # │ Apache v2.4     ║ -     │ http           ║ http   │
    # │ Apache v2.4 SSL ║ on    │ https          ║ https  │
    # │ NGINX  v1.1     ║ -     │ http           ║ http   │
    # │ NGINX  v1.1 SSL ║ on    │ https          ║ https  │
    # │ IIS    v7.5     ║ off   │ -              ║ http   │
    # │ IIS    v7.5 SSL ║ on    │ -              ║ https  │
    # └─────────────────╨───────┴────────────────╨────────┘

    static function scheme_get() {
        if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') return 'https';
        if (isset($_SERVER['HTTPS'])          && $_SERVER['HTTPS']          === 'on'   ) return 'https';
        return 'http';
    }

    static function host_get($decode = false) {
        $parts = str_contains($_SERVER['HTTP_HOST'], ':') ? explode(':', $_SERVER['HTTP_HOST']) : [$_SERVER['HTTP_HOST']];
        if ($decode && function_exists('idn_to_utf8') &&
                        idn_to_utf8($parts[0], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46))
            $parts[0] = idn_to_utf8($parts[0], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        return implode(':', $parts);
    }

    static function hostname_get($decode = false) {
        $parts = str_contains($_SERVER['HTTP_HOST'], ':') ? explode(':', $_SERVER['HTTP_HOST']) : [$_SERVER['HTTP_HOST']];
        if ($decode && function_exists('idn_to_utf8') &&
                           idn_to_utf8($parts[0], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46))
               $parts[0] = idn_to_utf8($parts[0], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        return $parts[0];
    }

    static function server_name_get($decode = false) {
        if ($decode && function_exists('idn_to_utf8') &&
                    idn_to_utf8($_SERVER['SERVER_NAME'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46))
             return idn_to_utf8($_SERVER['SERVER_NAME'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        else return $_SERVER['SERVER_NAME'];
    }

    static function addr_get() {
        return Core::is_IIS() ? $_SERVER['LOCAL_ADDR']:
                                $_SERVER['SERVER_ADDR'];
    }

    static function port_get() {
        return $_SERVER['SERVER_PORT'];
    }

    static function addr_remote_get() {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    static function port_remote_get() {
        return $_SERVER['REMOTE_PORT'] ?? '';
    }

    static function URI_get() {
        return Core::is_IIS() ? $_SERVER['UNENCODED_URL'] :
                                $_SERVER['REQUEST_URI'];
    }

    static function path_get() {
        return strstr(static::URI_get(), '?', true) ?:
                      static::URI_get();
    }

    static function query_get() {
        return $_SERVER['QUERY_STRING'];
    }

    static function http_range_get() {
        $result = new stdClass;
        $result->has_range = false;
        $result->min = null;
        $result->max = null;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $result->has_range = true;
            $matches = [];
            preg_match('%^bytes=(?<min>[0-9]+)-'.
                               '(?<max>[0-9]*)$%', $_SERVER['HTTP_RANGE'], $matches);
            if (array_key_exists('min', $matches) && strlen($matches['min'])) $result->min = (int)$matches['min'];
            if (array_key_exists('max', $matches) && strlen($matches['max'])) $result->max = (int)$matches['max'];
        }
        return $result;
    }

    static function user_agent_get($max_length = 240) {
        return                  isset($_SERVER['HTTP_USER_AGENT']) ?
            mb_strcut(trim(strip_tags($_SERVER['HTTP_USER_AGENT'])), 0, $max_length) : '';
    }

    static function web_server_get_info($software = null) {
        $result = new stdClass;
        $result->name = 'CLI';
        $result->version = '';
        if ($software === null && !empty($_SERVER['SERVER_SOFTWARE']))
            $software = $_SERVER['SERVER_SOFTWARE'];
        if ($software) {
            $matches = [];
            preg_match('%^(?<full_name>(?<name>[a-zA-Z0-9\\-]+)/(?<version>[a-zA-Z0-9\\.]+).*)$|'.
                        '^(?<full_name_unknown>.*)$%', $software, $matches);
            if (isset($matches['full_name'        ])) {$result->name = mb_strtolower($matches['name']); $result->version = $matches['version'];}
            if (isset($matches['full_name_unknown'])) {$result->name = mb_strtolower($matches['full_name_unknown']);}
        }
        return $result;
    }

    # ◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦◦

    static function make($url, $headers = [], $post = [], $settings = []) {
        $result = ['info' => [], 'headers' => []];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL           , $url    );
        curl_setopt($curl, CURLOPT_HTTPHEADER    , $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true    );
        curl_setopt($curl, CURLOPT_HEADER        , false   );
        curl_setopt($curl, CURLOPT_PATH_AS_IS    , true    ); # added in CURL v.7.42.0 (2015-04-22)
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, array_key_exists('followlocation', $settings) ? $settings['followlocation'] : false);
        curl_setopt($curl, CURLOPT_TIMEOUT       , array_key_exists('timeout'       , $settings) ? $settings['timeout']        : 5);
        curl_setopt($curl, CURLOPT_SSLVERSION    , array_key_exists('sslversion'    , $settings) ? $settings['sslversion']     : CURL_SSLVERSION_TLSv1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, array_key_exists('ssl_verifyhost', $settings) ? $settings['ssl_verifyhost'] : false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, array_key_exists('ssl_verifypeer', $settings) ? $settings['ssl_verifypeer'] : false);
        curl_setopt($curl, CURLOPT_PROXY         , array_key_exists('proxy'         , $settings) ? $settings['proxy']          : null);
        # prepare post query
        if ($post) {
            curl_setopt($curl, CURLOPT_POST,        true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }
        # prepare headers
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $c_header) use (&$result) {
            $c_matches = [];
            preg_match('%^(?<name>[^:]+): (?<value>.*)$%S', $c_header, $c_matches);
            if ($c_matches && strtolower($c_matches['name']) !== 'set-cookie') $result['headers'][strtolower($c_matches['name'])]   =           trim($c_matches['value'], CR.NL.'"');
            if ($c_matches && strtolower($c_matches['name']) === 'set-cookie') $result['headers'][strtolower($c_matches['name'])][] = ['raw' => trim($c_matches['value'], CR.NL.'"'), 'parsed' => Cookies::parse(trim($c_matches['value'], CR.NL.'"'))];
            return strlen($c_header);
        });
        # prepare return
        $data = curl_exec($curl);
        $result['error_message'] = curl_error($curl);
        $result['error_number' ] = curl_errno($curl);
        $result['data'] = $data ? ltrim($data, "\xff\xfe") : '';
        $result['info'] = curl_getinfo($curl);
        curl_close($curl);
        return $result;
    }

}
