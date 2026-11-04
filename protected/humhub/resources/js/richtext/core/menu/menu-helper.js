import {getIcon} from "./icons";
import crelt from "crelt";

export const PREFIX = "ProseMirror-menu";

// Work around classList.toggle being broken in IE11
export function setClass(dom, cls, on) {
    if (on) dom.classList.add(cls);
    else dom.classList.remove(cls);
}

export function setAttribute(dom, attr, value, on) {
    if (on) dom.setAttribute(attr, value);
    else dom.removeAttribute(attr);
}

export function translate(view, text) {
    return view._props.translate ? view._props.translate(text) : text;
}

export function addClassId(dom, options) {
    if (options.id) {
        addMenuClass(dom, options);
    }
}

export function initMenuItemTrigger(view, options) {
    let trigger = null;
    if (options.icon) {
        trigger = getIcon(options.icon, options.htmlNode)
    } else if (options.label) {
        trigger = document.createElement(options.htmlNode);
        trigger.innerHTML = translate(view, options.label).replace('</i>', '</i> ');
    } else {
        return null;
    }

    trigger.classList.add(buildMenuClass('trigger'));

    if (trigger) {
        setAttributesFromOptions(trigger, options);
    }

    return trigger;
}

export function setTabindex(dom, options) {
    if (typeof options.tabindex !== 'undefined') {
        dom.setAttribute('tabindex', options.tabindex);
    }
}

export function setTitle(dom, options, view) {
    if (options.title !== 'undefined') {
        const title = (typeof options.title === "function" ? options.title(view.state) : options.title);
        dom.setAttribute("title", translate(view, title));
    }
}

export function setAttributesFromOptions(dom, options) {
    if (options.class) dom.classList.add(options.class);
    if (options.css) dom.style.cssText += options.css;
    if (options.seperator) {
        dom.classList.add('seperator');
    }
    addClassId(dom, options);
}

export function buildMenuClass(suffix) {
    if (typeof suffix === 'object') {
        suffix = suffix.id;
    }

    return PREFIX + '-' + suffix;
}

export function addMenuClass(dom, suffix) {
    dom.classList.add(buildMenuClass(suffix));
}

export function randomId() {
    return '_' + Math.random().toString(36).substr(2, 9);
}

let lastMenuEvent = {time: 0, node: null};

export function markMenuEvent(e) {
    lastMenuEvent.time = Date.now();
    lastMenuEvent.node = e.target;
}

export function isMenuEvent(wrapper) {
    return Date.now() - 100 < lastMenuEvent.time &&
        lastMenuEvent.node && wrapper.contains(lastMenuEvent.node);
}
