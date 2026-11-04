import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, pressKeyEnter, pressKeyBackspace} from '../../testEditor.js';

describe("Plugin:max_height", () => {
    // jsdom lays nothing out: lay the editor's blocks out at 36px each, its height capped by
    // its `max-height` (the plugin compares `scrollHeight` with `offsetHeight`)
    const BLOCK_HEIGHT = 36;
    const contentHeight = (element) => element.childElementCount * BLOCK_HEIGHT;

    beforeEach(() => {
        vi.spyOn(HTMLElement.prototype, 'scrollHeight', 'get').mockImplementation(function () {
            return contentHeight(this);
        });
        vi.spyOn(HTMLElement.prototype, 'offsetHeight', 'get').mockImplementation(function () {
            const maxHeight = parseFloat(this.style.maxHeight);
            return isNaN(maxHeight) ? contentHeight(this) : Math.min(contentHeight(this), maxHeight);
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it("test maxHeight trigger", () => {
        let scroll = {active: false};
        initEditor({
            maxHeight: 200,
            onScrollActive: () => scroll.active = true,
            onScrollInactive: () => scroll.active = false,
        });

        pressKeyEnter();
        expect(scroll.active).to.be.false;
        pressKeyEnter();
        expect(scroll.active).to.be.false;
        pressKeyEnter();
        expect(scroll.active).to.be.false;
        pressKeyEnter();
        expect(scroll.active).to.be.false;
        pressKeyEnter();
        expect(scroll.active).to.be.true;
        pressKeyBackspace();
        expect(scroll.active).to.be.false;
    });

    it("test init with maxHeight overflow", () => {
        let scroll = {active: false};
        initEditor({
            maxHeight:100,
            onScrollActive: () => scroll.active = true,
            onScrollInactive: () => scroll.active = false,
        }, 'Test\n\nTest\n\nTest\n\nTest\n\n');

        expect(scroll.active).to.be.true;
    });
});
