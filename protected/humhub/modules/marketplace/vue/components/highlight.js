/**
 * Search highlighting for the module cards: the parts of a text matching the search query,
 * accent- and case-insensitively ("Umfrage" matches "umfräge", "é" matches "e"). The result is
 * plain data — `[{ text, mark }]` — rendered as text nodes (inside `<mark>` for a match), so
 * neither the text nor the query is ever interpreted as markup.
 *
 * @since 1.20
 */

const COMBINING = /[̀-ͯ]/g;

const fold = (value) => value.normalize('NFD').replace(COMBINING, '').toLowerCase();

// The folded text and, per folded character, the index of the source character it came from —
// folding can change the length ("é" is one character, "ß" stays, "İ" becomes "i").
const foldWithMap = (text) => {
    let folded = '';
    const source = [];
    for (let i = 0; i < text.length; i++) {
        const part = fold(text[i]);
        folded += part;
        for (let j = 0; j < part.length; j++) {
            source.push(i);
        }
    }
    return { folded, source };
};

export const highlightParts = (text, query) => {
    const value = String(text ?? '');
    const needle = fold(String(query ?? '').trim());
    if (needle === '' || value === '') {
        return value === '' ? [] : [{ text: value, mark: false }];
    }

    const { folded, source } = foldWithMap(value);
    const parts = [];
    let cursor = 0;
    let from = folded.indexOf(needle);
    while (from !== -1) {
        const start = source[from];
        let end = source[from + needle.length - 1] + 1;
        // Combining marks after the match fold to nothing: they belong to its last character.
        while (end < value.length && fold(value[end]) === '') {
            end++;
        }
        if (start >= cursor) {
            if (start > cursor) {
                parts.push({ text: value.slice(cursor, start), mark: false });
            }
            parts.push({ text: value.slice(start, end), mark: true });
            cursor = end;
        }
        from = folded.indexOf(needle, from + needle.length);
    }
    if (cursor < value.length) {
        parts.push({ text: value.slice(cursor), mark: false });
    }
    return parts;
};
