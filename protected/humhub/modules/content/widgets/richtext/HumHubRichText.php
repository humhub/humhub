<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\libs\ParameterEvent;
use humhub\models\UrlOembed;
use humhub\modules\content\assets\LegacyRichTextAsset;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Html;

/**
 * Legacy rich text implementation.
 *
 * @deprecated since 1.3 this is the old rich text implementation which won't be maintained in the future.
 */
class HumHubRichText extends AbstractRichText
{
    public static $editorClass = HumHubRichTextEditor::class;
    public static $processorClass = HumHubRichTextProcessor::class;

    /**
     * @inheritdoc
     */
    public function run()
    {
        LegacyRichTextAsset::register($this->view);

        if ($this->encode) {
            $this->text = Html::encode($this->text);
        }

        if (!$this->minimal && !$this->edit) {
           $this->text = self::translateOembed($this->text, $this->markdown);
        }

        // get user and space details from guids
        $this->text = self::translateMentioning($this->text, ($this->minimal) ? false : true);

        // create image tag for emojis
        $this->text = self::translateEmojis($this->text, ($this->minimal) ? false : true);

        if ($this->maxLength != 0) {
            $this->text = \humhub\libs\Helpers::truncateText($this->text, $this->maxLength);
        }

        $this->text = trim($this->text);

        if (!$this->minimal) {
            $output = nl2br($this->text);
        } else {
            $output = $this->text;
        }

        // replace leading spaces with no break spaces to keep the text format
        $output = preg_replace_callback('/^( +)/m', function($m) {
            return str_repeat("&nbsp;", strlen($m[1]));
        }, $output);

        $this->trigger(self::EVENT_BEFORE_OUTPUT, new ParameterEvent(['output' => &$output]));

        return trim($output);
    }

    /**
     * Parses for links and checks if they are embedable by oembed.
     *
     * @param $text
     * @param $markdown
     * @return mixed
     */
    public static function translateOembed($text, $markdown)
    {
        $maxOembedCount = 3; // Maximum OEmbeds
        $oembedCount = 0; // OEmbeds used

        $pattern= <<<REGEXP
                    /(?(R) # in case of recursion match parentheses
				 \(((?>[^\s()]+)|(?R))*\)
			|      # else match a link with title
				(https?|ftp):\/\/(([^\s()]+)|(?R))+(?<![\.,:;\'"!\?\s])
			)/x
REGEXP;
        $text = preg_replace_callback($pattern, function ($match) use (&$oembedCount, &$maxOembedCount, &$markdown) {

            // Try use oembed
            if ($maxOembedCount > $oembedCount) {
                $oembed = UrlOembed::GetOEmbed($match[0]);
                if ($oembed) {
                    $oembedCount++;
                    return $oembed;
                }
            }

            $options = strpos($match[0], Yii::$app->settings->get('baseUrl')) === 0 ? [] : ['target' => '_blank', 'rel' => "noopener noreferrer"];

            // The markdown parser will parse the links by itself
            return ($markdown) ? $match[0] : Html::a($match[0], Html::decode($match[0]), $options);
        }, $text);

        // mark emails
        return preg_replace_callback('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})/', function ($match) {
            return Html::mailto($match[0]);
        }, $text);
    }

    /**
     * Replace emojis from text to img tag
     *
     * @param string $text Contains the complete message
     * @param string $show show smilies or remove it (for activities and notifications)
     */
    public static function translateEmojis($text, $show = true)
    {
        $emojis = [
            "Relaxed", "Yum", "Relieved", "Hearteyes", "Cool", "Smirk",
            "KissingClosedEyes", "StuckOutTongue", "StuckOutTongueWinkingEye", "StuckOutTongueClosedEyes", "Disappointed", "Frown",
            "ColdSweat", "TiredFace", "Grin", "Sob", "Gasp", "Gasp2",
            "Laughing", "Joy", "Sweet", "Satisfied", "Innocent", "Wink",
            "Ambivalent", "Expressionless", "Sad", "Slant", "Worried", "Kissing",
            "KissingHeart", "Angry", "Naughty", "Furious", "Cry", "OpenMouth",
            "Fearful", "Confused", "Weary", "Scream", "Astonished", "Flushed",
            "Sleeping", "NoMouth", "Mask", "Worried", "Smile", "Muscle",
            "Facepunch", "ThumbsUp", "ThumbsDown", "Beers", "Cocktail", "Burger",
            "PoultryLeg", "Party", "Cake", "Sun", "Fire", "Heart"
        ];

        return preg_replace_callback('@;(\w*?);@', function($hit) use(&$show, &$emojis) {
            if (in_array($hit[1], $emojis)) {
                if ($show) {
                    return Html::img(Yii::getAlias("@web-static/img/emoji/" . $hit[1] . ".svg"), ['data-emoji-name' => $hit[0], 'data-richtext-feature' => '', 'data-guid' => "@-emoji".$hit[0], 'class' => 'atwho-emoji', 'width' => '18', 'height' => '18', 'alt' => $hit[1]]);
                }
                return '';
            }
            return $hit[0];
        }, $text);
    }

    /**
     * Translate guids from users to username
     *
     * @param string $text Contains the complete message
     * @param boolean $buildAnchors Wrap the username with a link to the profile, if it's true
     */
    public static function translateMentioning($text, $buildAnchors = true)
    {
        return preg_replace_callback('@\@\-([us])([\w\-]*?)($|[\.,:;\'"!\?\s])@', function($hit) use(&$buildAnchors) {
            if ($hit[1] == 'u') {
                $user = User::findOne(['guid' => $hit[2]]);
                if ($user !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $user->getUrl() . '" target="_self" class="atwho-user" data-richtext-feature data-guid="@-u' . $user->guid . '">@' . Html::encode($user->getDisplayName()) . '&#x200b;</a></span>' . $hit[3];
                    }
                    return " @" . Html::encode($user->getDisplayName()) . $hit[3];
                }
            } elseif ($hit[1] == 's') {
                $space = Space::findOne(['guid' => $hit[2]]);

                if ($space !== null) {
                    if ($buildAnchors) {
                        return ' <span contenteditable="false"><a href="' . $space->getUrl() . '" target="_self" class="atwho-user" data-richtext-feature data-guid="@-s' . $space->guid . '">@' . Html::encode($space->name) . '&#x200b;</a></span>' . $hit[3];
                    }
                    return " @" . Html::encode($space->name) . $hit[3];
                }
            }
            return $hit[0];
        }, $text);
    }
}
