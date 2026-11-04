/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

import {menu, minimize, maximize} from "./menu";
import {isSmallView} from "../../humhub-bridge";

const fullscreen = {
    id: 'fullscreen',
    init(context) {
        if (context.getPluginOption('fullscreen', 'autoFullScreen') === true) {
            context.editor.$.on('click', '.ProseMirror', function (e) {
                if (isSmallView && !context.editor.$.is('.fullscreen')) {
                    maximize(context);
                }
            });
        }

        context.editor.$.on('clear', function () {
            minimize(context);
        });
    },
    menu: (context) => {
        let fullScreenMenu = menu(context);
        context.fullScreenMenuItem = fullScreenMenu[0].item;
        return fullScreenMenu;
    }
};

export default fullscreen;
