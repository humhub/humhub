<?php

namespace humhub\modules\content\models\forms;

use humhub\modules\mail\models\Message;
use humhub\modules\mail\permissions\StartConversation;
use humhub\modules\post\models\Post;
use humhub\modules\space\components\ActiveQuerySpace;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\IntegrityException;

class CreateContentForm extends Model
{
    /**
     * @var ?string
     */
    public $target;

    /**
     * @var ?Space
     */
    public $targetSpace;

    /**
     * @var ?array
     */
    public $targetSpaceGuids;

    /**
     * pre-uploaded File GUIDs to be attached to the new content
     *
     * E.g., if the mobile app uploads files, the guid of the files are stored here, and then reposted to WallCreateContentForm to be attached to the new content
     *
     * @var ?array
     */
    public $fileList;

    /**
     * @inerhitdoc
     * @throws Exception
     * @throws IntegrityException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function init()
    {
        parent::init();

        $targetNames = $this->getTargetNames();
        if (count($targetNames) === 1) {
            $this->target = array_key_first($targetNames);
        }
    }

    public function afterValidate()
    {
        parent::afterValidate();

        if ($this->targetSpaceGuids) {
            $this->targetSpace = Space::findOne(['guid' => $this->targetSpaceGuids]);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target'], 'string'],
            [['targetSpaceGuids', 'fileList'], 'safe'],
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeLabels()
    {
        return [
            'target' => Yii::t('ContentModule.base', 'Select a target'),
            'targetSpaceGuids' => Yii::t('ContentModule.base', 'Choose a Space'),
        ];
    }

    /**
     * @return array
     * @throws Exception
     * @throws IntegrityException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getTargetNames(): array
    {
        /** @var \humhub\modules\user\Module $userModule */
        $userModule = Yii::$app->getModule('user');

        /** @var \humhub\modules\mail\Module $mailModule */
        $mailModule = Yii::$app->getModule('mail');

        $targetNames = [];

        if (
            !$userModule->profileDisableStream // The profile stream is enabled
            && (new Post(Yii::$app->user->identity))->content->canEdit() // Can post in own profile
        ) {
            $targetNames[User::class] = Yii::t('ContentModule.base', 'My profile');
        }

        if (static::getSpaceSearchQuery()->exists()) { // Can post in at least one space
            $targetNames[Space::class] = Yii::t('ContentModule.base', 'A Space');
        }

        if (
            $mailModule
            && Yii::$app->user->can(StartConversation::class)
        ) {
            $targetNames[Message::class] = Yii::t('ContentModule.base', 'A message');
        }

        return $targetNames;
    }

    public function getSpaceSearchUrl()
    {
        return '/content/content/create-search-space';
    }

    public static function getSpaceSearchQuery(): ActiveQuerySpace
    {
        return (new ActiveQuerySpace(Space::class))
            ->visible()
            ->filterBlockedSpaces()
            ->andWhere(['space.status' => Space::STATUS_ENABLED]);
    }
}
