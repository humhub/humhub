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
 * Disable/Enable comments link for Wall Entries.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.10
 */
class DisableCommentsLink extends Widget
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

        if (!$content->canEdit()) {
            return '';
        }

        return $this->render('disableCommentsLink', [
            'content' => $content,
            'disableCommentsLink' => Url::to(['/content/content/disable-comments', 'id' => $content->id]),
            'enableCommentsLink' => Url::to(['/content/content/enable-comments', 'id' => $content->id]),
        ]);
    }
}
