<?php

namespace humhub\modules\content\models\forms;

use humhub\modules\post\models\Post;
use humhub\modules\user\Module;
use Yii;
use yii\base\Model;
use yii\helpers\Url;

class ShareIntendTargetForm extends Model
{
    public $targetContainerGuid;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['targetContainerGuid'], 'required'],
        ];
    }

    public function attributeHints(): array
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        $canPostInOwnProfile =
            !$userModule->profileDisableStream // The profile stream is enabled
            && (new Post(Yii::$app->user->identity))->content->canEdit(); // Can post in own profile

        return [
            'targetContainerGuid' => $canPostInOwnProfile ?
                Yii::t('ContentModule.base', 'Select target Space/Profile.') :
                Yii::t('ContentModule.base', 'Select target Space.'),
        ];
    }

    public function getContainerSearchUrl(): string
    {
        return Url::to(['container-search-json']);
    }
}
