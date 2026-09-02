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
        // Skip the Post model's own "message required" rule when the post has (or will have)
        // file attachments, otherwise editing a message-less, file-only post always fails.
        if ($this->hasAttachments()) {
            $this->post->scenario = Post::SCENARIO_HAS_FILES;
        }

        if (!$this->post->validate() || !parent::validate($attributeNames, $clearErrors)) {
            $this->post->addError('message', Yii::t('PostModule.base', 'Post could not be saved!'));
        }

        if (!empty($this->post->message)) {
            return true;
        }

        // Allow empty message only With attachments
        if ($this->hasAttachments()) {
            return true;
        }

        $this->post->addError('message', Yii::t('PostModule.base', 'The post must not be empty!'));
    }

    /**
     * Checks if the post has file attachments, either newly uploaded ones pending attach
     * or ones already attached to an existing post.
     *
     * @return bool
     * @since 1.18.6
     */
    protected function hasAttachments(): bool
    {
        return !empty($this->fileList) || (!$this->post->isNewRecord && $this->post->fileManager->find()->count());
    }

    /**
     * Saves the form
     *
     * @return bool
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
