<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\Html;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\widgets\Link;
use Yii;
use yii\base\Widget;

/**
 * Schedule link for updating the schedule options of Wall Entries.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.14
 */
class ScheduleLink extends Widget
{
    public ContentActiveRecord $contentRecord;
    public array $allowedStates = [Content::STATE_DRAFT, Content::STATE_SCHEDULED];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = $this->contentRecord->content;

        if (!in_array($content->state, $this->allowedStates)) {
            return '';
        }

        $contentContainer = $content->container;
        if (!$contentContainer instanceof ContentContainerActiveRecord) {
            return '';
        }

        if (!$content->canEdit()) {
            return '';
        }

        return Html::tag('li', Link::withAction(
            Yii::t('ContentModule.base', 'Schedule publication'),
            'scheduleOptions',
            $contentContainer->createUrl('/content/content/schedule-options', ['id' => $content->id]),
        )
            ->icon('clock-o'));
    }
}
