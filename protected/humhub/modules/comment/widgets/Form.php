<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\helpers\IdHelper;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\Module;
use humhub\modules\content\models\Content;
use humhub\modules\file\handler\FileHandlerCollection;
use Yii;
use yii\helpers\Url;

class Form extends Widget
{
    public ?Content $content;
    public ?CommentModel $parentComment;

    /**
     * @var Comment|null can be provided if comment validation failed, otherwise a dummy model will be created
     */
    public $model;

    /**
     * @var string
     */
    public $mentioningUrl = '/user/mentioning/content';

    /**
     * @var bool
     */
    public $isHidden;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isHidden === null) {
            // Hide the comment form for sub comments until the button is clicked
            $this->isHidden = ($this->parentComment !== null);
        }
    }

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if (!$module->canComment($this->content)) {
            return '';
        }

        if (!$this->model) {
            $this->model = new CommentModel();
            $this->model->content_id = $this->content->id;
            $this->model->parent_comment_id = $this->parentComment?->id;
        }

        return $this->render('form', [
            'id' => IdHelper::getId($this->content, $this->parentComment),
            'model' => $this->model,
            'isNestedComment' => ($this->parentComment !== null),
            'mentioningUrl' => Url::to([$this->mentioningUrl, 'id' => $this->content->id]),
            'isHidden' => $this->isHidden,
            'fileHandlers' => FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
        ]);
    }
}
