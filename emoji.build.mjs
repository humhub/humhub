/**
 * Builds the emoji assets into committed artifacts (see docs/develop/internals-emoji.md):
 *
 * - the Noto Color Emoji font (COLRv1) as WOFF2 chunks per emoji group plus their `emoji.css`
 *   in protected/humhub/resources/fonts/emoji/, registered by `humhub\assets\EmojiFontAsset`
 * - the data of the emoji picker per language in protected/humhub/resources/emoji/data/,
 *   generated from `emojibase-data`
 *
 *   node emoji.build.mjs
 *
 * The font is downloaded once from the pinned Noto release into node_modules/.cache/. The output is
 * byte-reproducible: running the build again without changing the sources changes no file.
 */
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

// fontTools (Pyodide): harfbuzzjs (subset-font) is built without COLR support
const { subset } = createRequire(import.meta.url)('@web-alchemy/fonttools');

const NOTO_RELEASE = 'v2026-09-24-unicode18_0';
const NOTO_URL = `https://raw.githubusercontent.com/googlefonts/noto-emoji/${NOTO_RELEASE}/2D/fonts/Noto-COLRv1.ttf`;
const NOTO_LICENSE_URL = `https://raw.githubusercontent.com/googlefonts/noto-emoji/${NOTO_RELEASE}/2D/fonts/LICENSE`;
const NOTO_SHA256 = 'b8e25ea68db82f9e4d0aee921f4420be2be39887bd5c893a2ad98710531f9d0c';

const FONT_FAMILY = 'HumHub Emoji';

// Emojis a touch larger than the text around them, as the Twemoji images were (16px at 14px). The picker compensates
// for it (NOTO_SIZE_ADJUST in plugins/emoji/data.js)
const SIZE_ADJUST = '115%';

const root = dirname(fileURLToPath(import.meta.url));
const emojibase = resolve(root, 'node_modules/emojibase-data');
const cacheDir = resolve(root, 'node_modules/.cache/humhub-emoji');
const fontDir = resolve(root, 'protected/humhub/resources/fonts/emoji');
const dataDir = resolve(root, 'protected/humhub/resources/emoji/data');

const readJson = (path) => JSON.parse(readFileSync(path, 'utf8'));

async function download(url, file, sha256 = null) {
    if (!existsSync(file)) {
        console.log(`Downloading ${url}`);
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`Download failed: ${url} (${response.status})`);
        }
        mkdirSync(dirname(file), { recursive: true });
        writeFileSync(file, Buffer.from(await response.arrayBuffer()));
    }

    const content = readFileSync(file);
    if (sha256 && createHash('sha256').update(content).digest('hex') !== sha256) {
        rmSync(file);
        throw new Error(`Checksum mismatch: ${url}`);
    }

    return content;
}

/**
 * @returns {string} the CSS unicode-range of the code points, e.g. `U+200d, U+1f600-1f64f`
 */
function unicodeRange(codePoints) {
    const sorted = [...codePoints].sort((a, b) => a - b);
    const ranges = [];
    for (const cp of sorted) {
        const last = ranges[ranges.length - 1];
        if (last && cp === last[1] + 1) {
            last[1] = cp;
        } else {
            ranges.push([cp, cp]);
        }
    }

    return ranges
        .map(([from, to]) => 'U+' + from.toString(16) + (from === to ? '' : '-' + to.toString(16)))
        .join(', ');
}

/**
 * One chunk per emoji group, each with every code point of its emojis (skin tones and ZWJ sequences included):
 * a sequence is always complete in one chunk, so the browser can shape it with one font file.
 *
 * Digits, `#`, `*`, `©` and `®` are part of the symbols chunk for the keycaps (`1️⃣`): without them Chrome renders
 * a keycap with the text font. Plain text keeps the text font anyway, as it comes first in the font stack.
 */
function buildChunks() {
    const groups = readJson(resolve(emojibase, 'meta/groups.json')).groups;
    const chunks = new Map();

    for (const emoji of readJson(resolve(emojibase, 'en/data.json'))) {
        // Components (skin tones, hair) are part of the people's sequences
        const group = emoji.group === 2 ? 1 : emoji.group;
        if (group === undefined) {
            continue;
        }

        if (!chunks.has(group)) {
            chunks.set(group, { name: groups[group], codePoints: new Set() });
        }
        const chunk = chunks.get(group);

        for (const sequence of [emoji.emoji, ...(emoji.skins || []).map((skin) => skin.emoji)]) {
            for (const char of sequence) {
                chunk.codePoints.add(char.codePointAt(0));
            }
        }
    }

    return [...chunks.entries()].sort(([a], [b]) => a - b).map(([, chunk]) => chunk);
}

async function buildFont() {
    const font = await download(NOTO_URL, resolve(cacheDir, `Noto-COLRv1-${NOTO_RELEASE}.ttf`), NOTO_SHA256);
    const license = await download(NOTO_LICENSE_URL, resolve(cacheDir, `LICENSE-${NOTO_RELEASE}`));

    rmSync(fontDir, { recursive: true, force: true });
    mkdirSync(fontDir, { recursive: true });

    // When ranges overlap (U+200D, U+FE0F, ...), the face declared last is tried first: smileys and people last,
    // and the symbols after them, as a browser looks for the font of a keycap (`1️⃣`, the digit is in the text font)
    // or `©️` through the faces of U+FE0F in this order and would load each one on the way
    const chunks = buildChunks().reverse();
    chunks.push(...chunks.splice(chunks.findIndex((chunk) => chunk.name === 'symbols'), 1));
    let css = [
        '/*!',
        ' * AUTO-GENERATED FILE — do not edit. Built by emoji.build.mjs.',
        ` * Noto Color Emoji ${NOTO_RELEASE} (COLRv1), SIL Open Font License 1.1, see OFL.txt`,
        ' */',
        '',
    ].join('\n');

    for (const chunk of chunks) {
        const file = `humhub-emoji-${chunk.name}.woff2`;
        const woff2 = await subset(font, {
            unicodes: [...chunk.codePoints].sort((a, b) => a - b).map((cp) => cp.toString(16)).join(','),
            'layout-features': '*',
            flavor: 'woff2',
        });
        writeFileSync(resolve(fontDir, file), woff2);
        console.log(`${file}: ${(woff2.length / 1024).toFixed(0)} KB`);

        css += `@font-face {
    font-family: '${FONT_FAMILY}';
    font-style: normal;
    font-weight: 400;
    font-display: swap;
    size-adjust: ${SIZE_ADJUST};
    src: url('${file}') format('woff2') tech(color-COLRv1);
    unicode-range: ${unicodeRange(chunk.codePoints)};
}
`;
    }

    writeFileSync(resolve(fontDir, 'emoji.css'), css);
    writeFileSync(resolve(fontDir, 'OFL.txt'), license);
}

/**
 * The data of emoji-picker-element: the localized names, keywords and shortcodes (CLDR of the language plus the
 * English emojibase shortcodes, e.g. `thumbsup`)
 */
function buildData() {
    rmSync(dataDir, { recursive: true, force: true });
    mkdirSync(dataDir, { recursive: true });

    const shortcodes = (locale, preset) => {
        const file = resolve(emojibase, locale, 'shortcodes', preset + '.json');
        return existsSync(file) ? readJson(file) : {};
    };
    const englishShortcodes = shortcodes('en', 'emojibase');

    for (const locale of readdirSync(emojibase)) {
        if (!existsSync(resolve(emojibase, locale, 'data.json'))) {
            continue;
        }

        const cldrShortcodes = shortcodes(locale, 'cldr');
        const data = readJson(resolve(emojibase, locale, 'data.json'))
            .filter((emoji) => emoji.group !== undefined && emoji.group !== 2)
            .map((emoji) => {
                const result = {
                    annotation: emoji.label,
                    emoji: emoji.emoji,
                    group: emoji.group,
                    order: emoji.order,
                    version: emoji.version,
                };
                const codes = [...new Set([].concat(
                    cldrShortcodes[emoji.hexcode] || [],
                    englishShortcodes[emoji.hexcode] || [],
                ))];
                if (codes.length) {
                    result.shortcodes = codes;
                }
                if (emoji.tags) {
                    result.tags = emoji.tags;
                }
                // A string for the picker, emojibase has a list for some (`[':)', ':-)']`)
                if (emoji.emoticon) {
                    result.emoticon = [].concat(emoji.emoticon)[0];
                }
                // Mixed skin tones of several people (`tone: [1, 2]`) cannot be chosen in the picker
                const skins = (emoji.skins || []).filter((skin) => typeof skin.tone === 'number');
                if (skins.length) {
                    result.skins = skins.map((skin) => ({
                        tone: skin.tone,
                        emoji: skin.emoji,
                        version: skin.version,
                    }));
                }

                return result;
            });

        writeFileSync(resolve(dataDir, locale + '.json'), JSON.stringify(data));
    }

    writeFileSync(resolve(dataDir, 'LICENSE'), readFileSync(resolve(emojibase, 'LICENSE')));
    console.log(`Picker data: ${readdirSync(dataDir).length - 1} languages`);
}

await buildFont();
buildData();
