<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

use humhub\libs\EmojiMap;
use humhub\libs\Helpers;
use humhub\libs\Markdown;
use humhub\libs\ParameterEvent;
use humhub\modules\content\assets\ProseMirrorRichTextAsset;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Html;
use humhub\models\UrlOembed;

/**
 * The ProsemirrorRichText is a [Prosemirror](https://prosemirror.net) and [Markdown-it](https://github.com/markdown-it/markdown-it)
 * based rich text implementation.
 *
 * This rich text is a pure markdown based rich text enhanced with some additional features and markdown plugins.
 *
 * In order to stay compatible with the legacy rich text content, this rich text contains some pre-processing logic on the server side, which can be deactivated
 * if not required by means of the `richtextCompatMode` setting of the `content` module.
 *
 * Note that this rich text, when in edit mode, just outputs an invisible div with pure markdown content, which will be interpreted by
 * the related ProsemirrorRichTextEditor.
 *
 * This rich text implementation supports all features as [[preset]], the [[includes]] and [[excludes]] of plugins
 * and is extensible through additional javascript plugins.
 *
 * Note that the plugin settings as [[preset]], [[includes]], [[excludes]], [[pluginOptions]] have to be set for the editor as
 * well as for the rich text output widget.
 *
 * Beside the default (GFM based) markdown-it syntax, the following plugins are available:
 *
 * ### anchors
 *
 * If enabled will add anchors to heading elements. This plugin is disabled by default and can be enabled as follows:
 *
 * ```php
 * RichText::output($text, [
 *     'preset' => 'myPreset',
 *     'pluginOptions' => [
 *         'anchors' => true
 *     ]
 * ]);
 *
 * // or with specific settings
 * RichText::output($text, [
 *     'preset' => 'myPreset',
 *     'pluginOptions' => [
 *         'anchors' => ['permalink' => true]
 *     ]
 * ]);
 * ```
 * See [markdown-it-anchor](https://www.npmjs.com/package/markdown-it-anchor) for more settings.
 *
 * ### clipboard
 *
 * Allows pasting of raw markdown content into the richtext editor.
 *
 * ### emoji
 *
 * [twemoji](https://github.com/twitter/twemoji) and [markdown-it-emoji](https://www.npmjs.com/package/markdown-it-emoji) based emojies
 *
 * ### fullscreen
 *
 * Adds a enlarge/shrink button to the rich text editor.
 *
 * ### mention
 *
 * Markdown link extension for mentionings in the form of [<name>](mention:<guid> "<profile-url>").
 *
 * ### oembed
 *
 * Enables scanning and replacement of pasted oembed links in form of link extensions [<url>](oembed:url)
 *
 * ### placeholder
 *
 * Text placeholder for the editor input
 *
 * ### strikethrough
 *
 * Markdown strikethrough formatting.
 *
 * ### table
 *
 * Simple Markdown table support.
 *
 * ### upload
 *
 * File upload support.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @see https://github.com/humhub/humhub-prosemirror for more information about the prosemirror-richtext client implementation
 * @see https://prosemirror.net/docs/ Prosemirror documentation
 * @see https://github.com/markdown-it/markdown-it markdown-it repository
 * @since 1.3
 */
class ProsemirrorRichText extends AbstractRichText
{
    /**
     * @inheritdoc
     */
    public $jsWidget = 'ui.richtext.prosemirror.RichText';

    /**
     * @var array holds included oembeds used for rendering
     */
    private $oembeds = [];

    /**
     * @inheritdoc
     */
    protected static $editorClass = ProsemirrorRichTextEditor::class;

    /**
     * @inheritdoc
     */
    protected static $processorClass = ProsemirrorRichTextProcessor::class;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if($this->edit) {
            $this->visible = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function run() {
        if($this->minimal) {
            return $this->renderMinimal();
        }

        if($this->isCompatibilityMode()) {
            $this->text = RichTextCompatibilityParser::parse($this->text);
        }

        foreach (static::scanLinkExtension($this->text, 'oembed') as $match) {
            if(isset($match[3])) {
                $this->oembeds[$match[3]] = UrlOembed::GetOEmbed($match[3]);
            }
        }

        $this->text = $this->parseOutput();

        if ($this->maxLength > 0) {
            $this->text = Helpers::truncateText($this->text, $this->maxLength);
        }

        $this->content = Html::encode($this->text);
        $output = parent::run() . $this->buildOembedOutput();
        $this->trigger(self::EVENT_BEFORE_OUTPUT, new ParameterEvent(['output' => &$output]));

        return trim($output);

    }

    /**
     * @since v1.3.2
     */
    protected function parseOutput()
    {
        return static::parseMentionings($this->text, $this->edit);
    }

    /**
     * @return string truncated and stripped text
     */
    protected function renderMinimal() {
        $result = preg_replace('/\\\\(\n|\r){1,2}/',  ' ', $this->text);
        $result = strip_tags((new Markdown())->parse($result));
        $result = $this->toUTF8Emoji($result);
        return  Html::encode(($this->maxLength > 0) ? Helpers::truncateText($result, $this->maxLength) : $result);
    }

    protected function toUTF8Emoji($text)
    {
        // Note the ; was used in the legacy editor
        return preg_replace_callback('/[:|;](([A-Za-z0-9])+)[:|;]/', function($match)  {
            $result =  $match[0];

            if(isset($match[1])) {
                $result = array_key_exists(strtolower($match[1]), EmojiMap::MAP) ?  EmojiMap::MAP[strtolower($match[1])] : $result;
            }

            return $result;
        }, $text);
    }

    /**
     * @return string html extension holding the actual oembed dom nodes which will be embedded into the rich text
     */
    public function buildOembedOutput()
    {
        $result = '';
        foreach ($this->oembeds as $url => $oembed) {
            $result .= Html::tag('div', $oembed, ['data-oembed' => $url]);
        }

        return Html::tag('div', $result, ['class' => 'richtext-oembed-container', 'style' => 'display:none']);
    }

    /**
     * Parses the given text for mentionings and replaces them with possibly updated values (e.g. name).
     *
     * @param $text string rich text content to parse
     * @param $edit bool if not in edit mode deleted or inactive users will be rendered differently
     * @return mixed
     */
    public static function parseMentionings($text, $edit = false)
    {
        // $match[0]: markdown, $match[1]: name, $match[2]: extension(mention) $match[3]: guid, $match[4]: url
        return static::replaceLinkExtension($text, 'mention', function($match) use ($edit) {
            $contentContainer = ContentContainer::findOne(['guid' => $match[3]]);
            $notFoundResult = '['.$match[1].'](mention:'.$match[2].' "#")';

            if(!$contentContainer || !$contentContainer->getPolymorphicRelation()) {
                // If no user or space was found we leave out the url in the non edit mode.
                return $edit ?  '['.$match[1].'](mention:'.$match[3].' "'.$match[4].'")' : $notFoundResult;
            }

            $container = $contentContainer->getPolymorphicRelation();

            if($container instanceof User) {
                return $container->isActive()
                    ?  '['.$container->getDisplayName().'](mention:'.$container->guid.' "'.$container->getUrl().'")'
                    : $notFoundResult;
            }

            if($container instanceof Space) {
                return '['.$container->name.'](mention:'.$container->guid.' "'.$container->getUrl().'")';
            }

            return '';
        });
    }

    /**
     * Can be used to scan for link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param $text string rich text content to parse
     * @param $extension string|null extension string if not given all extension types will be included
     * @return array
     */
    public static function scanLinkExtension($text, $extension = null)
    {
        preg_match_all(static::getLinkExtensionPattern($extension), $text, $match, PREG_SET_ORDER);
        return $match;
    }

    /**
     * Can be used to scan and replace link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param $text string rich text content to parse
     * @param $extension string|null extension string if not given all extension types will be included
     * @return mixed
     */
    public static function replaceLinkExtension($text, $extension = null, $callback)
    {
        return preg_replace_callback(static::getLinkExtensionPattern($extension), $callback, $text);
    }

    /**
     * @param string $extension the extension to parse, if not set all extensions are included
     * @return string the regex pattern for a given extension or all extension if no specific extension string is given
     */
    protected static function getLinkExtensionPattern($extension = '[a-zA-Z]+')
    {
        return '/(?<!\\\\)\[([^\]]*)\]\(('.$extension.'):{1}([^\)\s]*)(?:\s")?([^\)"]*)?(?:")?[^\)]*\)/is';
    }

    /**
     * Checks if the compatibility mode is enabled.
     * The compatibility mode is only required, if old content is present and won't be activated for new installations.
     *
     * @return bool
     */
    public function isCompatibilityMode()
    {
        return Yii::$app->getModule('content')->settings->get('richtextCompatMode', 1);
    }
}
