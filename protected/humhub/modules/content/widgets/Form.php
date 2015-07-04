<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\content\widgets;

use Yii;
use yii\helpers\Url;
use yii\web\HttpException;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Description of ContentFormWidget
 *
 * @author luke
 */
class Form extends \yii\base\Widget
{

    /**
     * URL to Submit ContentForm to
     */
    public $submitUrl;
    public $submitButtonText;

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * Form HTML
     */
    protected $form = "";

    public function init()
    {

        if ($this->submitButtonText == "")
            $this->submitButtonText = Yii::t('ContentModule.widgets_ContentFormWidget', 'Submit');

        if ($this->contentContainer == null || !$this->contentContainer instanceof ContentContainerActiveRecord) {
            throw new HttpException(500, "No Content Container given!");
        }

        return parent::init();
    }

    /**
     * Renders form and stores output in $form
     * Overwrite me!
     */
    public function renderForm()
    {
        return "";
    }

    /**
     * Checks write permissions
     */
    protected function hasWritePermission()
    {
        return $this->contentContainer->canWrite();
    }

    public function run()
    {

        if (!$this->hasWritePermission())
            return;

        $this->renderForm();

        return $this->render('@humhub/modules/content/widgets/views/form', array(
                    'form' => $this->form,
                    'contentContainer' => $this->contentContainer,
                    'submitUrl' => $this->submitUrl,
                    'submitButtonText' => $this->submitButtonText
        ));
    }

}
