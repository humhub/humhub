<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerController;

/**
 * PinLinkWidget for Wall Entries shows a pin link.
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Pin or Unpin" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class PinLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function run()
    {

        // Show pin links only inside content container streams
        if (!Yii::$app->controller instanceof ContentContainerController || !$this->content->content->canPin()) {
            return;
        }

        return $this->render('pinLink', array(
                    'pinUrl' => Url::to(['/content/content/pin', 'id' => $this->content->content->id]),
                    'unpinUrl' => Url::to(['/content/content/un-pin', 'id' => $this->content->content->id]),
                    'isPinned' => $this->content->content->isPinned()
        ));
    }

}

?>