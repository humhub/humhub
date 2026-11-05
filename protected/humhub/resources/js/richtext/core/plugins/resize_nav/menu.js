/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {icons, MenuItem} from "../../menu";
import {toggleNavPluginKey, isCollapsed} from "./plugin";

function resizeNav(context) {
    return new MenuItem({
        id: 'resizeNav',
        title: "More",
        sortOrder: 400,
        run(state, dispatch) {
            let wasCollapsed = isCollapsed(state);
            dispatch(state.tr.setMeta(toggleNavPluginKey, !wasCollapsed));

            if (wasCollapsed) {
                this.switchIcon(icons.angleDoubleLeft);
            } else {
                this.switchIcon(icons.angleDoubleRight);
            }
        },
        icon: localStorage.getItem("isExpandedToolbar") !== 'true' ? icons.angleDoubleRight : icons.angleDoubleLeft
    });
}

export function getNodes(context) {
    return context.editor.$.find(getSelector(context));
}

export function getSelector(context) {
    return context.getPluginOption('resizeNav', 'selector', SELECTOR_DEFAULT);
}

export function menu(context) {
    return [{
        id: 'resizeNav',
        group: 'resize',
        item: resizeNav(context)
    }];
}

export function menuWrapper(context) {
    return {
        select: function (menuItem, state, active) {
            let collapsed = toggleNavPluginKey.getState(state);

            if (collapsed && menuItem.options.hideOnCollapse) {
                return false;
            }

            return active;
        }
    }
}
