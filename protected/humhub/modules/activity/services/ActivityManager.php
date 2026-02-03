<?php

namespace humhub\modules\activity\services;

use humhub\components\Event;
use humhub\helpers\DataTypeHelper;
use humhub\models\RecordMap;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\ModelEvent;

class ActivityManager extends Component
{
    public const EVENT_BEFORE_DISPATCH = 'beforeDispatch';

    public static function dispatch(
        string $class,
        ContentProvider|ContentContainerActiveRecord $target,
        ?User $user = null,
    ): ?BaseActivity {
        if (!DataTypeHelper::isClassType($class, BaseActivity::class)) {
            throw new InvalidArgumentException("Class {$class} does not implement " . BaseActivity::class);
        }

        $event = new ModelEvent(['data' => ['class' => $class, 'target' => $target, 'user' => $user]]);
        Event::trigger(static::class, self::EVENT_BEFORE_DISPATCH, $event);

        if (!$event->isValid) {
            return null;
        }

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
        $model->save();

        $activity = static::load($model);
        (new GroupingService($activity))->afterInsert();

        return $activity;
    }

    public static function load(Activity $record): BaseActivity
    {
        if (!empty($record->group_max_id) && $record->group_max_id !== $record->id) {
            $groupCount = $record->group_count;
            $record = Activity::findOne(['activity.id' => $record->group_max_id]);
            $record->group_count = $groupCount;
        }

        return Yii::createObject($record->class, ['record' => $record]);
    }

    /**
     * Should be triggered on all possible Activity related Content changes.
     * e.g. Visibility, Move Content
     */
    public static function afterContentChange(Content $content): void
    {
        Activity::updateAll(['contentcontainer_id' => $content->contentcontainer_id], ['content_id' => $content->id]);
        foreach (Activity::find()->andWhere(['content_id' => $content->id])->all() as $activity) {
            (new GroupingService(static::load($activity)))->afterUpdate();
        }
    }
}
