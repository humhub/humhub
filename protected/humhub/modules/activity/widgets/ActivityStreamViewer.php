<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\stream\widgets\StreamViewer;
use humhub\modules\stream\actions\Stream as StreamAction;

/**
 * ActivityStreamWidget shows an stream/wall of activities inside a sidebar.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class ActivityStreamViewer extends StreamViewer
{
    public $id = 'activityStream';

    public $view = 'activityStream';

    public $streamAction = '/activity/stream/stream';

    public $init = true;

    public $jsWidget = 'activity.ActivityStream';

    public function getData()
    {
        return [
            'stream' => $this->getStreamUrl(),
            'stream-empty-message' => Yii::t('ActivityModule.widgets_views_activityStream', 'There are no activities yet.'),
        ];
    }

    protected function getStreamUrl()
    {
        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction);
        }

        return Url::to(array_merge([$this->streamAction]));
    }

}
