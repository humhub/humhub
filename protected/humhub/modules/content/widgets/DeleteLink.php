<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

/**
 * Delete Link for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Delete" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class DeleteLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $content = null;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if ($this->content->content->canEdit()) {
            return $this->render('deleteLink', array(
                        'model' => $this->content->content->object_model,
                        'id' => $this->content->content->object_id
            ));
        }
    }

}

?>