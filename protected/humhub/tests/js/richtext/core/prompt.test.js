import { describe, it, expect, afterEach } from 'vitest';

import '../testEditor.js';
import {openPrompt, TextField, CheckboxField} from '../../../../resources/js/richtext/core/prompt';

describe("Core:prompt", () => {
    const translate = (text) => ({Text: 'Linktext', OK: 'Einfügen', Cancel: 'Abbrechen'}[text] || text);
    const keydown = (prompt, keyCode, shiftKey = false) => prompt.$form.trigger($.Event('keydown', {keyCode, shiftKey}));

    const open = () => openPrompt({
        title: 'Create a link',
        translate,
        fields: {
            text: new TextField({label: 'Text'}),
            newTab: new CheckboxField({label: 'New tab'}),
        },
        callback() {},
    });

    afterEach(() => {
        $('.ProseMirror-prompt, #opener').remove();
    });

    it("is a modal dialog named by its title", () => {
        const prompt = open();
        const $dialog = $('.ProseMirror-prompt');

        expect($dialog.attr('role')).toBe('dialog');
        expect($dialog.attr('aria-modal')).toBe('true');
        expect($('#' + $dialog.attr('aria-labelledby')).text()).toBe('Create a link');
        prompt.close();
    });

    it("labels its fields and translates the labels and buttons", () => {
        const prompt = open();

        const $text = prompt.$wrapper.find('input[type="text"]');
        const $checkbox = prompt.$wrapper.find('input[type="checkbox"]');
        expect(prompt.$wrapper.find('label[for="' + $text.attr('id') + '"]').text()).toBe('Linktext:');
        expect(prompt.$wrapper.find('label[for="' + $checkbox.attr('id') + '"]').text()).toBe('New tab:');
        expect(prompt.$wrapper.find('.ProseMirror-prompt-submit').text()).toBe('Einfügen');
        expect(prompt.$wrapper.find('.ProseMirror-prompt-cancel').text()).toBe('Abbrechen');
        prompt.close();
    });

    it("keeps the focus inside on Tab and Shift+Tab", () => {
        const prompt = open();
        const $text = prompt.$wrapper.find('input[type="text"]');
        const $cancel = prompt.$wrapper.find('.ProseMirror-prompt-cancel');

        $cancel[0].focus();
        keydown(prompt, 9);
        expect(document.activeElement).toBe($text[0]);

        keydown(prompt, 9, true);
        expect(document.activeElement).toBe($cancel[0]);
        prompt.close();
    });

    it("closes on Escape and gives the focus back", () => {
        const $opener = $('<button id="opener">').appendTo('body');
        $opener[0].focus();

        const prompt = open();
        prompt.$wrapper.find('input[type="text"]')[0].focus();
        keydown(prompt, 27);

        expect($('.ProseMirror-prompt').length).toBe(0);
        expect(document.activeElement).toBe($opener[0]);
    });

    it("marks a required field, and an empty one as invalid with its error until it is changed", () => {
        const prompt = openPrompt({
            title: 'Create a link',
            translate: (text) => ({'Required field': 'Pflichtfeld'}[text] || text),
            fields: {href: new TextField({label: 'Link', required: true})},
            callback() {},
        });
        const $input = prompt.$wrapper.find('input[type="text"]');
        expect($input.attr('aria-required')).toBe('true');

        prompt.submit();

        // Still open, the field invalid and described by the error, which stays
        expect($('.ProseMirror-prompt').length).toBe(1);
        expect($input.attr('aria-invalid')).toBe('true');
        const $error = $('#' + $input.attr('aria-describedby'));
        expect($error.text()).toBe('Pflichtfeld');
        expect($error.attr('role')).toBe('alert');
        expect(document.activeElement).toBe($input[0]);

        $input.val('https://humhub.com').trigger('input');
        expect($input.attr('aria-invalid')).toBeUndefined();
        expect($input.attr('aria-describedby')).toBeUndefined();
        expect($error[0].isConnected).toBe(false);
        prompt.close();
    });
});
