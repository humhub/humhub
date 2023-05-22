<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use humhub\modules\comment\Module;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\handler\FileHandlerCollection;
use Yii;
use yii\helpers\Url;

/**
 * This widget is used include the comments functionality to a wall entry.
 *
 * Normally it shows a excerpt of all comments, but provides the functionality
 * to show all comments.
 *
 * @since 0.5
 */
class Form extends Widget
{
    /**
     * @var CommentModel|ContentActiveRecord
     */
    public $object;

    /**
     * @var Comment|null can be provided if comment validation failed, otherwise a dummy model will be created
     */
    public $model;

    /**
     * @var string
     */
    public $mentioningUrl = '/search/mentioning/content';

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
            $this->isHidden = ($this->object instanceof Comment);
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

        if (!$module->canComment($this->object)) {
            return '';
        }

        if (!$this->model) {
            $this->model = new CommentModel();
            $this->model->setPolyMorphicRelation($this->object);
        }

        return $this->render('form', [
            'objectModel' => get_class($this->object),
            'objectId' => $this->object->getPrimaryKey(),
            'id' => $this->object->getUniqueId(),
            'model' => $this->model,
            'isNestedComment' => ($this->object instanceof CommentModel),
            'mentioningUrl' => Url::to([$this->mentioningUrl, 'id' => $this->object->content->id]),
            'isHidden' => $this->isHidden,
            'fileHandlers' => FileHandlerCollection::getByType([FileHandlerCollection::TYPE_IMPORT, FileHandlerCollection::TYPE_CREATE]),
        ]);
    }

}
