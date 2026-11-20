<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\models\forms;

use humhub\modules\content\widgets\richtext\extensions\file\FileExtension;
use humhub\modules\file\libs\FileHelper;
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
     * Checks if the post has file attachments shown in the stream, either newly uploaded ones pending attach
     * or ones already attached to an existing post. Files hidden in the stream (uploaded by the rich text
     * editor) are not counted, they are shown only when they are referenced in the message.
     *
     * @return bool
     * @since 1.18.6
     */
    protected function hasAttachments(): bool
    {
        return FileHelper::existsStreamFile($this->fileList) || (!$this->post->isNewRecord && $this->post->fileManager->findStreamFiles());
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

        $removedFileGuids = array_diff(
            FileExtension::getFileGuids($this->post->getOldAttribute('message')),
            FileExtension::getFileGuids($this->post->message),
        );

        if ($this->post->save()) {
            // Files hidden in the stream (uploaded by the rich text editor) are attached only when the message references them
            $this->post->fileManager->attach(FileHelper::filterStreamFiles($this->fileList));
            $this->deleteRemovedFiles($removedFileGuids);
            return true;
        }

        $this->post->addError('message', Yii::t('PostModule.base', 'Post could not be saved!'));
        return false;
    }

    /**
     * Deletes the files, which are hidden in the stream and were removed from the message, they are not
     * shown anywhere anymore.
     *
     * @param string[] $fileGuids GUIDs of the files removed from the message
     */
    private function deleteRemovedFiles(array $fileGuids): void
    {
        if ($fileGuids === []) {
            return;
        }

        foreach ($this->post->fileManager->findAll() as $file) {
            if (!$file->show_in_stream && in_array($file->guid, $fileGuids, true)) {
                $file->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }
}
