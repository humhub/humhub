<?php

namespace humhub\modules\content\widgets;


use humhub\components\Widget;
use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use Yii;

/**
 * Can be used to render an archive icon for archived content.
 * @package humhub\modules\content\widgets
 * @since 1.14
 */
class StateBadge extends Widget
{
    public ?ContentActiveRecord $model;

    public function run()
    {
        if ($this->model === null) {
            return '';
        }

        switch ($this->model->content->state) {
            case Content::STATE_DRAFT:
                return Html::tag('span', Yii::t('ContentModule.base', 'Draft'),
                    ['class' => 'label label-danger label-state-draft']
                );
            case Content::STATE_SCHEDULED:
                return Html::tag('span', Yii::t('ContentModule.modules', 'Scheduled at {dateTime}', [
                        'dateTime' => Yii::$app->formatter->asDatetime($this->model->content->scheduled_at, 'short')
                    ]),
                    ['class' => 'label label-warning label-state-scheduled']
                );
            case Content::STATE_DELETED:
                return Html::tag('span', Yii::t('ContentModule.base', 'Deleted'),
                    ['class' => 'label label-danger label-state-deleted']
                );
        }

        return '';
    }
}
