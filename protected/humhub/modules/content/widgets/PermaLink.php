<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

/**
 * PermaLink for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Permalink" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class PermaLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\activerecords\Content
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run()
    {

        return $this->render('permaLink', array(
                    'object' => $this->content,
                    'model' => $this->content->content->object_model,
                    'id' => $this->content->content->object_id,
        ));
    }

}

?>