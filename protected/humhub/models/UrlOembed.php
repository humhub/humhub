<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models;

use yii\base\InvalidArgumentException;
use humhub\events\OembedFetchEvent;
use humhub\libs\RestrictedCallException;
use humhub\libs\UrlOembedClient;
use humhub\libs\UrlOembedHttpClient;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\db\ActiveRecord;
use Yii;

/**
 * UrlOembed records hold already loaded oembed previews.
 *
 * This class is used to fetch and save oembed results by means of the [[preload()]] and [[getOEmbed()]] methods.
 *
 * [[preload()]] can be used to preload oembed results for a given text string.
 *
 * [[getOEmbed()]] can be used to fetch a single oembed record for a given Url.
 *
 * All successfull results of `preload()` or `getOEmbed()` will be cached and saved in the `url_oembed` table.
 *
 * @property string $url
 * @property string $preview
 */
class UrlOembed extends ActiveRecord
{
    /**
     * @event
     * @since 1.4
     */
    const EVENT_FETCH = 'fetch';

    /**
     * @var int Maximum amount of remote fetch calls per request
     */
    public static $maxUrlFetchLimit = 5;

    /**
     * @var int Maximum amount of local db fetch calls per request
     */
    public static $maxUrlLoadLimit = 100;

    /**
     * @var int Counter for remote fetch calls
     */
    protected static $urlsFetched = 0;

    /**
     * @var int Counter for local db fetch calls
     */
    protected static $urlsLoaded = 0;

    /**
     * @var array Internal request cache
     */
    protected static $cache = [];

    /**
     * @var UrlOembedClient
     */
    protected static $client;


    /**
     * @var array Allowed oembed types
     */
    public static $allowed_types = ['video', 'rich', 'photo'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'url_oembed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'preview'], 'required'],
            [['preview'], 'string'],
            [['url'], 'string', 'max' => 180],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'url' => 'Url',
            'preview' => 'Preview',
        ];
    }

    /**
     * Returns the OEmbed API Url if exists
     *
     * @return string|null
     */
    public function getProviderUrl()
    {
        foreach (static::getProviders() as $providerBaseUrl => $providerAPI) {
            if (strpos($this->url, $providerBaseUrl) !== false) {
                return str_replace("%url%", urlencode($this->url), $providerAPI);
            }
        }
        return null;
    }

    /**
     * Flushes internal caches and fetch counters
     */
    public static function flush()
    {
        static::$cache = [];
        static::$urlsFetched = 0;
        static::$urlsLoaded = 0;
    }

    /**
     * Returns a OEmbed html string for a given $url or null in the following cases:
     *
     *  - There is no OEmbed provider available for the given url
     *  - The OEmbed provider does not return a valid response
     *  - A fetch counter restriction exceeded
     *
     * @param string $url
     * @return string|null
     */
    public static function getOEmbed($url)
    {
        $oembedFetchEvent = new OembedFetchEvent(['url' => $url]);
        (new UrlOembed())->trigger(static::EVENT_FETCH, $oembedFetchEvent);
        if ($result = $oembedFetchEvent->getResult()) {
            return $result;
        }

        try {
            $url = trim($url);

            if (static::hasOEmbedSupport($url)) {
                $urlOembed = static::findExistingOembed($url);
                $result = $urlOembed ? $urlOembed->preview : self::loadUrl($url);

                if (!empty($result)) {
                    return trim(preg_replace('/\s+/', ' ', $result));
                }
            }
        } catch (RestrictedCallException $re) {
            Yii::warning($re);
        }

        return null;
    }

    /**
     * Parses the given $text string for urls an saves new loaded OEmbed instances for.
     *
     * This method will only execute a remote fetch call if:
     *
     *  - There was a provider found for the given url
     *  - The same url has not been fetched before
     *  - The max fetch counters are not exceeded
     *
     * @param string $text
     */
    public static function preload($text)
    {
        preg_replace_callback('/http(.*?)(\s|$)/i', function ($match) {

            $url = trim($match[0]);

            if (!static::hasOEmbedSupport($url)) {
                return;
            }

            try {
                if (!static::findExistingOembed($url)) {
                    static::loadUrl($url);
                }
            } catch (RestrictedCallException $re) {
                Yii::warning($re);
            }
        }, $text);
    }

    /**
     * Checks if there is an existing UrlOembed record for the given $url.
     *
     * > Note: Results will be cached for this request if an record was found.
     *
     * @param $url
     * @return UrlOembed|null
     * @throws RestrictedCallException
     */
    protected static function findExistingOembed($url)
    {
        if (array_key_exists($url, static::$cache)) {
            return static::$cache[$url];
        }

        if (static::$urlsLoaded >= static::$maxUrlLoadLimit) {
            throw new RestrictedCallException('Max url db load limit exceeded.');
        }

        static::$urlsLoaded++;

        $record = static::findOne(['url' => $url]);

        if ($record) {
            static::$cache[$url] = $record;
        }

        return $record;
    }

    /**
     * Fetches the oembed result for a given $url and saves an UrlOembed record to the database.
     * A Remote fetch for new urls is only executed in case there is a related provider for the given url configured.
     *
     * @param string $url
     * @param string $customProviderUrl
     * @return string|null
     */
    public static function loadUrl($url, $customProviderUrl = '')
    {
        try {
            $urlOembed = static::findExistingOembed($url);

            if (!$urlOembed) {
                $urlOembed = new static(['url' => $url]);
            }

            $providerUrl = $customProviderUrl != '' ? $customProviderUrl : $urlOembed->getProviderUrl();
            if (empty($providerUrl)) {
                return null;
            }


            $data = static::fetchUrl($providerUrl);
            $html = static::buildHtmlPreview($url, $data);

            $urlOembed->preview = $html ?: '';
            $urlOembed->save();

            static::$cache[$url] = $urlOembed;
            return $html;
        } catch (RestrictedCallException $re) {
            Yii::warning($re);
        }


        return null;
    }

    /**
     * Builds the oembed preview html result in case the given $data array is valid.
     *
     * @param $url
     * @param []|null $data
     * @param UrlOembed $urlOembed
     * @return string|null
     */
    protected static function buildHtmlPreview($url, $data = null)
    {
        if (static::validateOembedResponse($data)) {
            return Html::tag('div', $data['html'], [
                'data' => [
                    'guid' => uniqid('oembed-', true),
                    'richtext-feature' => 1,
                    'oembed-provider' => Html::encode(static::getProviderByUrl($url)),
                    'url' => Html::encode($url)
                ],
                'class' => 'oembed_snippet',
            ]);
        }
        return null;
    }

    /**
     * Validates the given $data array.
     *
     * @param []|null $data
     * @return bool
     */
    protected static function validateOembedResponse($data = null)
    {
        return !empty($data) &&
            isset($data['html'], $data['type'])
            && in_array($data['type'], static::$allowed_types, true);
    }


    /**
     * Checks if a given URL Supports OEmbed
     *
     * @param string $url
     * @return boolean
     */
    public static function hasOEmbedSupport($url)
    {
        return static::getProviderByUrl($url) != null;
    }

    /**
     * @param $url
     * @return mixed|null
     */
    public static function getProviderByUrl($url)
    {
        foreach (static::getProviders() as $providerBaseUrl => $providerAPI) {
            if (strpos($url, $providerBaseUrl) !== false) {
                return $providerBaseUrl;
            }
        }

        return null;
    }

    /**
     * Executes the remote fetch call in case the [$maxUrlFetchLimit] is not reached.
     *
     * @param string $url
     * @return array|null
     * @throws RestrictedCallException
     */
    protected static function fetchUrl($url)
    {
        if (static::$urlsFetched >= static::$maxUrlFetchLimit) {
            throw new RestrictedCallException('Max url fetch limit exceeded.');
        }

        static::$urlsFetched++;

        return static::getClient()->fetchUrl($url);
    }

    /**
     * Returns the UrlOembedClient responsible for fetching OEmbed results.
     *
     * @return UrlOembedClient
     */
    public static function getClient()
    {
        if (!static::$client) {
            static::$client = new UrlOembedHttpClient();
        }

        return static::$client;
    }

    /**
     * Sets the UrlOembedClient responsible for fetching OEmbed results.
     *
     * @param null $client
     */
    public static function setClient($client = null)
    {
        static::$client = $client;
    }

    /**
     * Returns all available OEmbed providers
     *
     * @return array
     */
    public static function getProviders()
    {
        $providers = Yii::$app->settings->get('oembedProviders');
        if (!empty($providers)) {
            return Json::decode($providers);
        }

        return [];
    }

    /**
     * Saves an array of available OEmbed providers
     *
     * @param array $providers
     */
    public static function setProviders($providers)
    {
        Yii::$app->settings->set('oembedProviders', Json::encode($providers));
    }

}
