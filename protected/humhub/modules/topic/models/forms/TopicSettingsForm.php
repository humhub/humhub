<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\models\forms;

use humhub\libs\BaseSettingsManager;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\Model;

/**
 * Topic settings form
 *
 * @property string $topicInputBehavior
 * @since 1.18.3
 */
class TopicSettingsForm extends Model
{
    public ?string $topicInputBehavior = null;
    public ?ContentContainerActiveRecord $contentContainer = null;
    private ?BaseSettingsManager $settings = null;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->settings = $this->isGlobal() ? Yii::$app->settings : $this->contentContainer->settings;

        $this->topicInputBehavior = $this->settings->get('topicInputBehavior', $this->isGlobal() ? 'visible' : 'default');

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['topicInputBehavior'], 'in', 'range' => array_keys($this->getTopicInputBehaviorOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'topicInputBehavior' => $this->isGlobal()
                ? Yii::t('TopicModule.base', 'Default topic input behavior')
                : Yii::t('TopicModule.base', 'Topic field behavior'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'topicInputBehavior' => $this->isGlobal()
                ? Yii::t('TopicModule.base', 'Defines how the topic field is displayed by default. Space admins can override this setting.')
                : ($this->contentContainer instanceof Space
                    ? Yii::t('TopicModule.base', 'Controls how the topic field appears when creating content in this space.')
                    : Yii::t('TopicModule.base', 'Controls how the topic field appears when creating content in this user.')),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settings->set('topicInputBehavior', $this->topicInputBehavior);

        return true;
    }

    public function getTopicInputBehaviorOptions(): array
    {
        $options = [
            'hidden' => Yii::t('TopicModule.base', 'Hidden'),
            'visible' => Yii::t('TopicModule.base', 'Always visible'),
            'required' => Yii::t('TopicModule.base', 'Required'),
        ];

        if (!$this->isGlobal()) {
            $options = array_merge([
                'default' => Yii::t('TopicModule.base', 'Use global default')
                    . ' (' . $options[(new self())->topicInputBehavior] . ')',
            ], $options);
        }

        return $options;
    }

    public function isGlobal(): bool
    {
        return $this->contentContainer === null;
    }
}
