<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use Yii;
use humhub\modules\content\components\ContentContainerController;
use yii\base\Widget;

/**
 * PinLink for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Pin or Unpin" Link to the Content Objects.
 *
 * @since 0.5
 */
class ArchiveLink extends Widget
{
    /**
     * @var ContentActiveRecord
     */
    public $content;

    /**
     * Executes the widget.
     */
    public function run()
    {
        if (!$this->content->content->canArchive()) {
            return '';
        }

        return $this->render('archiveLink', [
            'object' => $this->content,
            'id' => $this->content->content->id,
        ]);
    }

}
