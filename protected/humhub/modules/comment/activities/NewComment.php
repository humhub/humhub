<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\activities;

use humhub\modules\comment\models\Comment;
use Yii;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

/**
 * NewComment activity
 *
 * @author luke
 */
class NewComment extends BaseActivity implements ConfigurableActivityInterface
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'comment';

    /**
     * @inheritdoc
     */
    public $viewName = "newComment";

    /**
     * @var Comment
     */
    public $source;

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('CommentModule.base', 'Comments');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('CommentModule.base', 'Whenever a new comment was written.');
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->source->url;
    }

}
