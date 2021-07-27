<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * Lock/Unlock comments link for Wall Entries.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.10
 */
class LockCommentsLink extends Widget
{

    /**
     * @var ContentActiveRecord
     */
    public $contentRecord;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = $this->contentRecord->content;

        if (!$content->canLockComments()) {
            return '';
        }

        return $this->render('lockCommentsLink', [
            'content' => $content,
            'lockCommentsLink' => Url::to(['/content/content/lock-comments', 'id' => $content->id]),
            'unlockCommentsLink' => Url::to(['/content/content/unlock-comments', 'id' => $content->id]),
        ]);
    }
}
