import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, clickMenuItem, expectMenuItemNotVisible, expectMenuItemVisible} from '../testEditor.js';

describe("Menu:configuration", () => {
    it("test exclude menu item by global config", () => {
        window.humhub.richtext.globalOptions = {
            menu: {
                'exclude': ['insertTable']
            }
        };

        initEditor({exclude: ['resizeNav']});
        expectMenuItemNotVisible('insertTable');
        window.humhub.richtext.globalOptions = {};
    });

    it("test exclude dropdown item by global config", () => {
        window.humhub.richtext.globalOptions = {
            menu: {
                'exclude': ['makeHeading1']
            }
        };

        initEditor({exclude: ['resizeNav']});
        clickMenuItem('type ');
        expect($('.ProseMirror-menu-makeHeading1').length).to.equal(0)
        window.humhub.richtext.globalOptions = {};
    });

    it("test exclude menu-group item by global config", () => {
        window.humhub.richtext.globalOptions = {
            menu: {
                'exclude': ['marks-group']
            }
        };

        initEditor({exclude: ['resizeNav']});
        expectMenuItemNotVisible('marks-group');
        window.humhub.richtext.globalOptions = {};
    });

    it("test exclude menu item by preset config", () => {
        window.humhub.richtext.globalOptions = {
            presets: {
                markdown: {
                    menu: {
                        'exclude': ['insertTable']
                    }
                }
            }
        };

        initEditor({preset: 'markdown', exclude: ['resizeNav']});
        expectMenuItemNotVisible('insertTable');

        initEditor({preset: 'full', exclude: ['resizeNav']});
        expectMenuItemVisible('insertTable');
        window.humhub.richtext.globalOptions = {};
    });

    it("test exclude menu item by instance", () => {
        initEditor({
            exclude: ['resizeNav'],
            menu: {
                'exclude': ['insertTable']
            }
        });
        expectMenuItemNotVisible('insertTable');
    });
});
