/**
 * The subtitle tracks among the variants of a file (`GET /api/v2/file/{id}/variants`). Free of imports, so the bundle of
 * any module can include it. A variant is a track by its name only: `subtitles-<language>.vtt` or
 * `captions-<language>.vtt`.
 *
 * @since 1.20
 */

const NAME = /^(subtitles|captions)-([a-z]{2,3}(?:-[A-Za-z0-9]{2,8})*)\.vtt$/;

/**
 * @param {string} name the name of a variant
 * @returns {?{kind: string, language: string}} `kind` is the `<track kind>`, `language` the `srclang`; null if the name is no track
 */
export function parseSubtitleName(name) {
    const match = NAME.exec(String(name || ''));

    return match ? { kind: match[1], language: match[2] } : null;
}

/**
 * @param {Array} variants the `results` of the variants API
 * @returns {Array<{name: string, url: string, kind: string, language: string}>} the tracks with a URL, in the given order
 */
export function subtitleTracks(variants) {
    return (Array.isArray(variants) ? variants : []).flatMap((variant) => {
        const parsed = variant && variant.url ? parseSubtitleName(variant.name) : null;

        return parsed ? [{ ...variant, ...parsed }] : [];
    });
}

/**
 * @param {string} language a language code of a track
 * @param {string} [locale] the language of the name, default the page's
 * @returns {string} the name of the language, first letter upper case; the code if it has none
 */
export function languageLabel(language, locale = document.documentElement.lang) {
    try {
        const name = new Intl.DisplayNames([locale || 'en'], { type: 'language' }).of(language);

        return name ? name.charAt(0).toLocaleUpperCase(locale || 'en') + name.slice(1) : language;
    } catch (e) {
        return language;
    }
}
