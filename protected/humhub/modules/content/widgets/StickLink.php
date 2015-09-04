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
 * StickLinkWidget for Wall Entries shows a stick link.
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Stick or Unstick" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class StickLink extends \yii\base\Widget
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

        // Show stick links only inside content container streams
        if (!Yii::$app->controller instanceof ContentContainerController || !$this->content->content->canStick()) {
            return;
        }

        return $this->render('stickLink', array(
                    'stickUrl' => Url::to(['/content/content/stick', 'id' => $this->content->content->id]),
                    'unstickUrl' => Url::to(['/content/content/un-stick', 'id' => $this->content->content->id]),
                    'isSticked' => $this->content->content->isSticked()
        ));
    }

}

?>