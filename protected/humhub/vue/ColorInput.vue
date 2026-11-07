<template>
    <div ref="root" class="input-group color-input">
        <input
            ref="swatch"
            type="color"
            class="form-control form-control-color"
            :value="swatch"
            :disabled="isDisabled"
            :aria-label="swatchLabel"
            :title="swatchLabel"
            @input="onPick"
            @change="onCommit"
        >
        <input
            ref="input"
            :id="textId"
            :name="textName"
            type="text"
            class="form-control color-input-hex"
            :class="{ 'is-invalid': isInvalid }"
            :value="text"
            :placeholder="placeholder"
            :disabled="isDisabled"
            :aria-label="textLabel"
            :aria-invalid="isInvalid ? 'true' : null"
            :aria-describedby="isDescribedBy"
            maxlength="7"
            spellcheck="false"
            autocomplete="off"
            @mousedown="onTextMousedown"
            @click="onTextClick"
            @input="onType"
            @change="onCommit"
        >
    </div>
</template>

<script>
import { log } from '@humhub/vue';

const HEX = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i;

/**
 * A hex colour as lowercase `#rrggbb` (`#ABC` → `#aabbcc`), `null` for anything else.
 */
export const normalizeHex = (value) => {
    const text = String(value ?? '').trim();
    if (!HEX.test(text)) {
        return null;
    }
    const hex = text.slice(1).toLowerCase();
    return `#${hex.length === 3 ? hex.split('').map((char) => char + char).join('') : hex}`;
};

/**
 * A colour as a hex value with the browser's own colour picker: a native `<input type="color">` as the
 * swatch and a text input for `#rrggbb`, kept in sync. A pick writes `#rrggbb` into the text, a valid
 * `#rgb`/`#rrggbb` typed moves the swatch, an invalid text leaves the swatch where it was; on `change`
 * (leaving the field, Enter) a valid value is normalized to lowercase `#rrggbb`.
 *
 * Two ways to use it:
 *
 * - **Over a native text input** (`inputId`): the PHP widget `humhub\widgets\form\ColorInput` renders
 *   the input, which keeps the `name` and carries the value, and this component mounts right after it.
 *   The native input is hidden (visually, out of the tab order and the accessibility tree) and written
 *   on every change with `input` and `change` events; the component follows the input's own `input`
 *   and `change` events (a script setting a value dispatches `input`, e.g. the theme settings' reset),
 *   its `disabled`, `is-invalid` and `aria-describedby`, and a form reset. Its `<label for>` focuses the
 *   text field. The accessible name of both fields is the label's text.
 * - **In a Vue form** (`v-model`): `ColorField` wraps it with label, hint and errors.
 *
 * A click into the text field opens the browser's picker as well (`showPicker()`, where the browser has
 * it); a click within the focused field only places the caret, and the keyboard focus opens nothing.
 *
 * @since 1.20
 */
export default {
    name: 'ColorInput',
    props: {
        // Native mode: the id of the text input to mount over
        inputId: { type: String, default: null },
        modelValue: { type: String, default: '' },
        name: { type: String, default: null },
        id: { type: String, default: null },
        label: { type: String, default: null },
        placeholder: { type: String, default: '#rrggbb' },
        disabled: { type: Boolean, default: false },
        invalid: { type: Boolean, default: false },
        describedBy: { type: String, default: null },
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            text: '',
            swatch: '#000000',
            nativeDisabled: false,
            nativeInvalid: false,
            nativeDescribedBy: null,
            nativeLabel: null,
            writing: false,
        };
    },
    computed: {
        isNative() {
            return !!this.inputId;
        },
        isDisabled() {
            return this.disabled || this.nativeDisabled;
        },
        isInvalid() {
            return this.invalid || this.nativeInvalid;
        },
        isDescribedBy() {
            return this.describedBy || this.nativeDescribedBy || null;
        },
        textId() {
            return this.isNative ? `${this.inputId}-hex` : this.id;
        },
        textName() {
            return this.isNative ? null : this.name;
        },
        textLabel() {
            return this.isNative ? this.nativeLabel : this.label;
        },
        swatchLabel() {
            return this.textLabel;
        },
    },
    watch: {
        modelValue(value) {
            if (!this.isNative) {
                this.setText(value);
            }
        },
    },
    created() {
        if (this.isNative) {
            // The native input is in the document already: its value and states for the first render
            this.initNative();
        } else {
            this.setText(normalizeHex(this.modelValue) ?? this.modelValue);
        }
    },
    mounted() {
        this.mountNative();
    },
    beforeUnmount() {
        this.unmountNative();
    },
    methods: {
        setText(value) {
            this.text = String(value ?? '');
            const hex = normalizeHex(this.text);
            if (hex) {
                this.swatch = hex;
            }
        },
        // A click into the text field opens the picker too, a click within the focused field only moves the caret;
        // focusing it with the keyboard opens nothing. `showPicker()` needs the click's activation
        onTextMousedown(event) {
            this.textWasFocused = document.activeElement === event.target;
        },
        onTextClick() {
            if (!this.textWasFocused) {
                this.openPicker();
            }
        },
        openPicker() {
            const swatch = this.$refs.swatch;
            if (!swatch || this.isDisabled || typeof swatch.showPicker !== 'function') {
                return;
            }
            try {
                swatch.showPicker();
            } catch (e) {
                // Without a user activation, or in a cross-origin frame: the swatch still opens it
            }
        },
        onType(event) {
            this.setText(event.target.value);
            this.write(false);
        },
        onPick(event) {
            this.setText(event.target.value);
            this.write(false);
        },
        onCommit() {
            const hex = normalizeHex(this.text);
            if (hex) {
                this.text = hex;
            }
            this.write(true);
        },
        // The value goes to the native input, or to the model
        write(committed) {
            if (this.isNative) {
                if (!this.native) {
                    return;
                }
                this.writing = true;
                this.native.value = this.text;
                this.native.dispatchEvent(new Event('input', { bubbles: true }));
                if (committed) {
                    this.native.dispatchEvent(new Event('change', { bubbles: true }));
                }
                this.writing = false;
                return;
            }
            this.$emit('update:modelValue', this.text);
            if (committed) {
                this.$emit('change', this.text);
            }
        },
        initNative() {
            const native = document.getElementById(this.inputId);
            if (!native) {
                log.error(`ColorInput: no <input id="${this.inputId}">`);
                return;
            }
            this.native = native;
            // Shown as lowercase `#rrggbb` where valid (a theme writes `#FFC107`); the input keeps its value
            this.setText(normalizeHex(native.value) ?? native.value);
            this.readNative();
            const label = native.id ? document.querySelector(`label[for="${native.id}"]`) : null;
            if (label) {
                this.labelEl = label;
                this.nativeLabel = label.textContent.trim();
            }
        },
        mountNative() {
            const native = this.native;
            if (!native) {
                return;
            }

            // Visually hidden, not `hidden`: scripts and tests still reach it. Out of the tab order and
            // the accessibility tree
            this.nativeAttributes = Object.fromEntries(['tabindex', 'aria-hidden'].map((name) => [name, native.getAttribute(name)]));
            native.classList.add('visually-hidden');
            native.setAttribute('tabindex', '-1');
            native.setAttribute('aria-hidden', 'true');

            this.onNativeInput = () => {
                if (!this.writing) {
                    this.setText(native.value);
                }
            };
            // jQuery's `.trigger()` reaches jQuery handlers only (and a native event both)
            if (window.jQuery) {
                window.jQuery(native).on('input.colorInput change.colorInput', this.onNativeInput);
            } else {
                native.addEventListener('input', this.onNativeInput);
                native.addEventListener('change', this.onNativeInput);
            }
            // The form resets the value after the event: read it right after
            this.onNativeReset = () => {
                this.resetTimer = setTimeout(() => this.setText(native.value), 0);
            };
            this.nativeForm = native.form;
            this.nativeForm?.addEventListener('reset', this.onNativeReset);

            this.observer = new MutationObserver(() => this.readNative());
            this.observer.observe(native, { attributes: true, attributeFilter: ['disabled', 'class', 'aria-describedby'] });

            if (this.labelEl) {
                // The label would focus the hidden input: the text field takes its place
                this.onLabelClick = (event) => {
                    event.preventDefault();
                    this.focus();
                };
                this.labelEl.addEventListener('click', this.onLabelClick);
            }
        },
        readNative() {
            const native = this.native;
            this.nativeDisabled = native.disabled;
            this.nativeInvalid = native.classList.contains('is-invalid');
            this.nativeDescribedBy = native.getAttribute('aria-describedby');
        },
        unmountNative() {
            const native = this.native;
            if (!native) {
                return;
            }
            clearTimeout(this.resetTimer);
            this.observer?.disconnect();
            if (window.jQuery) {
                window.jQuery(native).off('.colorInput');
            } else {
                native.removeEventListener('input', this.onNativeInput);
                native.removeEventListener('change', this.onNativeInput);
            }
            this.nativeForm?.removeEventListener('reset', this.onNativeReset);
            this.labelEl?.removeEventListener('click', this.onLabelClick);
            native.classList.remove('visually-hidden');
            Object.entries(this.nativeAttributes).forEach(([name, value]) => {
                if (value === null) {
                    native.removeAttribute(name);
                } else {
                    native.setAttribute(name, value);
                }
            });
            this.native = null;
        },
        focus() {
            this.$refs.input?.focus();
        },
    },
};
</script>
