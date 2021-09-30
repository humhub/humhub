<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use Yii;
use yii\helpers\Url;

/**
 * PermaLink for Comment
 *
 * @since 1.10
 */
class PermaLink extends CommentControl
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('permaLink', [
            'permaUrl' => Url::to(['/comment/perma', 'id' => $this->comment->id], true),
            'modalWindowTitle' => Yii::t('CommentModule.base', '<strong>Permalink</strong> to this comment'),
        ]);
    }

}
