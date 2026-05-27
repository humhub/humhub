<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\services;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\topic\models\forms\TopicSettingsForm;
use Yii;

/**
 * Topic service
 *
 * @since 1.18.4
 */
class TopicService
{
    public const PICKER_VISIBILITY_DEFAULT = 'default';
    public const PICKER_VISIBILITY_VISIBLE = 'visible';
    public const PICKER_VISIBILITY_REQUIRED = 'required';
    public const PICKER_VISIBILITY_HIDDEN = 'hidden';

    private TopicSettingsForm $settings;

    public function __construct(public ?ContentContainerActiveRecord $contentContainer = null)
    {
        $this->settings = new TopicSettingsForm(['contentContainer' => $contentContainer]);
    }

    public static function instance(?ContentContainerActiveRecord $contentContainer = null): self
    {
        return new self($contentContainer);
    }

    public function getPickerVisibilityOptions(): array
    {
        $options = [
            self::PICKER_VISIBILITY_VISIBLE => Yii::t('TopicModule.base', 'Always show'),
            self::PICKER_VISIBILITY_REQUIRED => Yii::t('TopicModule.base', 'Topics required'),
            self::PICKER_VISIBILITY_HIDDEN => Yii::t('TopicModule.base', 'Hidden'),
        ];

        if (!$this->settings->isGlobal()) {
            $options = array_merge([
                self::PICKER_VISIBILITY_DEFAULT => Yii::t('TopicModule.base', 'Use global default')
                    . ' (' . $options[(new TopicSettingsForm())->pickerVisibility] . ')',
            ], $options);
        }

        return $options;
    }

    public function getPickerVisibility(): string
    {
        return $this->settings->pickerVisibility === self::PICKER_VISIBILITY_DEFAULT && !$this->settings->isGlobal()
            ? (new TopicSettingsForm())->pickerVisibility // Fallback to global default
            : $this->settings->pickerVisibility;
    }

    public function isVisible(): bool
    {
        return $this->getPickerVisibility() === self::PICKER_VISIBILITY_VISIBLE;
    }

    public function isRequired(): bool
    {
        return $this->getPickerVisibility() === self::PICKER_VISIBILITY_REQUIRED;
    }

    public function isHidden(): bool
    {
        return $this->getPickerVisibility() === self::PICKER_VISIBILITY_HIDDEN;
    }
}
