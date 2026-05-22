<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\models\forms;

use humhub\libs\BaseSettingsManager;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\topic\services\TopicService;
use Yii;
use yii\base\Model;

/**
 * Topic settings form
 *
 * @since 1.18.4
 */
class TopicSettingsForm extends Model
{
    public ?string $pickerVisibility = null;
    public ?ContentContainerActiveRecord $contentContainer = null;
    private ?BaseSettingsManager $settings = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->settings = $this->isGlobal() ? Yii::$app->settings : $this->contentContainer->settings;

        $this->pickerVisibility = $this->settings->get('topicPickerVisibility', $this->isGlobal()
            ? TopicService::PICKER_VISIBILITY_HIDDEN
            : TopicService::PICKER_VISIBILITY_DEFAULT);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['pickerVisibility'], 'in', 'range' => array_keys($this->getPickerVisibilityOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'pickerVisibility' => $this->isGlobal()
                ? Yii::t('TopicModule.base', 'Default topic picker visibility')
                : Yii::t('TopicModule.base', 'Topic picker visibility'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'pickerVisibility' => Yii::t('TopicModule.base', 'Controls how the topic picker appears in content creation forms above the stream.'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settings->set('topicPickerVisibility', $this->pickerVisibility);

        return true;
    }

    public function getPickerVisibilityOptions(): array
    {
        return TopicService::instance($this->contentContainer)->getPickerVisibilityOptions();
    }

    public function isGlobal(): bool
    {
        return $this->contentContainer === null;
    }
}
