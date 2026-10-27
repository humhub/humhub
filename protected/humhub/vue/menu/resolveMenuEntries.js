import { getMenuEntries, isRegistered } from '@humhub/vue';

/**
 * The entries a `DropdownMenu` shows: its built-in `entries`, overridden in place by registry
 * entries of the same id (`registerMenuEntry()`), followed by the registry's own entries, without
 * removed ones (`removeMenuEntry()`), those whose `condition(context)` fails and those of an
 * unregistered component, stably sorted by `sortOrder` (default 1000; built-ins before registry
 * entries on ties). Reactive when read inside a computed or render, so a host (e.g. the data
 * table deciding whether a row has a menu) sees the same entries as the menu.
 *
 * @since 1.20
 */
export function resolveMenuEntries(menuId, entries = [], context = {}) {
    if (!menuId) {
        return [];
    }

    const registry = getMenuEntries(menuId);
    const registryById = new Map(registry.entries.map((entry) => [entry.id, entry]));

    const usedRegistryIds = new Set();
    const merged = entries.map((entry) => {
        const override = registryById.get(entry.id);
        if (override) {
            usedRegistryIds.add(entry.id);
            return override;
        }
        return entry;
    });
    registry.entries.forEach((entry) => {
        if (!usedRegistryIds.has(entry.id)) {
            merged.push(entry);
        }
    });

    const removed = registry.removed;
    const sortOrderOf = (entry) => (typeof entry.sortOrder === 'number' ? entry.sortOrder : 1000);

    // A stable sort keyed on the pre-sort position, so ties keep the merge order whatever
    // the engine's Array#sort does with equal keys.
    return merged
        .filter((entry) => removed.indexOf(entry.id) === -1
            && (!entry.condition || entry.condition(context))
            && (!entry.component || isRegistered(entry.component)))
        .map((entry, index) => ({ entry, index }))
        .sort((a, b) => (sortOrderOf(a.entry) - sortOrderOf(b.entry)) || (a.index - b.index))
        .map((wrapped) => wrapped.entry);
}
