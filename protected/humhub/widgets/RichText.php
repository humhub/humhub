<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\helpers\Html;
use humhub\models\UrlOembed;

/**
 * RichText dis
 *
 * @author luke
 */
class RichText extends \yii\base\Widget
{

    public $text = "";

    /**
     * @var boolean
     */
    public $encode = true;

    /**
     * @var boolean
     */
    public $minimal = false;

    /**
     * @var int
     */
    public $maxLength = 0;

    public function run()
    {
        if ($this->encode) {
            $this->text = Html::encode($this->text);
        }

        $maxOembedCount = 3; // Maximum OEmbeds
        $oembedCount = 0; // OEmbeds used

        $this->text = preg_replace_callback('/(https?:\/\/.*?)(\s|$)/i', function ($match) use (&$oembedCount, &$maxOembedCount) {

            // Try use oembed
            if ($maxOembedCount > $oembedCount) {
                $oembed = UrlOembed::GetOembed($match[0]);
                if ($oembed) {
                    $oembedCount++;
                    return $oembed;
                }
            }

            return Html::a($match[1], Html::decode($match[1]), array('target' => '_blank')) . $match[2];
        }, $this->text);


        // get user and space details from guids
        //$text = self::translateMentioning($text, true);
        // create image tag for emojis
        //$text = self::translateEmojis($text);

        return nl2br($this->text);
    }

    /**
     * Replace emojis from text to img tag
     *
     * @param string $text Contains the complete message
     * @param string $show show smilies or remove it (for activities and notifications)
     */
    public static function translateEmojis($text, $show = true)
    {
        $emojis = array('Ambivalent', 'Angry', 'Confused', 'Cool', 'Frown', 'Gasp', 'Grin', 'Heart', 'Hearteyes', 'Laughing', 'Naughty', 'Slant', 'Smile', 'Wink', 'Yuck');

        return preg_replace_callback('@;(.*?);@', function($hit) use(&$show, &$emojis) {
            if (in_array($hit[1], $emojis)) {
                if ($show) {
                    return Html::img(Yii::app()->baseUrl . '/img/emoji/' . $hit[1] . '.png', $hit[1], array('data-emoji-name' => $hit[0], 'class' => 'atwho-emoji'));
                }
                return '';
            }
            return $hit[0];
        }, $text);
    }

    /**
     * Translate guids from users to username
     *
     * @param strint $text Contains the complete message
     * @param boolean $buildAnchors Wrap the username with a link to the profile, if it's true
     */
    public static function translateMentioning($text, $buildAnchors = true)
    {
        return preg_replace_callback('@\@\-([us])([\w\-]*?)($|\s|\.|")@', function($hit) use(&$buildAnchors) {
            if ($hit[1] == 'u') {
                $user = User::model()->findByAttributes(array('guid' => $hit[2]));
                if ($user !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $user->getProfileUrl() . '" target="_self" class="atwho-user" data-user-guid="@-u' . $user->guid . '">@' . CHtml::encode($user->getDisplayName()) . '</a></span>' . $hit[3];
                    }
                    return " @" . CHtml::encode($user->getDisplayName()) . $hit[3];
                }
            } elseif ($hit[1] == 's') {
                $space = Space::model()->findByAttributes(array('guid' => $hit[2]));
                if ($space !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $space->getUrl() . '" target="_self" class="atwho-user" data-user-guid="@-s' . $space->guid . '">@' . CHtml::encode($space->name) . '</a></span>' . $hit[3];
                    }
                    return " @" . CHtml::encode($space->name) . $hit[3];
                }
            }
            return $hit[0];
        }, $text);
    }

}
