<?php
namespace humhub\modules\ui\icon\widgets;

use humhub\components\Widget;
use Yii;
use humhub\libs\Html;
use humhub\modules\ui\icon\components\IconProvider;
use humhub\modules\ui\icon\components\IconFactory;

/**
 * The Icon widget is used as abstraction layer for rendering icons.
 *
 * This class only holds the icon definition as icon name, size and color and will forward the
 * actual rendering to an IconProvider through an IconFactory.
 *
 * It is possible to define own IconProvider, see [[IconFactory]]
 *
 * Usage:
 *
 * ```php
 * // Simple Icon
 * Icon::get('myIcon');
 *
 * // Icon with color definition
 * Icon::get('myIcon', ['color' => 'danger']);
 *
 * // Use another icon lib
 * Icon::get('myIcon', ['lib' => 'myIconLib']);
 * ```
 *
 * @see IconFactory
 * @see IconProvider
 * @since 1.4
 */
class Icon extends Widget
{
    const SIZE_XS = 'xs';
    const SIZE_SM = 'sm';
    const SIZE_LG = 'lg';
    const SIZE_2x = '2x';
    const SIZE_3x = '3x';
    const SIZE_4x = '4x';
    const SIZE_5x = '5x';
    const SIZE_6x = '6x';
    const SIZE_7x = '7x';
    const SIZE_8x = '8x';
    const SIZE_9x = '9x';
    const SIZE_10x = '10x';

    /**
     * @var array contains all available names which should be supported by the main icon provider
     */
    public static $names = [
        'adjust',
        'adn',
        'align-center',
        'align-justify',
        'align-left',
        'align-right',
        'ambulance',
        'anchor',
        'android',
        'angellist',
        'angle-double-down',
        'angle-double-left',
        'angle-double-right',
        'angle-double-up',
        'angle-down',
        'angle-left',
        'angle-right',
        'angle-up',
        'apple',
        'archive',
        'area-chart',
        'arrow-circle-down',
        'arrow-circle-left',
        'arrow-circle-o-down',
        'arrow-circle-o-left',
        'arrow-circle-o-right',
        'arrow-circle-o-up',
        'arrow-circle-right',
        'arrow-circle-up',
        'arrow-down',
        'arrow-left',
        'arrow-right',
        'arrow-up',
        'arrows',
        'arrows-alt',
        'arrows-h',
        'arrows-v',
        'asterisk',
        'at',
        'backward',
        'ban',
        'bank',
        'bar-chart',
        'bar-chart-o',
        'barcode',
        'bars',
        'bed',
        'beer',
        'behance',
        'behance-square',
        'bell',
        'bell-o',
        'bell-slash',
        'bell-slash-o',
        'bicycle',
        'binoculars',
        'birthday-cake',
        'bitbucket',
        'bitbucket-square',
        'bitcoin',
        'bold',
        'bolt',
        'bomb',
        'book',
        'bookmark',
        'bookmark-o',
        'briefcase',
        'btc',
        'bug',
        'building',
        'building-o',
        'bullhorn',
        'bullseye',
        'bus',
        'buysellads',
        'cab',
        'calculator',
        'calendar',
        'calendar-o',
        'camera',
        'camera-retro',
        'car',
        'caret-down',
        'caret-left',
        'caret-right',
        'caret-square-o-down',
        'caret-square-o-left',
        'caret-square-o-right',
        'caret-square-o-up',
        'caret-up',
        'cart-arrow-down',
        'cart-plus',
        'cc',
        'cc-amex',
        'cc-discover',
        'cc-mastercard',
        'cc-paypal',
        'cc-stripe',
        'cc-visa',
        'certificate',
        'chain',
        'chain-broken',
        'check',
        'check-circle',
        'check-circle-o',
        'check-square',
        'check-square-o',
        'chevron-circle-down',
        'chevron-circle-left',
        'chevron-circle-right',
        'chevron-circle-up',
        'chevron-down',
        'chevron-left',
        'chevron-right',
        'chevron-up',
        'child',
        'circle',
        'circle-o',
        'circle-o-notch',
        'circle-thin',
        'clipboard',
        'clock-o',
        'close',
        'cloud',
        'cloud-download',
        'cloud-upload',
        'cny',
        'code',
        'code-fork',
        'codepen',
        'coffee',
        'cog',
        'cogs',
        'columns',
        'comment',
        'comment-o',
        'comments',
        'comments-o',
        'compass',
        'compress',
        'connectdevelop',
        'copy',
        'copyright',
        'credit-card',
        'crop',
        'crosshairs',
        'css3',
        'cube',
        'cubes',
        'cut',
        'cutlery',
        'dashboard',
        'dashcube',
        'database',
        'dedent',
        'delicious',
        'desktop',
        'deviantart',
        'diamond',
        'digg',
        'dollar',
        'dot-circle-o',
        'download',
        'dribbble',
        'dropbox',
        'drupal',
        'edit',
        'eject',
        'ellipsis-h',
        'ellipsis-v',
        'empire',
        'envelope',
        'envelope-o',
        'envelope-square',
        'eraser',
        'eur',
        'euro',
        'exchange',
        'exclamation',
        'exclamation-circle',
        'exclamation-triangle',
        'expand',
        'external-link',
        'external-link-square',
        'eye',
        'eye-slash',
        'eyedropper',
        'facebook',
        'facebook-f',
        'facebook-official',
        'facebook-square',
        'fast-backward',
        'fast-forward',
        'fax',
        'female',
        'fighter-jet',
        'file',
        'file-archive-o',
        'file-audio-o',
        'file-code-o',
        'file-excel-o',
        'file-image-o',
        'file-movie-o',
        'file-o',
        'file-pdf-o',
        'file-photo-o',
        'file-picture-o',
        'file-powerpoint-o',
        'file-sound-o',
        'file-text',
        'file-text-o',
        'file-video-o',
        'file-word-o',
        'file-zip-o',
        'files-o',
        'film',
        'filter',
        'fire',
        'fire-extinguisher',
        'flag',
        'flag-checkered',
        'flag-o',
        'flash',
        'flask',
        'flickr',
        'floppy-o',
        'folder',
        'folder-o',
        'folder-open',
        'folder-open-o',
        'font',
        'forumbee',
        'forward',
        'foursquare',
        'frown-o',
        'futbol-o',
        'gamepad',
        'gavel',
        'gbp',
        'ge',
        'gear',
        'gears',
        'genderless',
        'gift',
        'git',
        'git-square',
        'github',
        'github-alt',
        'github-square',
        'gittip',
        'glass',
        'globe',
        'google',
        'google-plus',
        'google-plus-square',
        'google-wallet',
        'graduation-cap',
        'gratipay',
        'group',
        'h-square',
        'hacker-news',
        'hand-o-down',
        'hand-o-left',
        'hand-o-right',
        'hand-o-up',
        'hdd-o',
        'header',
        'headphones',
        'heart',
        'heart-o',
        'heartbeat',
        'history',
        'home',
        'hospital-o',
        'hotel',
        'html5',
        'ils',
        'image',
        'inbox',
        'indent',
        'info',
        'info-circle',
        'inr',
        'instagram',
        'institution',
        'ioxhost',
        'italic',
        'joomla',
        'jpy',
        'jsfiddle',
        'key',
        'keyboard-o',
        'krw',
        'language',
        'laptop',
        'lastfm',
        'lastfm-square',
        'leaf',
        'leanpub',
        'legal',
        'lemon-o',
        'level-down',
        'level-up',
        'life-bouy',
        'life-buoy',
        'life-ring',
        'life-saver',
        'lightbulb-o',
        'line-chart',
        'link',
        'linkedin',
        'linkedin-square',
        'linux',
        'list',
        'list-alt',
        'list-ol',
        'list-ul',
        'location-arrow',
        'lock',
        'long-arrow-down',
        'long-arrow-left',
        'long-arrow-right',
        'long-arrow-up',
        'magic',
        'magnet',
        'mail-forward',
        'mail-reply',
        'mail-reply-all',
        'male',
        'map-marker',
        'mars',
        'mars-double',
        'mars-stroke',
        'mars-stroke-h',
        'mars-stroke-v',
        'maxcdn',
        'meanpath',
        'medium',
        'medkit',
        'meh-o',
        'mercury',
        'microphone',
        'microphone-slash',
        'minus',
        'minus-circle',
        'minus-square',
        'minus-square-o',
        'mobile',
        'mobile-phone',
        'money',
        'moon-o',
        'mortar-board',
        'motorcycle',
        'music',
        'navicon',
        'neuter',
        'newspaper-o',
        'openid',
        'outdent',
        'pagelines',
        'paint-brush',
        'paper-plane',
        'paper-plane-o',
        'paperclip',
        'paragraph',
        'paste',
        'pause',
        'paw',
        'paypal',
        'pencil',
        'pencil-square',
        'pencil-square-o',
        'phone',
        'phone-square',
        'photo',
        'picture-o',
        'pie-chart',
        'pied-piper',
        'pied-piper-alt',
        'pinterest',
        'pinterest-p',
        'pinterest-square',
        'plane',
        'play',
        'play-circle',
        'play-circle-o',
        'plug',
        'plus',
        'plus-circle',
        'plus-square',
        'plus-square-o',
        'power-off',
        'print',
        'puzzle-piece',
        'qq',
        'qrcode',
        'question',
        'question-circle',
        'quote-left',
        'quote-right',
        'ra',
        'random',
        'rebel',
        'recycle',
        'reddit',
        'reddit-square',
        'refresh',
        'remove',
        'renren',
        'reorder',
        'repeat',
        'reply',
        'reply-all',
        'retweet',
        'rmb',
        'road',
        'rocket',
        'rotate-left',
        'rotate-right',
        'rouble',
        'rss',
        'rss-square',
        'rub',
        'ruble',
        'rupee',
        'save',
        'scissors',
        'search',
        'search-minus',
        'search-plus',
        'sellsy',
        'send',
        'send-o',
        'server',
        'share',
        'share-alt',
        'share-alt-square',
        'share-square',
        'share-square-o',
        'shekel',
        'sheqel',
        'shield',
        'ship',
        'shirtsinbulk',
        'shopping-cart',
        'sign-in',
        'sign-out',
        'signal',
        'simplybuilt',
        'sitemap',
        'skyatlas',
        'skype',
        'slack',
        'sliders',
        'slideshare',
        'smile-o',
        'soccer-ball-o',
        'sort',
        'sort-alpha-asc',
        'sort-alpha-desc',
        'sort-amount-asc',
        'sort-amount-desc',
        'sort-asc',
        'sort-desc',
        'sort-down',
        'sort-numeric-asc',
        'sort-numeric-desc',
        'sort-up',
        'soundcloud',
        'space-shuttle',
        'spinner',
        'spoon',
        'spotify',
        'square',
        'square-o',
        'stack-exchange',
        'stack-overflow',
        'star',
        'star-half',
        'star-half-empty',
        'star-half-full',
        'star-half-o',
        'star-o',
        'steam',
        'steam-square',
        'step-backward',
        'step-forward',
        'stethoscope',
        'stop',
        'street-view',
        'strikethrough',
        'stumbleupon',
        'stumbleupon-circle',
        'subscript',
        'subway',
        'suitcase',
        'sun-o',
        'superscript',
        'support',
        'table',
        'tablet',
        'tachometer',
        'tag',
        'tags',
        'tasks',
        'taxi',
        'tencent-weibo',
        'terminal',
        'text-height',
        'text-width',
        'th',
        'th-large',
        'th-list',
        'thumb-tack',
        'thumbs-down',
        'thumbs-o-down',
        'thumbs-o-up',
        'thumbs-up',
        'ticket',
        'times',
        'times-circle',
        'times-circle-o',
        'tint',
        'toggle-off',
        'toggle-on',
        'train',
        'transgender',
        'transgender-alt',
        'trash',
        'trash-o',
        'tree',
        'trello',
        'trophy',
        'truck',
        'try',
        'tty',
        'tumblr',
        'tumblr-square',
        'twitch',
        'twitter',
        'twitter-square',
        'umbrella',
        'underline',
        'undo',
        'university',
        'unlock',
        'unlock-alt',
        'upload',
        'usd',
        'user',
        'user-md',
        'user-plus',
        'user-secret',
        'user-times',
        'users',
        'venus',
        'venus-double',
        'venus-mars',
        'viacoin',
        'video-camera',
        'vimeo-square',
        'vine',
        'vk',
        'volume-down',
        'volume-off',
        'volume-up',
        'weibo',
        'weixin',
        'whatsapp',
        'wheelchair',
        'wifi',
        'windows',
        'wordpress',
        'wrench',
        'xing',
        'xing-square',
        'yahoo',
        'yelp',
        'youtube',
        'youtube-play',
        'youtube-square'
    ];

    /**
     * @var string icon name
     */
    public $name;

    /**
     * @var int icon size in pixel
     */
    public $size;

    /**
     * @var bool right float
     */
    public $right = false;

    /**
     * @var bool left float
     */
    public $left = false;

    /**
     * @var bool used to vertical alignment of icons;
     */
    public $fixedWidth = false;

    /**
     * @var bool used for icon list items
     */
    public $listItem = false;

    /**
     * @var bool bordered icon
     */
    public $border = false;

    /**
     * Set this to true if the icon is only used for decoration and is not required for navigating your site.
     * @var bool used for accessibility, set this to true if the icon is just used as decoration and
     */
    public $ariaHidden = false;

    /**
     * @var array
     */
    public $htmlOptions = [];

    /**
     * @var string css color
     */
    public $color;

    /**
     * @var string explicitly define a icon library, if not defined the default icon provider is used
     */
    public $lib;


    /**
     * Can be used to get an Icon instance from an unknown format.
     *
     * The following formats are  supported:
     *
     * ```php
     * // Will just return the given $instance
     * Icon::get($instance);
     *
     * // Will overwrite the instance configuration and return the given $instane
     * Icon::get($instance, $someOptions);
     *
     *
     * // Will create an instance with the given icon name and options
     * Icon::get('tasks', $someOptoins);
     *
     *
     * // Will create an instance from the given options array
     * Icon::get(['name' => 'tasks', color => 'success']);
     * ```
     * @param $icon
     * @param array $options
     * @return Icon|null|object
     */
    public static function get($icon, $options = [])
    {
        if($icon instanceof static) {
            return Yii::configure($icon, $options);
        } else if(is_string($icon)) {
            $options['name'] = $icon;
            return new Icon($options);
        } else if(is_array($icon)) {
            return new Icon($icon);
        }

        return null;
    }

    /**
     * Returns all supported icon names of a provider-
     *
     * @param null $providerId
     * @return string[]
     * @see IconFactory::getNames()
     * @throws \yii\base\InvalidConfigException
     */
    public static function getNames($providerId = null)
    {
        return IconFactory::getInstance()->getNames($providerId);
    }

    /**
     * Renders a icon list e.g.:
     *
     * ```php
     * Icon::renderList([
     *     ['tasks' => 'First list item', 'options' => ['color' => 'success']],
     *     ['book' => 'First second item', 'options' => ['color' => 'danger']]
     * ])
     * ```
     *
     * @param $listDefinition
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function renderList($listDefinition)
    {
        return IconFactory::getInstance()->renderList($listDefinition);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        /**
         * Catch for legacy icon usage
         */
        $this->name = (strpos($this->name, 'fa-') === 0)
            ? substr($this->name, 3, strlen($this->name))
            : $this->name;

        if($this->color) {
            switch($this->color) {
                case 'default':
                    $this->color = $this->view->theme->variable('default');
                    break;
                case 'primary':
                    $this->color = $this->view->theme->variable('primary');
                    break;
                case 'info':
                    $this->color = $this->view->theme->variable('info');
                    break;
                case 'success':
                    $this->color = $this->view->theme->variable('success');
                    break;
                case 'warning':
                case 'warn':
                    $this->color = $this->view->theme->variable('warn');
                    break;

                case 'error':
                case 'danger':
                    $this->color = $this->view->theme->variable('danger');
                    break;

            }
        }

        if($this->getId(false)) {
            $this->htmlOptions['id'] = $this->id;
        }

        return IconFactory::getInstance()->render($this);
    }

    /**
     * @param $size string
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function fixedWith($active = true)
    {
        $this->fixedWidth = $active;
        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function listItem($active = true)
    {
        $this->listItem;
        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function right($active = true)
    {
        if($active) {
            $this->left(false);
        }

        $this->right = $active;
        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function left($active = true)
    {
        if($active) {
            $this->right(false);
        }

        $this->left = $active;
        return $this;
    }

    /**
     * @param bool $active
     */
    public function ariaHidden($active = true)
    {
        $this->ariaHidden = $active;
        return $this;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function border($active = true)
    {
        $this->border = $active;
        return $this;
    }

    /**
     * @param string|array $style
     */
    public function style($style)
    {
        Html::addCssStyle($this->htmlOptions, $style);
        return $this;
    }

    /**
     * @param string $color
     */
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param $lib
     * @return $this
     */
    public function lib($lib)
    {
        $this->lib = $lib;
        return $this;
    }

    /**
     * @return [] array representation of this icon
     */
    public function asArray()
    {
        return [
            'id' => $this->getId(false),
            'name' => $this->name,
            'size' => $this->size,
            'fixedWidth' => $this->fixedWidth,
            'listItem' => $this->listItem,
            'right' => $this->right,
            'left' => $this->left,
            'ariaHidden' => $this->ariaHidden,
            'border' => $this->border,
            'htmlOptions' => $this->htmlOptions,
            'color' => $this->color,
            'lib' => $this->lib
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function asString()
    {
        return (string) $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString()
    {
        $result = $this::widget($this->asArray());

        return $result ? $result : '';
    }
}
