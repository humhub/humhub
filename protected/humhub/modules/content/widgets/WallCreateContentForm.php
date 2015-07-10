<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use Yii;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * WallCreateContentForm is the base widget to create  "quick" create content forms above Stream/Wall.
 *
 * @author luke
 */
class WallCreateContentForm extends \yii\base\Widget
{

    /**
     * @var string form submit route/url (required)
     */
    public $submitUrl;

    /**
     * @var string submit button text
     */
    public $submitButtonText;

    /**
     * @var ContentContainerActiveRecord this content will belong to
     */
    public $contentContainer;

    /**
     * @var string form implementation
     */
    protected $form = "";

    /**
     * @inheritdoc
     */
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
     * Returns the custom form implementation.
     * 
     * @return string
     */
    public function renderForm()
    {
        return "";
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->contentContainer->canWrite())
            return;

        return $this->render('@humhub/modules/content/widgets/views/wallCreateContentForm', array(
                    'form' => $this->renderForm(),
                    'contentContainer' => $this->contentContainer,
                    'submitUrl' => $this->contentContainer->createUrl($this->submitUrl),
                    'submitButtonText' => $this->submitButtonText
        ));
    }

    /**
     * Creates the given ContentActiveRecord based on given submitted form information.
     * 
     * - Automatically assigns ContentContainer
     * - Access Check
     * - User Notification / File Uploads
     * - Reloads Wall after successfull creation or returns error json
     * 
     * [See guide section](guide:dev-module-stream.md#CreateContentForm)
     * 
     * @param ContentActiveRecord $record
     * @return string json 
     */
    public static function create(ContentActiveRecord $record)
    {
        Yii::$app->response->format = 'json';

        // Set Content Container
        $contentContainer = null;
        $containerClass = Yii::$app->request->post('containerClass');
        $containerGuid = Yii::$app->request->post('containerGuid', "");

        if ($containerClass === User::className()) {
            $contentContainer = User::findOne(['guid' => $containerGuid]);
            $record->content->visibility = 1;
        } elseif ($containerClass === Space::className()) {
            $contentContainer = Space::findOne(['guid' => $containerGuid]);
            $record->content->visibility = Yii::$app->request->post('visibility');
        }

        $record->content->container = $contentContainer;

        // Handle Notify User Features of ContentFormWidget
        // ToDo: Check permissions of user guids
        $userGuids = Yii::$app->request->post('notifyUserInput');
        if ($userGuids != "") {
            foreach (explode(",", $userGuids) as $guid) {
                $user = User::findOne(['guid' => trim($guid)]);
                if ($user) {
                    $record->content->notifyUsersOfNewContent[] = $user;
                }
            }
        }

        // Store List of attached Files to add them after Save
        $record->content->attachFileGuidsAfterSave = Yii::$app->request->post('fileList');
        if ($record->validate() && $record->save()) {
            return array('wallEntryId' => $record->content->getFirstWallEntryId());
        }

        return array('errors' => $record->getErrors());
    }

}
