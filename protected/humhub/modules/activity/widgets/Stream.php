<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\widgets;

use yii\web\HttpException;

/**
 * ActivityStreamWidget shows an stream/wall of activities inside a sidebar.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class Stream extends \yii\base\Widget
{
    /**
     * Optional content container if this stream belongs to one
     *
     * @var \humhub\modules\content\models\ContentContainer|\humhub\modules\space\models\Space|\humhub\modules\user\models\User
     */
    public $contentContainer;

    /**
     * Path to Stream Action to use
     *
     * @var string
     */
    public $streamAction = '';

    /**
     * Init the activity stream widget
     */
    public function init()
    {
        parent::init();
        if ($this->streamAction === '') {
            throw new HttpException(500, 'You need to set the streamAction attribute to use this widget!');
        }
    }

    /**
     * Runs the activity widget
     */
    public function run()
    {
        $streamUrl = $this->getStreamUrl();
        $infoUrl = \yii\helpers\Url::to(['/activity/link/info', 'id' => '-id-']);

        return $this->render('activityStream', [
            'streamUrl' => $streamUrl,
            'infoUrl' => $infoUrl,
        ]);
    }

    protected function getStreamUrl()
    {
        $params = [
            'mode' => \humhub\modules\stream\actions\Stream::MODE_ACTIVITY,
        ];

        if ($this->contentContainer) {
            return $this->contentContainer->createUrl($this->streamAction, $params);
        }

        return \yii\helpers\Url::to(array_merge([$this->streamAction], $params));
    }

}
