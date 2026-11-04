/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {icons, MenuItem} from "../../menu"
import {
    isSourceMode,
    switchToSourceMode,
    switchToRichtextMode,
    sourcePluginKey,
    EDIT_MODE_SOURCE,
} from "./plugin"

const switchMode = function (context) {
    return new MenuItem({
        id: 'source',
        title: context.translate("Switch editor mode"),
        icon: icons.markdown,
        hideOnCollapse: true,
        run: (state, dispatch) => {
            if (isSourceMode(state)) {
                switchToRichtextMode(context);
                // We do not need to dispatch a transaction, since the editor will be reinitialized anyways
            } else {
                switchToSourceMode(context);
                dispatch(state.tr.setMeta(sourcePluginKey, EDIT_MODE_SOURCE));
            }
        },
        select: state => true,
        active(state) {
            return isSourceMode(state);
        }
    });
};

export function menu(context) {
    return [{
        type: 'group',
        id: 'source-group',
        sortOrder: 550,
        items: [switchMode(context)]
    }];
}

export function menuWrapper(context) {
    return {
        run: function(menuItem, state) {
            if (menuItem.options.id === 'source' || !isSourceMode(state)) {
                return false;
            }

            if (menuItem.runSource) {
                menuItem.runSource();
                return true;
            }

            return false;
        },
        enable: function(menuItem, state, enabled) {
            let sourceMode = isSourceMode(state);
            const enabledButtons = [
                'main-menu-group',
                'marks',
                'marks-group',
                'source',
                'source-group',
                'resize-group',
                'resizeNav',
                'fullScreen'
            ];

            if (enabledButtons.includes(menuItem.options.id) || (sourceMode && menuItem.runSource)) {
                return enabled;
            }

            return sourceMode ? false : enabled;
        },
        active: function(menuItem, state, active) {
            if (['main-menu-group', 'source', 'source-group'].includes(menuItem.options.id)) {
                return active;
            }

            return (isSourceMode(state)) ? false : active;
        }
    }
}
