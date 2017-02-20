<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;

/**
 * Edit Link for Wall Entries
 *
 * This widget will be attached to the WallEntryControls and displays
 * the "Edit" Link to the Content Objects if the editMode is set to EDIT_MODE_MODAL.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 1.2
 */
class EditLinkModal extends EditLink
{

    /**
     * Executes the widget.
     */
    public function run()
    {
        if(!$this->url) {
            return;
        }
        
        if ($this->model->content->canWrite()) {
            return '<li>'.\yii\helpers\Html::a('<i class="fa fa-pencil"></i>'. Yii::t('ContentModule.widgets_views_editLink', 'Edit') , '#', 
                    ['class' => "stream-entry-edit-link", 'data-action-click' => "editModal", 'data-action-url' => $this->url]).'</li>';
        }
    }

}