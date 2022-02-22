<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\models\forms;

use humhub\modules\post\models\Post;
use Yii;
use yii\web\ServerErrorHttpException;

/**
 * PostEditForm
 * @package humhub\modules\post\models\forms
 *
 * @since 1.11
 */
class PostEditForm extends yii\base\Model
{
    /**
     * The list of files attached to a Post
     * @var array
     */
    public $fileList;

    /**
     * @var Post The edited Post
     */
    public $post;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fileList'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        return parent::load($data, $formName) | $this->post->load($data);
    }

    /**
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!$this->post->validate() || !parent::validate($attributeNames, $clearErrors)) {
            $this->post->addError('message', Yii::t('PostModule.base', 'Post could not be saved!'));
        }

        if (!empty($this->post->message)) {
            return true;
        }

        // Allow empty message only With attachments
        if (!empty($this->fileList) || (!$this->post->isNewRecord && $this->post->fileManager->find()->count())) {
            return true;
        }

        $this->post->addError('message', Yii::t('PostModule.base', 'The post must not be empty!'));
    }

    /**
     * Saves the form
     *
     * @return boolean
     * @throws ServerErrorHttpException
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->post->save()) {
            $this->post->fileManager->attach($this->fileList);
            return true;
        }

        $this->post->addError('message', Yii::t('PostModule.base', 'Post could not be saved!'));
        return false;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}