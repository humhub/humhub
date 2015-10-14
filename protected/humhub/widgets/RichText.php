<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
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

        if (!$this->minimal) {
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
        }

        // get user and space details from guids
        $this->text = self::translateMentioning($this->text, ($this->minimal) ? false : true);

        // create image tag for emojis
        $this->text = self::translateEmojis($this->text, ($this->minimal) ? false : true);

        if ($this->maxLength != 0) {
            $this->text = \humhub\libs\Helpers::truncateText($this->text, $this->maxLength);
        }
        
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
        $emojis = array(
            "1F601", "1F602", "1F603", "1F604", "1F605", "1F606",
            "1F607", "1F608", "1F609", "1F610", "1F611", "1F612",
            "1F613", "1F614", "1F615", "1F616", "1F617", "1F618",
            "1F619", "1F620", "1F621", "1F622", "1F623", "1F624",
            "1F625", "1F626", "1F627", "1F628", "1F629", "1F631",
            "1F632", "1F633", "1F634", "1F635", "1F636", "1F637",
            "1F641", "1F642", "1F60A", "1F60B", "1F60C", "1F60D",
            "1F60E", "1F60F", "1F61A", "1F61B", "1F61C", "1F61D",
            "1F61E", "1F61F", "1F62A", "1F62B", "1F62C", "1F62D",
            "1F62E", "1F62F", "1F44A", "1F592", "1F593", "2764",
            "1F389", "1F525", "1F37B", "1F382", "1F354", "1F355",
            "1F357", "1F56B", "1F575", "1F31E"
        );

        return preg_replace_callback('@;(.*?);@', function($hit) use(&$show, &$emojis) {
            if (in_array($hit[1], $emojis)) {
                if ($show) {
                    return Html::img(Yii::getAlias("@web/img/emoji/" . $hit[1] . ".svg"), array('data-emoji-name' => $hit[0], 'class' => 'atwho-emoji', 'width' => '18', 'height' => '18', 'alt' => $hit[1]));
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
                $user = \humhub\modules\user\models\User::findOne(['guid' => $hit[2]]);
                if ($user !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $user->getUrl() . '" target="_self" class="atwho-user" data-user-guid="@-u' . $user->guid . '">@' . Html::encode($user->getDisplayName()) . '</a></span>' . $hit[3];
                    }
                    return " @" . Html::encode($user->getDisplayName()) . $hit[3];
                }
            } elseif ($hit[1] == 's') {
                $space = \humhub\modules\space\models\Space::findOne(['guid' => $hit[2]]);

                if ($space !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $space->getUrl() . '" target="_self" class="atwho-user" data-user-guid="@-s' . $space->guid . '">@' . Html::encode($space->name) . '</a></span>' . $hit[3];
                    }
                    return " @" . Html::encode($space->name) . $hit[3];
                }
            }
            return $hit[0];
        }, $text);
    }

}
