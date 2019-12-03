<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\stream\widgets\StreamViewer as BaseStreamViewer;
use humhub\modules\user\models\User;
use humhub\modules\post\permissions\CreatePost;

/**
 * StreamViewer shows a users profile stream
 * 
 * @since 1.2.4
 * @author Luke
 */
class StreamViewer extends BaseStreamViewer
{

    /**
     * @var string the path to Stream Action to use
     */
    public $streamAction = '/user/profile/stream';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $createPostPermission = new CreatePost();

        if (empty($this->messageStreamEmptyCss)) {
            if ($this->contentContainer->permissionManager->can($createPostPermission)) {
                $this->messageStreamEmptyCss = 'placeholder-empty-stream';
            }
        }

        if (empty($this->messageStreamEmpty)) {
            if ($this->contentContainer->permissionManager->can($createPostPermission)) {
                if (Yii::$app->user->id === $this->contentContainer->id) {
                    $this->messageStreamEmpty = Yii::t('UserModule.profile', '<b>Your profile stream is still empty</b><br>Get started and post something...');
                } else {
                    $this->messageStreamEmpty = Yii::t('UserModule.profile', '<b>This profile stream is still empty</b><br>Be the first and post something...');
                }
            } else {
                $this->messageStreamEmpty = Yii::t('UserModule.profile', '<b>This profile stream is still empty!</b>');
            }
        }

        parent::init();
    }

}
