<?php


namespace humhub\modules\content\widgets\richtext\extensions;


use humhub\components\ActiveRecord;
use humhub\models\UrlOembed;
use humhub\modules\content\Module;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\Html;

/**
 * This extension is used to support legacy richtext format (HumHub <1.3).
 *
 * The legacy format uses a different syntax for emoji, mentioning, oembed.
 *
 * This conversion can be deactivated either by module configuration or by module db setting `richtextCompatMode`.
 *
 * @package humhub\modules\content\widgets\richtext\extensions
 */
class RichTextCompatibilityExtension extends Model implements RichTextExtension
{
    /**
     * Content module db setting used to deactivate this feature
     */
    const DB_SETTING_KEY = 'richtextCompatMode';

    /**
     * @inheritdoc
     */
    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string
    {
        if(!$this->isCompatibilityMode()) {
            return $output;
        }

        $output = static::translateEmojis($output);
        $output = static::translateLinks($output);
        return static::translateMentionings($output);
    }

    /**
     * Replace emojis from text to img tag
     *
     * @param string $text Contains the complete message
     * @param string $show show smilies or remove it (for activities and notifications)
     * @return string
     */
    public static function translateEmojis(string $text) : string
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

        $emojiMapping = [
            'Cool' => 'sunglasses',
            'Hearteyes' => 'heart_eyes',
            'KissingClosedEyes' => 'kissing_closed_eyes',
            'StuckOutTongue' => 'stuck_out_tongue',
            'StuckOutTongueWinkingEye' => 'stuck_out_tongue_winking_eye',
            'StuckOutTongueClosedEyes' => 'stuck_out_tongue_closed_eyes',
            'Frown' => 'frowning_face',
            'ColdSweat' => 'cold_sweat',
            'TiredFace' => 'tired_face',
            'Gasp' => 'open_mouth',
            'Gasp2' => 'astonished',
            'Sweet' => 'grin',
            'Ambivalent' => 'neutral_face',
            'Sad' => 'disappointed',
            'Slant' => 'confused',
            'KissingHeart' => 'kissing_heart',
            'Naughty' => 'rage',
            'Furious' => 'angry',
            'OpenMouth' => 'open_mouth',
            'NoMouth' => 'no_mouth',
            'ThumbsUp' => 'thumbsup',
            'ThumbsDown' => 'thumbsdown',
            'Burger' => 'hamburger',
            'PoultryLeg' => 'poultry_leg',
            'Party' => 'beers',
            'sun' => 'sun_with_face'
        ];

        return preg_replace_callback('@;(\w*?);@', function ($hit) use (&$emojis, &$emojiMapping) {
            if (array_key_exists($hit[1], $emojiMapping)) {
                return ':' . $emojiMapping[$hit[1]] . ':';
            } elseif (in_array($hit[1], $emojis)) {
                return ':' . strtolower($hit[1]) . ':';
            }
            return $hit[0];
        }, $text);
    }

    /**
     * Translates links to either default markdown links or oembeds.
     *
     * @param $text
     * @return mixed
     */
    private static function translateLinks(string $text) : string
    {
        return preg_replace_callback('/(?<=^|\s)(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s\]\)\\"\'\<]{2,})(?=$|\s)/', function ($hit) {
            $url = $hit[0];
            return UrlOembed::getOEmbed($url) ? '[' . $url . '](oembed:' . $url . ')' : $url;
        }, $text);
    }

    /**
     * Translates old mentionings to new markdown based mentionings.
     *
     * @param $text
     * @return mixed
     */
    private static function translateMentionings(string $text) : string
    {
        return preg_replace_callback('@\@\-([us])([\w\-]*?)($|[\.,:;\'"!\?\s])@', function ($hit) {
            if ($hit[1] == 'u') {
                $container = User::findOne(['guid' => $hit[2]]);
                $name = ($container) ? $container->getDisplayName() : 'unknown';
            } else {
                $container = Space::findOne(['guid' => $hit[2]]);
                $name = ($container) ? $container->name : 'unknown';
            }

            if ($container === null) {
                return '';
            }

            return '[' . Html::encode($name) . '](mention:' . $hit[2] . ' "' . $container->getUrl() . '")';
        }, $text);
    }

    /**
     * Checks if the compatibility mode is enabled.
     * The compatibility mode is only required, if old content is present and won't be activated for new installations.
     *
     * @return bool
     */
    private function isCompatibilityMode()
    {
        /* @var $module Module  */
        $module = Yii::$app->getModule('content');
        return $module->richtextCompatMode && $module->settings->get(static::DB_SETTING_KEY, 1);
    }

    /**
     * @inheritdoc
     */
    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string
    {
        return $output;
    }

    /**
     * @inheritdoc
     */
    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function onAfterConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }
}
