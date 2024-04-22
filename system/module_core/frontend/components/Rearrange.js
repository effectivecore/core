
//////////////////////////////////////////////////////////////////
/// Copyright © 2017—2024 Maxim Rysevets. All rights reserved. ///
//////////////////////////////////////////////////////////////////

'use strict';

import BaseComponent from './BaseComponent.js';

 /*
  * Tree model #1. Like Menu (UL/LI).
  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  *
  *             ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓                   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
  *             ┃ RECEIVING ITEM (LI)                             ┃                   ┃ DRAGGED ITEM (LI)                               ┃
  *             ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫                   ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
  *             │ <x-drop_area data-placement="before"/>          │◀────┐             │ <x-drop_area data-placement="before"/>          │
  *    ┌────────┴───────┬─────────────────────────────────────────┤     │    ┌────────┴───────┬─────────────────────────────────────────┤
  *    │                │ title                                   │     │    │                │ title                                   │
  *    │                ├─────────────────────────────────────────┤     │    │                ├─────────────────────────────────────────┤
  *    │ <x-drag-icon/> │ UL ┊ <x-drop_area data-placement="in"/> │◀────┤───── <x-drag-icon/> │ UL ┊ <x-drop_area data-placement="in"/> │
  *    │                │    ┊┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄ │     │    │                │    ┊┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄ │
  *    │                │    ┊              content               │     │    │                │    ┊              content               │
  *    └────────┬───────┴─────────────────────────────────────────┤     │    └────────┬───────┴─────────────────────────────────────────┤
  *             │ <x-drop_area data-placement="after"/>           │◀────┘             │ <x-drop_area data-placement="after"/>           │
  *             └─────────────────────────────────────────────────┘                   └─────────────────────────────────────────────────┘
  *
  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  */

 /*
  * Tree model #2. Like Page Blocks in Area.
  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  *
  *    ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓      ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
  *    ┃ <x-drop_area data-placement="in"/>                      ┃      ┃ <x-drop_area data-placement="in"/>                      ┃
  *    ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫      ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
  *    │                                                         │      │          ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
  *    │                                                         │      │          ┃ RECEIPT ITEM #1                            ┃ │
  *    │                        NO ITEMS                         │◀──┐  │          ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫ │
  *    │                                                         │   │  │          │ <x-drop_area data-placement="before"/>     │ │
  *    │                                                         │   │  │ ┌────────┴───────┬────────────────────────────────────┤ │
  *    └─────────────────────────────────────────────────────────┘   ├───── <x-drag-icon/> │              content               │ │
  *                                                                  │  │ └────────┬───────┴────────────────────────────────────┤ │
  *                                                                  │  │          │ <x-drop_area data-placement="after"/>      │ │
  *                                                                  │  │          └────────────────────────────────────────────┘ │
  *                                                                  │  │          ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
  *                                                                  │  │          ┃ RECEIPT ITEM #2                            ┃ │
  *                                                                  │  │          ┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫ │
  *                                                                  │  │          │ <x-drop_area data-placement="before"/>     │ │
  *                                                                  │  │ ┌────────┴───────┬────────────────────────────────────┤ │
  *                                                                  │  │ │ <x-drag-icon/> │              content               │ │
  *                                                                  │  │ └────────┬───────┴────────────────────────────────────┤ │
  *                                                                  └────────────▶│ <x-drop_area data-placement="after"/>      │ │
  *                                                                     │          └────────────────────────────────────────────┘ │
  *                                                                     └─────────────────────────────────────────────────────────┘
  *
  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  */

export default class Rearrange extends BaseComponent {

    constructor(root, type, item_selector, area_selector = null, weight_selector = null, parent_selector = null) {
        super();
        this.cache = {drag_items: [], drop_areas: []};
        this.drag_icon = null;
        this.root = root;
        this.type = type;
        this.item_selector = item_selector;
        this.area_selector = area_selector;
        this.weight_selector = weight_selector;
        this.parent_selector = parent_selector;
        if (type === 'flat') this.build_flat();
        if (type === 'tree') this.build_tree();
        this.root.setAttribute('data-rearrange-type', type);
        this.root.setAttribute('data-rearrange-is-processed', '');
        this.update_areas_state();
    }

    build_flat() {
        this.root.querySelectorAll(this.item_selector).forEach((c_item) => {
            if (c_item.getAttribute('data-rearrange-item') === null) {
                c_item.setAttribute('data-rearrange-item', '');
                c_item.prepend(this.template_drag_icon_get());
                c_item.prepend(this.template_drop_area_get('before'));
                c_item. append(this.template_drop_area_get('after'));
                this.cache.drag_items.push(c_item);
            }
        });
    }

    build_tree() {
        this.build_flat();
        this.root.querySelectorAll(this.area_selector).forEach((c_item) => {
            if (c_item.getAttribute('data-rearrange-area') === null) {
                c_item.setAttribute('data-rearrange-area', '');
                c_item.prepend(this.template_drop_area_get('in'));
                this.cache.drop_areas.push(c_item);
            }
        });
    }

    //////////////////////////////////////////////////////////////////

    template_drag_icon_get() {
        return this.markup('x-drag-icon', {
            'draggable'  : true,
            'ondragstart': 'on_drag_start',
            'ondragend'  : 'on_drag_end'
        });
    }

    template_drop_area_get(placement) {
        return this.markup('x-drop-area', {
            'data-placement': placement,
            'ondragenter'   : 'on_dragenter',
            'ondragover'    : 'on_dragover',
            'ondragleave'   : 'on_dragleave',
            'ondrop'        : 'on_drop'
        });
    }

    //////////////////////////////////////////////////////////////////

    update_weight(drag_item, drop_item, placement) {
        if (this.weight_selector) {
            let c_weight = 0;
            for (const c_item of drag_item.parentNode.children) {
                if (c_item.getAttribute('data-rearrange-item') !== null) {
                    let c_input = c_item.querySelector(this.weight_selector);
                    if (c_input) {
                        c_input.value = c_weight;
                        c_weight -= 5;
                    }
                }
            }
        }
    }

    update_parent(drag_item, drop_item, placement) {
        if (this.parent_selector) {
            let input_parent = drag_item.querySelector(this.parent_selector);
            if (input_parent) {
                input_parent.value = drag_item.parentNode.getAttribute('data-rearrange-parent-id');
            }
        }
    }

    update_areas_state(drag_item, drop_item, placement) {
        if (this.type === 'tree') {
            for (const c_area of this.cache.drop_areas) {
                if (c_area.children.length > 1)
                     c_area.setAttribute('data-rearrange-area-is-empty', 'N');
                else c_area.setAttribute('data-rearrange-area-is-empty', 'Y');
            }
        }
    }

    //////////////////////////////////////////////////////////////////

    on_drag_start(drag_icon) {
        this.drag_icon = drag_icon;
        this.root           .setAttribute('data-rearrange-is-active'     , '');
        drag_icon.parentNode.setAttribute('data-rearrange-item-is-active', '');
    }

    on_drag_end(drag_icon) {
        this.drag_icon = null;
        this.root           .removeAttribute('data-rearrange-is-active'     );
        drag_icon.parentNode.removeAttribute('data-rearrange-item-is-active');
    }

    on_dragenter(drop_area) {
        drop_area.setAttribute('data-drop-is-active', '');
    }

    on_dragover(drop_area) {
        event.preventDefault();
    }

    on_dragleave(drop_area) {
        drop_area.removeAttribute('data-drop-is-active');
    }

    on_drop(drop_area) {
        drop_area.removeAttribute('data-drop-is-active');
        let placement = drop_area.getAttribute('data-placement');
        let drag_item = this.drag_icon.parentNode;
        let drop_item =      drop_area.parentNode;
        if (drop_item === drag_item                                          ) {console.log('Dragging into yourself was detected!'); return;}
        if (drop_item === drag_item.nextSibling     && placement === 'before') {console.log('Dragging into neighbor was detected!'); return;}
        if (drop_item === drag_item.previousSibling && placement === 'after' ) {console.log('Dragging into neighbor was detected!'); return;}
        if (this.type === 'tree') {
            let drag_item__children = new Set([
                ...drag_item.querySelectorAll(this.item_selector),
                ...drag_item.querySelectorAll(this.area_selector)
            ]);
            for (const c_drag_child of drag_item__children) {
                if (drop_item === c_drag_child) {
                    console.log('Dragging into children was detected!');
                    return
                }
            }
        }
        if (placement === 'before') drop_item.parentNode.insertBefore(drag_item, drop_item            );
        if (placement === 'after' ) drop_item.parentNode.insertBefore(drag_item, drop_item.nextSibling);
        if (placement === 'in'    ) drop_item           .insertBefore(drag_item, drop_area.nextSibling);
        this.update_weight(drag_item, drop_item, placement);
        this.update_parent(drag_item, drop_item, placement);
        this.update_areas_state();
    }

}
