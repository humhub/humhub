<?php

namespace humhub\modules\content\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\Icon;
use Yii;
use yii\helpers\Url;

class PublishDraftLink extends Widget
{
    /**
     * @var ContentActiveRecord
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->content->content->getStateService()->isDraft()
            || !$this->content->content->canEdit()) {
            return '';
        }

        $publishUrl = Url::to(['/content/content/publish-draft', 'id' => $this->content->content->id]);

        return Html::tag(
            'li',
            Html::a(
                Icon::get('arrow-back-up-double') . ' '
                . Yii::t('ContentModule.base', 'Publish draft'),
                '#',
                ['data-action-click' => 'publishDraft', 'data-action-url' => $publishUrl],
            ),
        );
    }

}
