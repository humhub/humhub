<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\libs\BasePermission;

/**
 * Edit Link for Wall Entries
 *
 * This widget will attached to the WallEntryControlsWidget and displays
 * the "Edit" Link to the Content Objects.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.10
 */
class EditLink extends \yii\base\Widget
{

    /**
     * @var \humhub\modules\content\components\ContentActiveRecord
     */
    public $model = null;

    /**
     * @var string edit route.
     */
    public $url;
    
    /**
     * @var defines the edit type of the wallentry
     */
    public $mode;


    /**
     * Executes the widget.
     */
    public function run()
    {
        if(!$this->url) {
            return;
        }

        if ($this->model->content->canEdit()) {
            return $this->render('editLink', [
                        'editUrl' => $this->url,
                        'mode' => $this->mode
            ]);
        }
    }

}

?>