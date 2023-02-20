<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models\forms;

use DateTime;
use DateTimeZone;
use humhub\libs\DbDateValidator;
use humhub\modules\content\models\Content;
use Yii;
use yii\base\Model;

class ScheduleOptionsForm extends Model
{
    public ?Content $content = null;
    public bool $enabled = false;
    public ?string $date = null;
    public ?string $time = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->content instanceof Content && $this->content->scheduled_at !== null) {
            $this->enabled = true;
            $this->date = $this->content->scheduled_at;
            $scheduledDateTime = new DateTime($this->content->scheduled_at, new DateTimeZone('UTC'));
            $this->time = Yii::$app->formatter->asTime($scheduledDateTime, 'short');
        }

        if ($this->date === null) {
            $this->date = (new DateTime('tomorrow'))->format('Y-m-d H:i:s');
        }

        if ($this->time === null) {
            $this->time = '';
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['enabled', 'boolean'],
            ['date', DbDateValidator::class, 'timeAttribute' => 'time'],
            ['time', 'date', 'type' => 'time', 'format' => Yii::$app->formatter->isShowMeridiem() ? 'h:mm a' : 'php:H:i']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'enabled' => Yii::t('ContentModule.base', 'Enable schedule date time')
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->content instanceof Content && !$this->content->isNewRecord) {
            $this->content->setState(Content::STATE_SCHEDULED, ['scheduled_at' => $this->date]);
            return $this->content->save();
        }

        return true;
    }

    public function getStateTitle(): string
    {
        return Yii::t('ContentModule.modules', 'Scheduled at {dateTime}', [
            'dateTime' => Yii::$app->formatter->asDatetime($this->date, 'short')
        ]);
    }
}
