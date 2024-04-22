
//////////////////////////////////////////////////////////////////
/// Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ///
//////////////////////////////////////////////////////////////////

'use strict';

import Rearrange from '/system/module_core/frontend/components/Rearrange.js';
import Translation from '/system/module_locale/frontend/components/Translation.jsd';

document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────────────────────────────────
    // range
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('x-field[data-type="range"] input[type="range"]').forEach((c_input) => {
        c_input.parentNode.querySelectorWithProcessing('x-value', (x_value) => {
            c_input.addEventListener('mousemove', () => {
                x_value.innerText = c_input.title = c_input.value;
            });
        });
    });

    // ─────────────────────────────────────────────────────────────────────
    // color
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('x-field[data-type="color"] input[type="color"]').forEach((c_input) => {
        c_input.parentNode.querySelectorWithProcessing('x-value', (x_value) => {
            c_input.addEventListener('change', () => {
                x_value.innerText = c_input.title = c_input.value;
            });
        });
    });

    // ─────────────────────────────────────────────────────────────────────
    // timezone
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('select[data-source="uagent-timezone"]').forEach((c_timezone) => {
        if (c_timezone.value === '' && window.Intl) {
            c_timezone.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
        }
    });

    // ─────────────────────────────────────────────────────────────────────
    // operator 'is null' + 'is not null' on Selection edit page
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('x-widget[data-type="items-query-conditions"] [data-type="manage"] x-widget[data-row-id]').forEach((c_row_widget) => {
        let c_field_operator = c_row_widget.querySelector('x-field[data-role="operator"] select');
        let c_field_value    = c_row_widget.querySelector('x-field[data-role="value"] input');
        if (c_field_operator && c_field_value) {
            c_field_operator.addEventListener('change', () => {
                if (c_field_operator.value === 'is null' ||
                    c_field_operator.value === 'is not null') {
                    c_field_value.value = 'n/a';
                }
            });
        }
    });

    // ─────────────────────────────────────────────────────────────────────
    // palette
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('x-group[data-type="palette"]').forEach((c_palette) => {
        c_palette.querySelectorWithProcessing('input[data-opener-type="palette"]', (opener) => {
            c_palette.querySelectorAll('x-field input').forEach((c_input) => {
                c_input.addEventListener('click', () => {
                    opener.style.backgroundColor = c_input.style.backgroundColor;
                    opener.value                 = c_input.value;
                });
            });
        });
    });

    // ─────────────────────────────────────────────────────────────────────
    // table-adaptive + check all
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('[data-selection]').forEach((c_selection) => {
        c_selection.querySelectorWithProcessing('[data-decorator][data-view-type="table-adaptive"]', (decorator) => {
            let head_cell       = decorator.querySelector   ('x-head x-cell[data-cell-id$="checkbox_select"]');
            let body_checkboxes = decorator.querySelectorAll('x-body x-cell[data-cell-id$="checkbox_select"] input[type="checkbox"]');
            let query_checker   = 'x-body x-cell[data-cell-id$="checkbox_select"] input[type="checkbox"]:not(:checked)';
            if (head_cell && body_checkboxes.length) {
                // insert checkbox "check_all"
                let check_all = document.createElement('input');
                    check_all.type = 'checkbox';
                    check_all.title = Translation.get('select all rows');
                    check_all.checked = decorator.querySelectorAll(query_checker).length === 0;
                head_cell.append(check_all);
                // when clicking on "check_all"
                check_all.addEventListener('change', () => {
                    body_checkboxes.forEach((c_checkbox) => {
                        c_checkbox.checked = check_all.checked;
                    });
                });
                // when clicking on single checkbox
                body_checkboxes.forEach((c_checkbox) => {
                    c_checkbox.addEventListener('change', () => {
                        check_all.checked = decorator.querySelectorAll(
                            query_checker
                        ).length === 0;
                    });
                });
            }
        });
    });

    // ─────────────────────────────────────────────────────────────────────
    // rearrangeable
    // ─────────────────────────────────────────────────────────────────────

    document.querySelectorAll('x-layout-manager' + ':not([data-rearrange-is-processed])').forEach((c_root) => {
        new Rearrange(c_root, 'tree',
            'x-widget[data-type="items-blocks"] > x-widgets-group[data-type="manage"] > x-widget',
            'x-widget[data-type="items-blocks"] > x-widgets-group[data-type="manage"]',
            'input[data-role="weight"]',
            'input[data-role="parent"]'
        );
    });

    document.querySelectorAll('x-widget[data-type^="items"]' + ':not([data-rearrange-is-processed])').forEach((c_root) => {
        if (c_root.getAttribute('data-type') !== 'items-blocks') {
            new Rearrange(c_root, 'flat',
                'x-widgets-group[data-type="manage"] > x-widget', null,
                'input[data-role="weight"]',
                'input[data-role="parent"]'
            );
        }
    });

});
