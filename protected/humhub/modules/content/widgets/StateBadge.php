<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use DateTime;
use DateTimeZone;
use Exception;
use humhub\components\Widget;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\widgets\bootstrap\Badge;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Can be used to render an archive icon for archived content.
 * @package humhub\modules\content\widgets
 * @since 1.14
 */
class StateBadge extends Widget
{
    public ?ContentActiveRecord $model;

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function run()
    {
        switch ($this->getState()) {
            case Content::STATE_DRAFT:
                return Badge::danger(Yii::t('ContentModule.base', 'Draft'))->cssClass('badge-state-draft');
            case Content::STATE_SCHEDULED:
                $scheduledDateTime = new DateTime($this->model->content->scheduled_at, new DateTimeZone('UTC'));
                return Badge::warning(Yii::t('ContentModule.base', 'Scheduled for {dateTime}', [
                    'dateTime' => Yii::$app->formatter->asDatetime($scheduledDateTime, 'short'),
                ]))->cssClass('badge-state-scheduled');
            case Content::STATE_DELETED:
                return Badge::danger(Yii::t('ContentModule.base', 'Deleted'))->cssClass('badge-state-deleted');
        }

        return '';
    }

    /**
     * @return int|null
     * @since 1.16
     */
    public function getState(): ?int
    {
        if ($this->model === null) {
            return null;
        }

        return $this->model->content->state;
    }
}
