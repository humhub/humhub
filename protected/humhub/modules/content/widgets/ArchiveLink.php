<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;
use humhub\modules\content\components\ContentContainerController;

/**
 * StickLink for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Stick or Unstick" Link to the Content Objects.
 *
 * @since 0.5
 */
class ArchiveLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!Yii::$app->controller instanceof ContentContainerController || !$this->content->content->canArchive()) {
            return;
        }

        return $this->render('archiveLink', array(
                    'object' => $this->content,
                    'id' => $this->content->content->id,
        ));
    }

}

?>