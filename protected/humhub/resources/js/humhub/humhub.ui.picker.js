/**
 * The bridge between native `<select>`s and the Vue picker (see
 * docs/develop/ui-js-vuejs-picker.md, "Native bridge"): the UI addition `picker` mounts a
 * `PickerInput` island right after every `select[data-ui-picker]` (and `data-ui-select2`, read
 * as deprecated alias until 1.21); the island hides the select and keeps it in sync.
 *
 * Helpers for code that changes a picker: `getItems()`, `setItems()`, `addItem()`,
 * `removeItem()` — they change the `<select>` and fire `change`, which the island follows.
 * The action `ui.picker.selectSelf` adds the current user to its target select.
 *
 * @since 1.20 (before: the Select2 based `Picker` widget)
 */
humhub.module('ui.picker', function (module, require, $) {
    var additions = require('ui.additions');
    var config = require('config');

    var SELECTOR = 'select[data-ui-picker], select[data-ui-select2]';
    var uid = 0;

    var bind = function (select) {
        if (select.hasAttribute('data-picker-bound')) {
            return;
        }

        var vue = humhub.modules.vue;
        if (!vue || typeof vue.mountElement !== 'function') {
            // No Vue runtime: the native select stays and works.
            return;
        }

        if (!select.id) {
            select.id = 'picker-' + (++uid);
        }
        select.setAttribute('data-picker-bound', '1');

        var host = document.createElement('div');
        host.className = 'picker-host';
        host.setAttribute('data-vue-component', 'PickerInput');
        host.setAttribute('data-props', JSON.stringify({selectId: select.id}));
        select.parentNode.insertBefore(host, select.nextSibling);

        vue.mountElement(host);
    };

    // An image description read from an option (as nativeSelect.js readOption()): a JSON
    // `data-image` is parsed, `data-color` becomes a `color` image, any other string is kept.
    var optionImage = function (option) {
        var image = option.getAttribute('data-image');
        if (image === null) {
            var color = option.getAttribute('data-color');
            return color ? {type: 'color', color: color} : null;
        }
        if (image.trim().charAt(0) === '{') {
            try {
                return JSON.parse(image);
            } catch (e) {
                return image;
            }
        }
        return image;
    };

    var optionItem = function (option) {
        var text = option.getAttribute('data-text');
        var subText = option.getAttribute('data-sub-text');
        return {
            id: option.value,
            text: text !== null ? text : option.textContent.trim(),
            subText: subText !== null ? subText : null,
            image: optionImage(option),
        };
    };

    var toSelect = function (select) {
        return select && select.jquery ? select[0] : select;
    };

    var pickerConfig = function (select) {
        try {
            return JSON.parse(select.getAttribute('data-picker-config') || '{}') || {};
        } catch (e) {
            return {};
        }
    };

    // A picker holds only its chosen options (source/url, or a plain select nobody enhanced, as
    // the old `Picker` widget did); an enhanced select keeps all of them.
    var isPicker = function (select) {
        var json = pickerConfig(select);
        return select.hasAttribute('data-picker-source')
            || select.hasAttribute('data-picker-url')
            || !!(json.source || json.url)
            || (!select.hasAttribute('data-ui-picker') && !select.hasAttribute('data-ui-select2'));
    };

    var getItems = function (select) {
        select = toSelect(select);
        return Array.prototype.filter.call(select.options, function (option) {
            return option.selected && option.value !== '';
        }).map(optionItem);
    };

    var createOption = function (item) {
        var option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = item.text;
        option.setAttribute('data-text', item.text);
        if (item.subText) {
            option.setAttribute('data-sub-text', item.subText);
        }
        if (item.image) {
            option.setAttribute('data-image', typeof item.image === 'string' ? item.image : JSON.stringify(item.image));
        }
        return option;
    };

    // Makes `items` the selection, as nativeSelect.js writeSelection(): a picker holds only its
    // chosen options, in the order chosen, plus its initially selected ones (unselected, so
    // `form.reset()` can restore them); an enhanced select keeps all options. A single select
    // takes the first item; without items it selects its empty option (created as needed), so the
    // empty value is posted. Fires a bubbling `change` when the selection changed.
    var selectedValues = function (select) {
        return Array.prototype.filter.call(select.options, function (option) {
            return option.selected;
        }).map(function (option) {
            return option.value;
        }).join('\n');
    };

    var write = function (select, items) {
        var before = selectedValues(select);
        if (!select.multiple) {
            items = items.slice(0, 1);
        }
        var ids = items.map(function (item) {
            return String(item.id);
        });
        var prune = isPicker(select);

        items.forEach(function (item) {
            var option = Array.prototype.find.call(select.options, function (entry) {
                return entry.value === String(item.id);
            }) || createOption(item);
            if (prune || !option.parentNode) {
                select.appendChild(option);
            }
        });

        Array.prototype.slice.call(select.options).forEach(function (option) {
            var chosen = ids.indexOf(option.value) !== -1;
            if (!chosen && prune && !option.defaultSelected && option.value !== '') {
                option.remove();
                return;
            }
            option.selected = chosen;
        });

        if (!select.multiple && ids.length === 0) {
            var empty = Array.prototype.find.call(select.options, function (option) {
                return option.value === '';
            });
            if (!empty) {
                empty = document.createElement('option');
                empty.value = '';
                select.insertBefore(empty, select.firstChild);
            }
            empty.selected = true;
        }

        if (selectedValues(select) !== before) {
            select.dispatchEvent(new Event('change', {bubbles: true}));
        }
    };

    var setItems = function (select, items) {
        write(toSelect(select), items);
    };

    var addItem = function (select, item) {
        select = toSelect(select);
        var items = getItems(select);
        if (!select.multiple) {
            // A single select holds one item: the added one replaces it.
            items = [item];
        } else if (!items.some(function (entry) {
            return entry.id === String(item.id);
        })) {
            items.push(item);
        }
        write(select, items);
    };

    var removeItem = function (select, id) {
        select = toSelect(select);
        write(select, getItems(select).filter(function (item) {
            return item.id !== String(id);
        }));
    };

    var selectSelf = function (evt) {
        var user = config.get('user') || {};
        if (user.isGuest || !user.guid) {
            return;
        }
        addItem(evt.$target, {id: user.guid, text: user.text, image: user.image});
    };

    // The Tabler icon provider id (`TablerIconProvider::ID`).
    var TABLER = 'ti';

    // The names of an icon library, handed over by `IconPicker` as `iconNames.<lib>` of this
    // module's config: a comma-separated string (or a list). Read on every search, as a picker
    // rendered later (pjax, modal) may bring them after init.
    var iconNames = function (lib) {
        var names = ((config.get('ui.picker') || {}).iconNames || {})[lib];
        if (typeof names === 'string') {
            return names ? names.split(',') : [];
        }
        return Array.isArray(names) ? names : [];
    };

    // The core picker source `icon` (`IconPicker`): the names of `params.lib` (default Tabler)
    // containing the term, case-insensitive.
    var iconSource = function (options) {
        var lib = (options && options.params && options.params.lib) || TABLER;
        return {
            search: function (term) {
                var needle = String(term || '').toLowerCase();
                return Promise.resolve(iconNames(lib).filter(function (name) {
                    return name.toLowerCase().indexOf(needle) !== -1;
                }).map(function (name) {
                    return {id: name, text: name, image: lib === TABLER ? {type: 'icon', name: name} : null};
                }));
            },
        };
    };

    var init = function () {
        additions.register('picker', SELECTOR, function ($match) {
            $match.each(function () {
                bind(this);
            });
        });

        var vue = humhub.modules.vue;
        if (vue && typeof vue.registerPickerSource === 'function') {
            vue.registerPickerSource('icon', iconSource);
        }
    };

    module.export({
        init: init,
        bind: bind,
        getItems: getItems,
        setItems: setItems,
        addItem: addItem,
        removeItem: removeItem,
        selectSelf: selectSelf,
        iconSource: iconSource,
    });
});
