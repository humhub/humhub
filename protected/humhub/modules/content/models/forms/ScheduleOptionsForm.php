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

        if ($this->hasContent() && $this->content->scheduled_at !== null) {
            $this->enabled = $this->content->state == Content::STATE_SCHEDULED;
            $this->date = $this->content->scheduled_at;
        }

        if ($this->date === null) {
            $this->date = (new DateTime('tomorrow'))->format('Y-m-d H:i:s');
        }

        if ($this->time === null) {
            $this->initTime();
        }
    }

    private function initTime()
    {
        if ($this->date === null) {
            $this->time = '';
        } else {
            $scheduledDateTime = new DateTime($this->date, new DateTimeZone('UTC'));
            $this->time = Yii::$app->formatter->asTime($scheduledDateTime, 'short');
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
            'enabled' => Yii::t('ContentModule.base', 'Activate scheduling')
        ];
    }

    public function load($data, $formName = null)
    {
        if (!parent::load($data, $formName)) {
            return false;
        }

        if (!$this->isSubmitted() && !$this->hasContent()) {
            $this->normalizeDate();
            $this->initTime();
        }

        return true;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->hasContent()) {
            if ($this->enabled) {
                $this->content->setState(Content::STATE_SCHEDULED, ['scheduled_at' => $this->date]);
            } else {
                $this->content->setState(Content::STATE_DRAFT);
            }
            return $this->content->save();
        }

        return $this->isSubmitted();
    }

    public function getStateTitle(): string
    {
        return Yii::t('ContentModule.base', 'Scheduled at {dateTime}', [
            'dateTime' => Yii::$app->formatter->asDatetime($this->date, 'short')
        ]);
    }

    public function hasContent(): bool
    {
        return $this->content instanceof Content && !$this->content->isNewRecord;
    }

    public function isSubmitted(): bool
    {
        return Yii::$app->request->post('state') == Content::STATE_SCHEDULED;
    }

    private function normalizeDate()
    {
        if ($this->date === null) {
            return;
        }

        $datetime = new DateTime('now', new DateTimeZone('UTC'));
        $datetime->setTimestamp(strtotime($this->date));
        $this->date = $datetime->format('Y-m-d H:i:s');
    }
}
