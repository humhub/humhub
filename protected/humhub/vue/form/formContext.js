/**
 * Internal building block of the `HumHubForm` suite (see `../HumHubForm.vue`'s own
 * docblock) — lives in a subdirectory of `vue/` so `vue.build.mjs`'s auto-registration
 * scan skips it (only top-level `.vue` files are registered as components; see that
 * file's own header comment). Shared between `HumHubForm.vue` (the provider) and
 * `form/fieldMixin.js` (the injector).
 */

/**
 * The `provide()`/`inject` key `HumHubForm.vue` publishes its form context under. A
 * plain string (not a `Symbol`) is deliberate: every consumer lives in this same
 * committed-artifact build (core's `vue/` tree), so there is no cross-bundle identity
 * concern a `Symbol` would guard against, and a string key can be tracked/inspected with
 * Vue Devtools like any other injected value.
 */
export const FORM_CONTEXT_KEY = 'humhubForm';

// Mirrors `\yii\helpers\BaseHtml::getInputIdByName()` (PHP) exactly, replacement pair
// order included — turns an input NAME (`Model[attribute]`) into the same lowercase,
// dash-joined id Yii's ActiveField already renders for the equivalent ActiveForm field
// (`Comment[message]` -> `comment-message`), so a native HumHubForm field and a
// server-rendered ActiveForm field for the same attribute always agree on `id`/`for`.
const ID_REPLACEMENTS = [
    ['[]', ''],
    ['][', '-'],
    ['[', '-'],
    [']', ''],
    [' ', '-'],
    ['.', '-'],
    ['--', '-'],
];

/**
 * @param {string} name an input name, e.g. `Comment[message]` or a bare `message`.
 * @returns {string} the Yii-convention input id, e.g. `comment-message` / `message`.
 */
export function toInputId(name) {
    return ID_REPLACEMENTS.reduce(
        (value, [search, replace]) => value.split(search).join(replace),
        name.toLowerCase(),
    );
}
