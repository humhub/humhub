<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use yii\base\Widget;

/**
 * Delete Link for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Delete" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class DeleteLink extends Widget
{
    /**
     * @var ContentActiveRecord
     */
    public $content = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if ($this->content->content->canEdit()) {

            $isAdmin = $this->content->content->created_by !== Yii::$app->user->id;

            return $this->render('deleteLink', [
                'model' => $this->content->content->object_model,
                'id' => $this->content->content->object_id,
                'isAdmin' => $isAdmin,
            ]);
        }

        return '';
    }
}
