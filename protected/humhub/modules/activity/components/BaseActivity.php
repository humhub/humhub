<?php

namespace humhub\modules\activity\components;

use humhub\models\RecordMap;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

abstract class BaseActivity extends BaseObject
{
    protected ContentContainer $contentContainer;

    protected User $user;

    protected string $createdAt;

    public function __construct(Activity $record, $config = [])
    {
        parent::__construct($config);

        $this->contentContainer = $record->contentContainer;
        $this->user = $record->createdBy;
        $this->createdAt = $record->created_at;
    }


    public static function create(ContentProvider|ContentContainerActiveRecord $target, ?User $user = null): bool
    {
        $model = new Activity();
        $model->class = static::class;
        if ($target instanceof ContentProvider) {
            $model->contentcontainer_id = $target->content->contentcontainer_id;
            $model->content_id = $target->content->id;
        } else {
            $model->contentcontainer_id = $target->contentcontainer_id;
        }

        if ($target instanceof ContentAddonActiveRecord) {
            $model->content_addon_record_id = RecordMap::getId($target);
        }

        if ($user === null && Yii::$app->user->isGuest) {
            throw new InvalidArgumentException('Could not automatically determine if the user is guest.');
        }

        $model->created_by = $user ? $user->id : Yii::$app->user->identity->id;

        return $model->save();
    }

    public static function factory(Activity $record): BaseActivity
    {
        return Yii::createObject($record->class, ['record' => $record]);
    }

    public function renderWeb(): string
    {
        return Yii::$app->getView()->renderFile('@activity/views/layouts/web.php', array_merge(
            $this->getViewParams(), ['message' => $this->getAsText()]
        ));
    }


    public function renderPlaintext(): string
    {
        return Yii::$app->getView()->renderFile('@activity/views/layouts/mail_plaintext.php', array_merge(
            $this->getViewParams(), ['message' => $this->getAsText()]
        ));
    }

    public function renderMail(): string
    {
        return Yii::$app->getView()->renderFile('@activity/views/layouts/mail.php', array_merge(
            $this->getViewParams(), ['message' => $this->getAsText()]
        ));
    }

    protected function getViewParams()
    {
        return [
            'url' => '',
            'contentContainer' => $this->contentContainer,
            'createdAt' => $this->createdAt,
            'user' => $this->user
        ];
    }

    public abstract function getAsText(): string;

}
