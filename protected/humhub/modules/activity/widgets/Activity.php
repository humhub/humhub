<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\widgets;

use humhub\modules\activity\components\ActivityWebRenderer;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity as ActivityModel;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use Yii;
use yii\base\Exception;

/**
 * ActivityWidget shows an activity.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Activity extends StreamEntryWidget
{

    /**
     * @var ActivityModel is the current activity object.
     */
    public $model;

    /**
     * @inheritDoc
     */
    public $rootElement = 'li';

    /**
     * @inheritDoc
     */
    public $jsWidget = 'activity.ActivityStreamEntry';

    /**
     * @return string rendered wall entry body without the layoutRoot wrapper
     * @throws Exception
     */
    protected function renderBody()
    {
        $cacheKey = 'activity_wall_out_' . Yii::$app->language . '_' . $this->id;

        $activity = $this->model->getActivityBaseClass();

        $output = '';

        if ($activity instanceof BaseActivity) {
            $renderer = new ActivityWebRenderer();
            $output = $renderer->render($activity);
            Yii::$app->cache->set($cacheKey, $output);
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'activity-entry'
        ];
    }
}
