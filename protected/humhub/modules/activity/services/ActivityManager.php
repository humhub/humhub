<?php

namespace humhub\modules\activity\services;

use humhub\models\RecordMap;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;

class ActivityManager
{
    public static function dispatch(
        string $class,
        ContentProvider|ContentContainerActiveRecord $target,
        ?User $user = null,
    ): bool {
        $model = new Activity();
        $model->class = $class;
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

    public static function load(Activity $record): BaseActivity
    {
        return Yii::createObject($record->class, ['record' => $record]);
    }
}
