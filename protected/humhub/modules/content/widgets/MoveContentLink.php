<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;

/**
 * MoveContentLink used to move a wallentry to another space.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.3
 */
class MoveContentLink extends WallEntryControlLink
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $model;

    /**
     * @inheritdocs
     */
    public $icon = 'fa-arrows-h';

    /**
     * @inheritdocs
     */
    public $action = 'ui.modal.load';

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return Yii::t('ContentModule.base', 'Move content');
    }

    /**
     * @inheritdocs
     */
    public function getActionUrl() {
        if(!$this->model->content->container) {
            return null;
        }
        
        return $this->model->content->container->createUrl('/content/move/move', ['id' => $this->model->content->id]);
    }

    /**
     * @inheritdocs
     */
    public function preventRender()
    {
        // We show the move content link in case the user is generally allowed to move content within the container not considering other move content checks.
        return !$this->model->content->container || !$this->model->content->checkMovePermission();
    }
}
