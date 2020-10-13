<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.org/licences
 */

namespace humhub\modules\activity\widgets;

use humhub\modules\stream\widgets\StreamViewer;
use Yii;
use yii\helpers\Url;

/**
 * ActivityStreamWidget shows an stream/wall of activities inside a sidebar.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class ActivityStreamViewer extends StreamViewer
{
    /**
     * @inheritDoc
     */
    public $streamFilterNavigation = null;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'id' => 'activityStream',
            'view' => 'activityStream',
            'streamAction' => '/activity/stream/stream',
            'jsWidget' => 'activity.ActivityStream',
            'options' => ['class' => 'panel-body', 'style' => 'padding:0px']
        ];

        parent::__construct(array_merge($defaults, $config));
    }

    public function getData()
    {
        return [
            'stream' => $this->getStreamUrl(),
            'stream-empty-message' => Yii::t('ActivityModule.base', 'There are no activities yet.'),
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
