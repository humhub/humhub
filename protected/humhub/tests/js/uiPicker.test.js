import { afterEach, beforeAll, describe, expect, it } from 'vitest';

// ui.picker runs against the real select2 build (the one Select2Asset ships), so the
// filtering is tested through select2's own data adapters and result rendering.

const $ = globalThis.jQuery;

// Bootstrap/jquery.highlight plugins the result template relies on.
$.fn.tooltip = $.fn.tooltip || function () { return this; };
$.fn.highlight = $.fn.highlight || function () { return this; };

// Minimal `ui.widget`: the real one needs the action/event/client core modules this
// harness does not provide, while the picker only uses the instance bookkeeping.
globalThis.humhub.module('ui.widget', function (module) {
    const Widget = function (node, options) {
        this.$ = $(node);
        this.$.data('humhub-widget', this);
        this.options = $.extend(this.getDefaultOptions(), options || {});
        this.init();
    };
    Widget.prototype.getDefaultOptions = () => ({});
    Widget.prototype.init = () => {};
    Widget.prototype.fire = () => {};
    Widget.instance = (node) => $(node).data('humhub-widget');

    module.export({ Widget });
});
globalThis.humhub.module('ui.loader', function (module) {
    module.export({ set: ($node) => $node });
});

// The UMD build takes its CommonJS branch here and only registers itself once called.
const select2 = await import('../../../vendor/npm-asset/select2/dist/js/select2.full.js');
if (!$.fn.select2) {
    (select2.default || select2)(window, $);
}
await import('../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub/humhub.ui.picker.js');

const { Picker } = globalThis.humhub.modules['ui.picker'];

let picker;

const tick = () => new Promise((resolve) => setTimeout(resolve, 10));

const createPicker = async (html, options) => {
    const $select = $(html).appendTo(document.body);
    picker = new Picker($select[0], options);
    // select2 is initialised from an (asynchronous) almond require() callback
    await tick();
    return picker;
};

const offered = () => $('.select2-results__option[aria-selected]').map(function () {
    return $(this).find('.picker-text').text();
}).get();

const open = async () => {
    picker.$.select2('open');
    // ajax results arrive asynchronously
    await tick();
};

beforeAll(() => {
    // select2 positions its dropdown against the document, jsdom has no layout
    window.scrollTo = () => {};
});

afterEach(async () => {
    // let the delayed renderPlaceholder() (50ms) run before tearing select2 down
    await new Promise((resolve) => setTimeout(resolve, 60));
    picker.$.select2('destroy');
    $(document.body).empty();
});

describe('ui.picker', () => {
    it('does not offer already selected items of a multi-select', async () => {
        await createPicker(`<select multiple data-multiple="1">
            <option value="read" selected>Read</option>
            <option value="write">Write</option>
            <option value="admin" selected>Admin</option>
        </select>`);

        await open();

        expect(offered()).toEqual(['Write']);
    });

    it('shows the no results message once everything is selected', async () => {
        await createPicker(`<select multiple data-multiple="1" data-no-result="Nothing left">
            <option value="read" selected>Read</option>
        </select>`);

        await open();

        expect(offered()).toEqual([]);
        expect($('.select2-results__message').text()).toBe('Nothing left');
    });

    it('offers an item again after it was removed from the selection', async () => {
        await createPicker(`<select multiple data-multiple="1">
            <option value="read" selected>Read</option>
            <option value="write">Write</option>
        </select>`);

        picker.$.val([]).trigger('change');
        await open();

        expect(offered()).toEqual(['Read', 'Write']);
    });

    it('does not offer already selected items of ajax results', async () => {
        await createPicker(`<select multiple data-multiple="1">
            <option value="u2" selected>Bob</option>
        </select>`, {
            pickerUrl: '/user/search',
            ajax: {
                delay: 0,
                transport: (params, success) => success([
                    { id: 'u1', text: 'Alice' },
                    { id: 'u2', text: 'Bob' },
                    { id: 'u3', text: 'Carol' },
                ]),
            },
        });

        await open();

        expect(offered()).toEqual(['Alice', 'Carol']);
    });

    it('compares ajax results by the configured item key', async () => {
        await createPicker(`<select multiple data-multiple="1" data-item-key="guid">
            <option value="guid-2" selected>Bob</option>
        </select>`, {
            pickerUrl: '/user/search',
            ajax: {
                delay: 0,
                transport: (params, success) => success([
                    { id: 1, guid: 'guid-1', text: 'Alice' },
                    { id: 2, guid: 'guid-2', text: 'Bob' },
                ]),
            },
        });

        await open();

        expect(offered()).toEqual(['Alice']);
    });

    it('takes an item off the open dropdown once it got selected', async () => {
        await createPicker(`<select multiple data-multiple="1">
            <option value="read">Read</option>
            <option value="write">Write</option>
        </select>`);

        await open();
        // ctrl + click keeps the dropdown open
        const $read = $('.select2-results__option[aria-selected]').filter(function () {
            return $(this).find('.picker-text').text() === 'Read';
        });
        $read.trigger($.Event('mouseup', { ctrlKey: true }));

        expect(picker.$.val()).toEqual(['read']);
        expect(picker.$.data('select2').isOpen()).toBe(true);
        expect(offered()).toEqual(['Write']);
    });

    it('keeps offering the selected item of a single-select', async () => {
        await createPicker(`<select>
            <option value="read" selected>Read</option>
            <option value="write">Write</option>
        </select>`);

        await open();

        expect(offered()).toEqual(['Read', 'Write']);
    });
});
