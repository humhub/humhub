/**
 * The legacy widget base (humhub.ui.widget.js) as far as the jQuery widgets under test use it: options from the data
 * attributes, the widget's own event bus, `fire()` (bus and DOM), `Widget.instance()` and `Widget.extend()`. The real
 * one needs the action framework, which the test setup has no stand-in for.
 *
 * A test registers it before it imports the widgets: `globalThis.humhub.modules['ui.widget'] = { Widget }`.
 */
const jQuery = globalThis.jQuery;

export function Widget(node, options) {
    this.$ = node instanceof jQuery ? node : jQuery(node);
    this.errors = [];
    const events = jQuery({});
    this.on = (...args) => {
        events.on(...args);
        return this;
    };
    this.off = (...args) => {
        events.off(...args);
        return this;
    };
    this.trigger = (type, args) => {
        events.trigger(type, args);
        return this;
    };
    this.options = this.initOptions(options || {});
    this.init(this.$.data('ui-init'));
}

Widget.prototype.initOptions = function (options) {
    const data = {};
    jQuery.each(this.$.data(), (key, value) => {
        data[key] = value === '' ? true : value;
    });
    return jQuery.extend(this.getDefaultOptions(options), data, options);
};
Widget.prototype.getDefaultOptions = () => ({});
Widget.prototype.init = () => {};
Widget.prototype.fire = function (event, args) {
    this.trigger(event, args);
    this.$.trigger(event, args);
};
Widget.prototype.show = function () {
    this.$.show();
    return this;
};
Widget.prototype.hide = function () {
    this.$.hide();
    return this;
};
Widget.prototype.statusError = function () {
    this.errors = [];
};

// A subclass, as `Widget.extend()` of the action framework's components
Widget.extend = function (init) {
    const Parent = this;
    const Sub = function (...args) {
        (init || Parent).apply(this, args);
    };
    Sub.prototype = Object.create(Parent.prototype);
    Sub.prototype.constructor = Sub;
    Sub.extend = Parent.extend;
    return Sub;
};

Widget.instance = (node) => {
    const $node = jQuery(node);
    if (!$node.length) {
        return null;
    }
    let instance = $node.data('humhub-widget');
    if (!instance) {
        const ns = $node.data('ui-widget');
        const dot = ns.lastIndexOf('.');
        const WidgetClass = globalThis.humhub.modules[ns.slice(0, dot)][ns.slice(dot + 1)];
        instance = new WidgetClass($node);
        $node.data('humhub-widget', instance);
    }
    return instance;
};
