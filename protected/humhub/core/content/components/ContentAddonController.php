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

/**
 * ContentAddonController is a base controller for ContentAddons.
 * 
 * It automatically loads the target content or content addon record based
 * on given parameters contentModel or contentId.
 * 
 * Also an access check is performed.
 *
 * @author luke
 * @version 0.11
 */
class ContentAddonController extends Controller
{

    /**
     * Content this addon belongs to
     * 
     * @var HActiveRecordContent
     */
    public $parentContent;

    /**
     * ContentAddon this addon may belongs to
     * ContentAddons may also belongs to ContentAddons e.g. Like -> Comment
     * 
     * @var HActiveRecordContent
     */
    public $parentContentAddon;

    /**
     * @var HActiveRecordContentAddon
     */
    public $contentAddon;

    /**
     * Class name of content model class
     * 
     * @var string 
     */
    public $contentModel;

    /**
     * Primary key of content model record
     * 
     * @var int
     */
    public $contentId;

    /**
     * Automatically loads the by content or content addon given by parameter.
     * className & id
     * 
     * @return type
     */
    public function beforeAction($action)
    {

        $modelClass = Yii::app()->request->getParam('contentModel');
        $pk = (int) Yii::app()->request->getParam('contentId');

        if ($modelClass == "" || $pk == "") {
            throw new CHttpException(500, 'Model & ID parameter required!');
        }

        Helpers::CheckClassType($modelClass, array('HActiveRecordContentAddon', 'HActiveRecordContent'));

        $model = call_user_func(array($modelClass, 'model'));
        $target = $model->findByPk($pk);

        if ($target === null) {
            throw new CHttpException(500, 'Could not find underlying content or content addon record!');
        }

        if ($target instanceof HActiveRecordContentAddon) {
            $this->parentContentAddon = $target;
            $this->parentContent = $target->getSource();
        } else {
            $this->parentContent = $target;
        }

        if (!$this->parentContent->content->canRead()) {
            throw new CHttpException(403, 'Access denied!');
        }

        $this->contentModel = get_class($target);
        $this->contentId = $target->getPrimaryKey();

        return parent::beforeAction($action);
    }

    /**
     * Loads Content Addon
     * We also validates that the content addon corresponds to the loaded content.
     * 
     * @param string $className
     * @param int $pk
     */
    public function loadContentAddon($className, $pk)
    {
        if (!Helpers::CheckClassType($className, 'HActiveRecordContentAddon')) {
            throw new CException("Given className is not a content addon model!");
        }

        $model = call_user_func(array($className, 'model'));
        $target = $model->findByPk($pk);

        if ($target === null) {
            throw new CHttpException(500, 'Could not find content addon record!');
        }

        if ($target->object_model != get_class($this->parentContent) && $target->object_id != $this->parentContent->getPrimaryKey()) {
            throw new CHttpException(500, 'Content addon not belongs to given content record!');
        }

        $this->contentAddon = $target;
    }

}
