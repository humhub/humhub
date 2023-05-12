<?php

namespace humhub\modules\notification\jobs;

use humhub\modules\comment\models\Comment;
use humhub\modules\like\models\Like;
use humhub\modules\notification\models\Notification;
use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\Mentioning;
use Yii;
use yii\helpers\ArrayHelper;

class MarkAsReadJob extends ActiveJob
{
    public $sourceClass;

    public $sourcePk;

    public $userId;

    public function run()
    {
        $model = call_user_func([$this->sourceClass, 'findOne'], $this->sourcePk);

        if (!$model) {
            return;
        }

        $sources = [$model];

        $notificationsWhereClauses = [];

        while (($model->hasMethod('getSource') || $model->hasProperty('source')) && $model = $model->source) {
            $sources[] = $model;
        }

        foreach ($sources as $source) {
            $notificationsWhereClauses[] = [
                'source_class' => $source->className(),
                'source_pk' => $source->id,
            ];

            $sourceWhere = ['object_model' => $source->className(), 'object_id' => $source->id];

            $notificationsWhereClauses[] = [
                'source_class' => Comment::class,
                'source_pk' => Comment::find()
                    ->select('id')
                    ->where($sourceWhere)
                    ->column(),
            ];

            $notificationsWhereClauses[] = [
                'source_class' => Mentioning::class,
                'source_pk' => Mentioning::find()
                    ->select('id')
                    ->where($sourceWhere)
                    ->column(),
            ];

            $notificationsWhereClauses[] = [
                'source_class' => Like::class,
                'source_pk' => Like::find()
                    ->select('id')
                    ->where($sourceWhere)
                    ->column(),
            ];
        }

        $notifications = Notification::find()
            ->where([
                'user_id' => $this->userId,
                'seen' => 0,
            ])
            ->andWhere([
                'OR',
                ...$notificationsWhereClauses
            ]);

        foreach ($notifications->each() as $notification) {
            /** @var Notification $notification */
            $notification->seen = 1;
            $notification->save();
        }
    }
}
