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

        if ($this->model->content->state === Content::STATE_DRAFT) {
            return Html::tag(
                'span', Yii::t('ContentModule.base', 'Draft'),
                ['class' => 'label label-danger label-state-draft']
            );
        } elseif ($this->model->content->state === Content::STATE_DELETED) {
            return Html::tag(
                'span', Yii::t('ContentModule.base', 'Deleted'),
                ['class' => 'label label-danger label-state-deleted']
            );
        }

    }
}
