<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\modules\notification\models\Notification;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\libs\Viewable;

/**
 * Name (SocialEvent/NetworkEvent/SocialActivity/BaseEvent)
 *
 * This class represents an social activity triggered within the network.
 * An activity instance can be linked to an $originator user, which performed the activity.
 * 
 * The activity mainly provides functions for rendering the output for different channels as
 * web, mail or plain-text.
 * 
 * @since 1.1
 * @author buddha
 */
abstract class SocialActivity extends Viewable
{

    /**
     * User which performed the activity.
     *
     * @var \humhub\modules\user\models\User
     */
    public $originator;

    /**
     * The source instance which created this activity
     *
     * @var \yii\db\ActiveRecord
     */
    public $source;

    /**
     * The content container this activity belongs to.
     * 
     * If the source object is a type of Content/ContentAddon or ContentContainer the container
     * will be automatically set.
     * 
     * @var ContentContainerActiveRecord
     */
    public $container = null;

    /**
     * @var string the module id which this activity belongs to (required)
     */
    public $moduleId = "";

    /**
     * The notification record this notification belongs to
     *
     * @var Notification
     */
    public $record;

    /**
     * @inheritdoc
     */
    protected function getViewParams($params = [])
    {
        $params['originator'] = $this->originator;
        $params['source'] = $this->source;
        $params['contentContainer'] = $this->container;
        $params['record'] = $this->record;
        if (!isset($params['url'])) {
            $params['url'] = $this->getUrl();
        }

        return $params;
    }

    /**
     * Url of the origin of this notification
     * If source is a Content / ContentAddon / ContentContainer this will automatically generated.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = '#';

        if ($this->source instanceof ContentActiveRecord || $this->source instanceof ContentAddonActiveRecord) {
            $url = $this->source->content->getUrl();
        } elseif ($this->source instanceof ContentContainerActiveRecord) {
            $url = $this->source->getUrl();
        }

        // Create absolute URL, for E-Mails
        if (substr($url, 0, 4) !== 'http') {
            $url = \yii\helpers\Url::to($url, true);
        }

        return $url;
    }

    /**
     * Build info text about a content
     *
     * This is a combination a the type of the content with a short preview
     * of it.
     *
     * @param Content $content
     * @return string
     */
    public function getContentInfo(\humhub\modules\content\interfaces\ContentTitlePreview $content)
    {
        return \yii\helpers\Html::encode($content->getContentName()) .
                ' "' .
                \humhub\widgets\RichText::widget(['text' => $content->getContentDescription(), 'minimal' => true, 'maxLength' => 60]) . '"';
    }

}
