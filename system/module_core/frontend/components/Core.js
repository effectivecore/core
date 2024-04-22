
//////////////////////////////////////////////////////////////////
/// Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ///
//////////////////////////////////////////////////////////////////

'use strict';

// ─────────────────────────────────────────────────────────────────────
// Core singleton class
// ─────────────────────────────────────────────────────────────────────

export default class Core {

    static get_type(value) {
        return Object.prototype.toString.call(value).slice(8, -1);
    }

    static args_apply(string, args = {}) {
        return string.replace(/%%_([a-zA-Z0-9_]+)/g, (c_arg, c_arg_name) => {
            return args[c_arg_name] !== undefined ?
                   args[c_arg_name] : '';
        });
    }

}
