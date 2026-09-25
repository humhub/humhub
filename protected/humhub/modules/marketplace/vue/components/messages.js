import { i18n } from '@humhub/vue';

/**
 * Translated texts the island needs in more than one component.
 *
 * @since 1.20
 */

export const communityWarning = () => i18n.t('MarketplaceModule.base', 'Community modules are developed by third parties and are <strong>not tested or maintained by the HumHub team</strong>.<br><br>They may not be compatible with your HumHub version, can cause <strong>instability or unexpected behavior</strong>, and may stop working after future updates. Their long-term maintenance is not guaranteed.<br><br>Only enable this option if you understand the risks and trust the source of the module you intend to install.');

export const communityAcknowledge = () => i18n.t('MarketplaceModule.base', 'I understand the risk and want to continue.');

export const badgeLabel = (badge) => ({
    professional: i18n.t('MarketplaceModule.base', 'Professional Edition'),
    official: i18n.t('MarketplaceModule.base', 'Official'),
    partner: i18n.t('MarketplaceModule.base', 'Partner'),
    deprecated: i18n.t('MarketplaceModule.base', 'Deprecated'),
    community: i18n.t('MarketplaceModule.base', 'Community'),
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
 * The note on an unverified community module (translator markup, no values).
 */
export const communityNotice = () => i18n.t('MarketplaceModule.base', 'If this Module is additionally marked as <strong>"Unverified Community"</strong> it is neither tested nor maintained by the HumHub project team. It may cause instability or stop working after future updates.');
