import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, clickMenuItem, expectMenuItemNotVisible, expectMenuItemVisible} from '../../testEditor.js';

describe("Plugin:resize_nav", () => {

    function testVisibleInMinimzedNav(name, selector) {
        it(`${name} visible in minimized nav`, function() {
            initEditor();
            expectMenuItemVisible(selector);
        });
    }

    function testNotVisibleInMinimizedNav(name, selector) {
        it(`${name} not visible in minimized nav`, function() {
            initEditor();
            expectMenuItemNotVisible(selector);
        });
    }

    function testVisibleInExpandedNav(name, selector) {
        it(`${name} visible in expanded nav`, function() {
            initEditor();
            clickMenuItem('resizeNav');
            expectMenuItemVisible(selector);
        });
    }

    function testVisibleInCollapsedNav(name, selector) {
        it(`${name} visible in collapsed nav`, function() {
            initEditor();
            clickMenuItem('resizeNav');
            clickMenuItem('resizeNav');
            expectMenuItemVisible(selector);
        });
    }

    function testNotVisibleInCollapsedNav(name, selector) {
        it(`${name} not visible in collapsed nav`, function() {
            initEditor();
            clickMenuItem('resizeNav');
            clickMenuItem('resizeNav');
            expectMenuItemNotVisible(selector);
        });
    }

    let invisibleOnInit = [
        ['helper-group', 'helper-group'],
        ['format-group', 'format-group'],
        ['insertTable', 'insertTable'],
        ['fullscreen', 'fullScreen'],
        ['source', 'source-group'],
    ];

    let visibleOnInit = [
        ['text types', 'type'],
        ['emoji', 'insertEmoji'],
        ['insert dropdown', 'insert-dropdown'],
        ['marks-group', 'marks-group'],
        ['resize nav ', 'resizeNav'],
    ]

    invisibleOnInit.forEach((item) => testNotVisibleInMinimizedNav(...item));
    visibleOnInit.forEach((item) => testVisibleInMinimzedNav(...item));
    invisibleOnInit.concat(visibleOnInit).forEach((item) => testVisibleInExpandedNav(...item));

    invisibleOnInit.forEach((item) => testNotVisibleInCollapsedNav(...item));
    visibleOnInit.forEach((item) => testVisibleInCollapsedNav(...item));
});