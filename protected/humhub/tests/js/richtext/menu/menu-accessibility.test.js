import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, pressKey, toHtml} from '../testEditor.js';

describe("Menu:accessibility", () => {
    const DIR_RIGHT = 39;
    const DIR_LEFT = 37;

    const DIR_DOWN = 40;
    const DIR_UP = 38;

    const navigate = function (dir, expectFocusId) {
        const e = jQuery.Event("keydown");
        e.which = dir;

        $(':focus').trigger(e);
        //$('.ProseMirror-menubar').trigger(e);

        if (expectFocusId) {
            expectFocus(expectFocusId);
        }
    };

    /* const navigateDropDown = function (id, dir, expectFocusId) {
         var e = jQuery.Event("keydown");
         e.which = dir;
         $('.ProseMirror-menu-' + id).parent('.ProseMirror-menu-dropdown-wrap').trigger(e);
     };*/

    const pressEnter = function () {
        const e = jQuery.Event("keydown");
        e.which = 13;
        $(':focus').trigger(e);
    };

    const pressEscape = function () {
        const e = jQuery.Event("keydown");
        e.which = 27;
        $(':focus').trigger(e);
    };

    const expectFocus = function (id) {
        if (!canAssertFocus()) {
            return;
        }
        expect($('.ProseMirror-menu-' + id + ':focus').length).to.equal(1);
    };

    const focusMenuItem = function (id) {
        id = id || 'type'; // first element
        const element = $('.ProseMirror-menu-' + id)[0];
        if (element && element.focus) {
            element.focus();
        } else {
            $('.ProseMirror-menu-' + id).trigger('focus');
        }
    };

    it("test keyboard right arrow navigation", () => new Promise((done) => {
        initEditor();
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            navigate(DIR_RIGHT, 'markStrong');
            navigate(DIR_RIGHT, 'markEm');
            navigate(DIR_RIGHT, 'markStrikethrough');
            navigate(DIR_RIGHT, 'markCode');
            navigate(DIR_RIGHT, 'linkItem');
            navigate(DIR_RIGHT, 'insertEmoji');
            navigate(DIR_RIGHT, 'insert-dropdown');
            navigate(DIR_RIGHT, 'resizeNav');
            done();
        });
    }));

    it("test keyboard left navigation", () => new Promise((done) => {
        initEditor();
        focusMenuItem('resizeNav');
        wait(() => {
            expectFocus('resizeNav');
            navigate(DIR_LEFT, 'insert-dropdown');
            navigate(DIR_LEFT, 'insertEmoji');
            navigate(DIR_LEFT, 'linkItem');
            navigate(DIR_LEFT, 'markCode');
            navigate(DIR_LEFT, 'markStrikethrough');
            navigate(DIR_LEFT, 'markEm');
            navigate(DIR_LEFT, 'markStrong');
            navigate(DIR_LEFT, 'type');
            done();
        });
    }));

    it("test every toolbar control is named", () => {
        initEditor();
        const unnamed = $('.ProseMirror-menubar .ProseMirror-menu-trigger').filter((i, el) => {
            const name = el.getAttribute('aria-label') || el.getAttribute('title') || el.textContent.trim();
            return !name || name === 'undefined';
        }).map((i, el) => el.className).get();
        expect(unnamed).to.deep.equal([]);
        expect($('.ProseMirror-menu-tableOptions').attr('title')).to.equal('Table options');
    });

    it("test the toolbar is one Tab stop", () => new Promise((done) => {
        initEditor();
        const inTabOrder = () => $('.ProseMirror-menubar .ProseMirror-menu-trigger')
            .not('.ProseMirror-menu-dropdown-menu .ProseMirror-menu-trigger')
            .not('.d-none')
            .filter((i, el) => el.getAttribute('tabindex') !== '-1');

        // Before anything is focused, exactly one button is reachable by Tab: the first one
        expect(inTabOrder().length).to.equal(1);
        expect(inTabOrder().is('.ProseMirror-menu-type')).to.be.true;

        focusMenuItem();
        wait(() => {
            // The arrow keys move the one Tab stop along
            navigate(DIR_RIGHT, 'markStrong');
            expect(inTabOrder().length).to.equal(1);
            expect(inTabOrder().is('.ProseMirror-menu-markStrong')).to.be.true;
            done();
        });
    }));

    it("test the focus menu hides when the focus leaves the toolbar for the page", () => new Promise((done) => {
        // The inline layout shows the menu while the editor has the focus
        $('#stage').addClass('focusMenu');
        initEditor();
        const $menu = $('.ProseMirror-menubar');
        expect($menu.hasClass('d-none')).to.be.true;

        // jQuery derives focusout from the native blur, so the focus has to move for real
        $('#stage .ProseMirror')[0].focus();
        expect($menu.hasClass('d-none')).to.be.false;

        // From the text into the toolbar and on to another of its buttons: the menu stays
        focusMenuItem();
        focusMenuItem('markStrong');
        setTimeout(() => {
            expect($menu.hasClass('d-none')).to.be.false;

            // From the toolbar to an element outside the editor: the menu hides once its fade is over
            $('#testContent')[0].focus();
            setTimeout(() => {
                expect($menu.hasClass('d-none')).to.be.true;
                $('#stage').removeClass('focusMenu');
                done();
            }, 200);
        }, 200);
    }));

    it("test open dropdown on ArrowDown", () => new Promise((done) => {
        if (!canAssertFocus()) {
            done();
            return;
        }

        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            navigate(DIR_DOWN);
            wait(() => {
                expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.true;
                done();
            });
        });
    }));

    it("test close dropdown on escape", () => new Promise((done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            navigate(DIR_DOWN);
            pressEscape();
            wait(() => {
                expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
                done();
            });
        });
    }));

    it("test open dropdown on enter", () => new Promise((done) => {
        if (!canAssertFocus()) {
            done();
            return;
        }

        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            pressEnter();
            wait(() => {
                expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.true;
                done();
            });
        });
    }));

    it("test close dropdown on enter", () => new Promise((done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            pressEnter();
            pressEnter();
            wait(() => {
                expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
                done();
            });
        });
    }));

    it("test navigate dropdown on ArrowDown", () => new Promise((done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            pressEnter();
            navigate(DIR_DOWN, 'makeParagraph');
            navigate(DIR_DOWN, 'makeHeading1');
            navigate(DIR_DOWN, 'makeHeading2');
            navigate(DIR_DOWN, 'makeHeading3');
            navigate(DIR_DOWN, 'makeHeading4');
            navigate(DIR_DOWN, 'makeCodeBlock');
            navigate(DIR_DOWN, 'makeParagraph');
            done();
        });
    }));

    it("test navigate dropdown on ArrowUp", () => new Promise((done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            pressEnter();
            navigate(DIR_DOWN, 'makeParagraph');
            navigate(DIR_UP, 'makeCodeBlock');
            navigate(DIR_UP, 'makeHeading4');
            navigate(DIR_UP, 'makeHeading3');
            navigate(DIR_UP, 'makeHeading2');
            navigate(DIR_UP, 'makeHeading1');
            navigate(DIR_UP, 'makeParagraph');
            done();
        });
    }));

    it("test enter sub menu on Enter", () => new Promise((done) => {
        if (!canAssertFocus()) {
            done();
            return;
        }

        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        wait(() => {
            expectFocus('type');
            pressEnter();
            navigate(DIR_DOWN, 'makeParagraph');
            navigate(DIR_DOWN, 'makeHeading1');
            navigate(DIR_DOWN, 'makeHeading2');
            navigate(DIR_DOWN, 'makeHeading3');
            navigate(DIR_DOWN, 'makeHeading4');
            navigate(DIR_DOWN, 'makeCodeBlock');
            pressEnter();
            wait(() => {
                expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line"><br></div>');
                done();
            });
        });
    }));

    // No more sub menu
    /*it("test enter sub menu on ArrowRight", (done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        expectFocus('type');
        pressEnter();
        navigate(DIR_DOWN, 'makeParagraph');
        navigate(DIR_DOWN, 'makeCodeBlock');
        navigate(DIR_DOWN, 'makeHeading');
        navigate(DIR_RIGHT, 'makeHeading1');
        done();
    });

    it("test navigate sub menu on ArrowDown", (done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        expectFocus('type');
        pressEnter();
        navigate(DIR_DOWN, 'makeParagraph');
        navigate(DIR_DOWN, 'makeCodeBlock');
        navigate(DIR_DOWN, 'makeHeading');
        navigate(DIR_RIGHT, 'makeHeading1');
        navigate(DIR_DOWN, 'makeHeading2');
        navigate(DIR_DOWN, 'makeHeading3');
        navigate(DIR_DOWN, 'makeHeading4');
        navigate(DIR_DOWN, 'makeHeading5');
        navigate(DIR_DOWN, 'makeHeading6');
        navigate(DIR_DOWN, 'makeHeading1');
        done();
    });

    it("test navigate sub menu on ArrowUp", (done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        expectFocus('type');
        pressEnter();
        navigate(DIR_DOWN, 'makeParagraph');
        navigate(DIR_DOWN, 'makeCodeBlock');
        navigate(DIR_DOWN, 'makeHeading');
        navigate(DIR_RIGHT, 'makeHeading1');
        navigate(DIR_UP, 'makeHeading6');
        navigate(DIR_UP, 'makeHeading5');
        navigate(DIR_UP, 'makeHeading4');
        navigate(DIR_UP, 'makeHeading3');
        navigate(DIR_UP, 'makeHeading2');
        navigate(DIR_UP, 'makeHeading1');
        done();
    });

    it("test leave sub menu on ArrowLeft", (done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        expectFocus('type');
        pressEnter();
        navigate(DIR_DOWN, 'makeParagraph');
        navigate(DIR_DOWN, 'makeCodeBlock');
        navigate(DIR_DOWN, 'makeHeading');
        navigate(DIR_RIGHT, 'makeHeading1');
        navigate(DIR_LEFT, 'makeHeading');
        done();
    });

    it("test leave sub menu on Escape", (done) => {
        initEditor();
        expect($('.ProseMirror-menu-dropdown-menu').is(':visible')).to.be.false;
        focusMenuItem();
        expectFocus('type');
        pressEnter();
        navigate(DIR_DOWN, 'makeParagraph');
        navigate(DIR_DOWN, 'makeCodeBlock');
        navigate(DIR_DOWN, 'makeHeading');
        navigate(DIR_RIGHT, 'makeHeading1');
        pressEscape();
        expectFocus('makeHeading');
        done();
    });*/
});
    const wait = function (callback, delay) {
        setTimeout(callback, delay || 0);
    };
    const canAssertFocus = function () {
        const ua = (typeof navigator !== 'undefined' && navigator.userAgent) ? navigator.userAgent : '';
        return !/HeadlessChrome/i.test(ua);
    };
