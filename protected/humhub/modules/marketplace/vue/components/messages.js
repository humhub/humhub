import { i18n } from '@humhub/vue';

/**
 * Translated texts the island needs in more than one component.
 *
 * @since 1.20
 */

export const communityWarning = () => i18n.t('MarketplaceModule.base', 'These modules are provided by independent partners and developers and are <strong>not part of our curated selection</strong>.<br><br>HumHub does not review them for quality, security or compatibility. Support, updates and licensing are handled by the respective provider.<br><br>Make sure you trust the source of a module before installing it.');

export const communityAcknowledge = () => i18n.t('MarketplaceModule.base', 'I understand and want to continue.');

export const badgeLabel = (badge) => ({
    professional: i18n.t('MarketplaceModule.base', 'Professional Edition'),
    official: i18n.t('MarketplaceModule.base', 'Official'),
    partner: i18n.t('MarketplaceModule.base', 'Partner'),
    deprecated: i18n.t('MarketplaceModule.base', 'Deprecated'),
})[badge] || '';

const priceText = (price) => (price && !price.onRequest && price.amount !== null ? `${price.amount}€` : '');

export const buyLabel = (price) => {
    const amount = priceText(price);
    return amount === ''
        ? i18n.t('MarketplaceModule.base', 'Buy')
        : i18n.t('MarketplaceModule.base', 'Buy (%price%)').replace('%price%', amount);
};

// Placeholders of a text with emphasised values: `emphasis()` hands the translation sentinels
// instead of the values, `strongParts()` splits the translated text at them. The values are
// rendered as text nodes inside `<b>` (`RichText`), never as markup.
const MARK = '\u0001';
const MARKED = /\u0001(\w+)\u0001/;

/**
 * The params for `i18n.t()` of a text whose placeholders `keys` are to be emphasised.
 */
export const emphasis = (...keys) => Object.fromEntries(keys.map((key) => [key, `${MARK}${key}${MARK}`]));

/**
 * `[{ text, strong }]` of a text translated with `emphasis()` params: the text between the
 * placeholders, and each placeholder's value from `values` (strong).
 */
export const strongParts = (translated, values) => String(translated).split(MARKED)
    .map((part, index) => (index % 2 === 1 ? { text: String(values[part] ?? ''), strong: true } : { text: part, strong: false }))
    .filter((part) => part.text !== '');

/**
 * The note on a third-party module, shown before it is bought or installed.
 */
export const thirdPartyNotice = () => [
    i18n.t('MarketplaceModule.base', 'This Module was developed by a third-party.'),
    i18n.t('MarketplaceModule.base', 'The HumHub project does not guarantee the functionality, quality or the continuous development of this Module.'),
    i18n.t('MarketplaceModule.base', 'Third-party Modules are not covered by Professional Edition agreements.'),
];

/**
 * The note on a non-curated module (translator markup, no values).
 */
export const communityNotice = () => i18n.t('MarketplaceModule.base', 'If this Module is additionally marked as <strong>"Not curated"</strong>, it is not part of our curated selection and has not been reviewed by the HumHub team for quality, security or compatibility.');
