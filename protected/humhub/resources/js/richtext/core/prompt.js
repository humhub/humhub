const prefix = "ProseMirror-prompt";

let count = 0;

/**
 * A modal dialog (WCAG 4.1.2, 2.4.3): named by its title, its fields by their labels; the focus stays in it, Escape
 * closes it and the focus returns to where it came from. `options.translate` translates the labels and the buttons.
 */
class Promt {
    constructor(options) {
        this.options = options;
        this.id = prefix + '-' + (++count);
        this.returnFocus = document.activeElement;
        this.render();
    }

    translate(text) {
        return this.options.translate ? this.options.translate(text) : text;
    }

    render() {
        $('.ProseMirror-prompt').remove();
        this.$wrapper = $('<div>').addClass(prefix).attr({role: 'dialog', 'aria-modal': 'true'}).appendTo($('body'));
        this.buildForm();
        this.initEvents();
        this.initWrapper();
    }

    initWrapper() {
        let box = this.$wrapper[0].getBoundingClientRect();
        this.$wrapper.css({
            top: ((window.innerHeight - box.height) / 2) + "px",
            left: ((window.innerWidth - box.width) / 2) + "px"
        });

        this.$wrapper.find('select:visible, input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first().focus();
    }

    buildForm() {
        this.$form = $('<form>').appendTo(this.$wrapper);

        if (this.options.title) {
            this.$form.append($('<h5>').attr('id', this.id + '-title').html(this.options.title));
            this.$wrapper.attr('aria-labelledby', this.id + '-title');
        }

        this.buildFormFields();

        this.domFields.forEach(field => {
            this.$form.append(field);
        });

        this.$form.on('submit', (e) => {
            e.preventDefault();
            this.submit();
        });

        this.buildButtons();
    }

    buildFormFields() {
        this.domFields = [];
        for (let name in this.options.fields) {
            let field = this.options.fields[name];
            let input = field.render();
            input.id = input.id || this.id + '-' + name;
            let $label = $('<label>').attr('for', input.id).html(this.translate(field.options.label || name) + ':');
            let $field = $('<div>').append($label).append('<br>').append(input);
            this.domFields.push($field[0]);
        }
    }

    buildButtons() {
        this.$buttons = $('<div>').addClass(prefix + "-buttons");
        $('<button type="submit" class="btn btn-primary">').addClass(prefix + "-submit").text(this.translate('OK')).appendTo(this.$buttons);

        this.$buttons.append(document.createTextNode(' '));

        $('<button type="button" class="btn btn-secondary">').addClass(prefix + "-cancel")
            .text(this.translate('Cancel')).appendTo(this.$buttons)
            .on('click', () => this.close());

        this.$form.append(this.$buttons);
    }

    close() {
        this.$wrapper.remove();

        if (this.returnFocus && document.body.contains(this.returnFocus)) {
            this.returnFocus.focus();
        }
    }

    // Tab and Shift+Tab go round the fields and buttons of the dialog
    trapFocus(e) {
        const focusable = this.$wrapper.find('input, select, textarea, button, [contenteditable="true"]')
            .not('[disabled], [type="hidden"]').toArray();
        if (!focusable.length) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    submit() {
        let params = this.getValues();
        if (params) {
            this.close();
            this.options.callback(params);
        }
    }

    getValues() {
        let result = Object.create(null);
        let i = 0;

        for (let name in this.options.fields) {
            let field = this.options.fields[name];
            let dom = this.domFields[i++];

            let value = field.read(dom);
            let bad = field.validate(value);

            if (bad) {
                this.reportInvalid(dom, bad);
                return null;
            }
            result[name] = field.clean(value);
        }

        return result;
    }

    reportInvalid(dom, message) {
        // FIXME this is awful and needs a lot more work
        let parent = dom.parentNode;
        let msg = parent.appendChild(document.createElement("div"));
        msg.style.left = (dom.offsetLeft + dom.offsetWidth + 2) + "px";
        msg.style.top = (dom.offsetTop - 5) + "px";
        msg.className = "ProseMirror-invalid";
        msg.setAttribute('role', 'alert');
        msg.textContent = message;
        setTimeout(() => parent.removeChild(msg), 1500);
    }

    initEvents() {
        this.$form.on("keydown", (e) => {
            if (e.keyCode === 27) {
                // Only this dialog, not a modal it is opened in
                e.preventDefault();
                e.stopPropagation();
                this.close();
            } else if (e.keyCode === 13 && !(e.ctrlKey || e.metaKey || e.shiftKey)) {
                e.preventDefault();
                this.submit();
            } else if (e.keyCode === 9) {
                this.trapFocus(e);
            }
        }).on('mousedown', (e) => {
            if (!$.contains(this.$wrapper[0], e.target)) {
                this.close();
            }
        })
    }
}

export function openPrompt(options) {
    return new Promt(options);
}

// ::- The type of field that `FieldPrompt` expects to be passed to it.
export class Field {
    // :: (Object)
    // Create a field with the given options. Options support by all
    // field types are:
    //
    // **`value`**`: ?any`
    //   : The starting value for the field.
    //
    // **`label`**`: string`
    //   : The label for the field.
    //
    // **`required`**`: ?bool`
    //   : Whether the field is required.
    //
    // **`validate`**`: ?(any) → ?string`
    //   : A function to validate the given value. Should return an
    //     error message if it is not valid.
    constructor(options) {
        this.options = options
    }

    // render:: (state: EditorState, props: Object) → dom.Node
    // Render the field to the DOM. Should be implemented by all subclasses.

    // :: (dom.Node) → any
    // Read the field's value from its DOM node.
    read(dom) {
        if (dom.value) {
            return dom.value;
        } else {
            return $(dom).find('input, select')[0].value;
        }
    }

    // :: (any) → ?string
    // A field-type-specific validation function.
    validateType(_value) {
    }

    validate(value) {
        if (!value && this.options.required)
            return "Required field";
        return this.validateType(value) || (this.options.validate && this.options.validate(value));
    }

    clean(value) {
        return this.options.clean ? this.options.clean(value) : value;
    }
}

// ::- A field class for single-line text fields.
export class TextField extends Field {
    render() {
        let input = document.createElement("input");
        input.type = "text";
        input.className = 'form-control';
        input.value = this.options.value || "";
        input.autocomplete = "off";
        return input;
    }
}


// ::- A field class for dropdown fields based on a plain `<select>`
// tag. Expects an option `options`, which should be an array of
// `{value: string, label: string}` objects, or a function taking a
// `ProseMirror` instance and returning such an array.
export class SelectField extends Field {
    render() {
        let select = document.createElement("select");
        select.className = 'form-control';

        this.options.options.forEach((o) => {
            let opt = select.appendChild(document.createElement("option"));
            opt.value = o.value;
            opt.selected = o.value === this.options.value;
            opt.label = o.label;
        });
        return select;
    }
}

// ::- A field class for checkbox fields.
export class CheckboxField extends Field {
    render() {
        let input = document.createElement("input");
        input.type = "checkbox";
        input.className = "form-check-input";
        input.checked = !!this.options.value;
        return input;
    }

    read(dom) {
        if (typeof dom.checked !== "undefined") {
            return !!dom.checked;
        }

        let input = $(dom).find('input[type="checkbox"]')[0];
        return !!(input && input.checked);
    }

    clean(value) {
        return !!value;
    }
}
