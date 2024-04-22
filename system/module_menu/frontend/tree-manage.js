
//////////////////////////////////////////////////////////////////
/// Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ///
//////////////////////////////////////////////////////////////////

'use strict';

import Rearrange from '/system/module_core/frontend/components/Rearrange.js';

document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────────────────────────────────
    // rearrangeable
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('[role="tree"][data-manage-mode="rearrange"]' + ':not([data-rearrange-is-processed])').forEach((c_root) => {
        new Rearrange(c_root, 'tree', 'li', 'ul',
            'input[data-role="weight"]',
            'input[data-role="parent"]'
        );
    });

});
