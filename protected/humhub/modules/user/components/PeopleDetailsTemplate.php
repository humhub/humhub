<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Markup;
use Twig\TemplateWrapper;
use Yii;

/**
 * Renders the details of a People card from the Twig template {@see Module::$peopleCardDetailsTemplate}.
 *
 * Each visible profile field is available by its internal name as the formatted, HTML encoded value
 * (e.g. the label of a select option, a link for URL fields), e.g. `{{ city }}`. The raw values,
 * e.g. for URL parameters, are available under `raw`, e.g. `{{ raw.room|url_encode }}`.
 * The rendered template is one HTML block, line breaks are up to the template (e.g. `<br>`).
 * It is shown below the profile fields selected in the People settings.
 *
 * @since 1.19.1
 */
class PeopleDetailsTemplate
{
    /**
     * @var TemplateWrapper[] compiled templates of this request, indexed by their source
     */
    private static array $templates = [];

    /**
     * @return string|null the configured template, null when none is set
     */
    public static function getTemplate(): ?string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        $template = trim((string)$module->peopleCardDetailsTemplate);

        return $template === '' ? null : $template;
    }

    public static function isActive(): bool
    {
        return self::getTemplate() !== null;
    }

    /**
     * @param User $user
     * @return string the HTML of the user's details, empty when no template is set
     */
    public static function render(User $user): string
    {
        $template = self::getTemplate();
        if ($template === null || $user->profile === null) {
            return '';
        }

        $variables = ['raw' => []];
        foreach (self::getProfileFields() as $profileField) {
            $value = (string)$profileField->getUserValue($user, false);
            $variables[$profileField->internal_name] = $value === '' ? '' : new Markup($value, Yii::$app->charset);
            $variables['raw'][$profileField->internal_name] = (string)$profileField->getUserValue($user, true, false);
        }

        return trim(self::getCompiledTemplate($template)->render($variables));
    }

    private static function getCompiledTemplate(string $template): TemplateWrapper
    {
        if (!isset(self::$templates[$template])) {
            $twig = new Environment(new ArrayLoader([]), ['autoescape' => 'html']);
            self::$templates[$template] = $twig->createTemplate($template);
        }

        return self::$templates[$template];
    }

    /**
     * @return ProfileField[]
     */
    private static function getProfileFields(): array
    {
        return Yii::$app->runtimeCache->getOrSet(
            __METHOD__,
            fn() => ProfileField::find()->where(['visible' => 1])->all(),
        );
    }
}
