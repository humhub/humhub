<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\widgets\AuthChoice;
use humhub\services\WellKnownService;
use humhub\widgets\bootstrap\Link;
use Yii;
use yii\authclient\OAuth1;
use yii\authclient\OAuth2;
use yii\authclient\OpenId;
use yii\base\Model;

/**
 * MobileSettingsForm
 *
 * @since 1.18.0
 */
class MobileSettingsForm extends Model
{
    public $enableLinkService;
    public $fileAssetLinks;
    public $fileAppleAssociation;
    public $whiteListedUrls;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $this->enableLinkService = $settingsManager->get('mailerLinkService');
        $this->fileAssetLinks = $settingsManager->get('fileAssetLinks');
        $this->fileAppleAssociation = $settingsManager->get('fileAppleAssociation');
        $this->whiteListedUrls = $settingsManager->getSerialized('whiteListedUrls');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['enableLinkService'], 'boolean'],
            [['fileAssetLinks', 'fileAppleAssociation'], 'string'],
            [['whiteListedUrls'], 'validateWhiteListedDomains'],
        ];
    }

    public function validateWhiteListedDomains($attribute, $params): void
    {
        if (!$this->$attribute) {
            return;
        }

        foreach ($this->whiteListedUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->addError($attribute, Yii::t('AdminModule.settings', 'Invalid URL format: {url}', ['url' => $url]));
                return;
            }
        }
    }

    public function getWhiteListedUrlsWithSso(): array
    {
        $urls = $this->whiteListedUrls;

        // Add SSO service provider domains to the whitelist if they are not already present
        $clients = (new AuthChoice())->getClients();
        foreach ($clients as $client) {
            if (!method_exists($client, 'buildAuthUrl')) { // OAuth2, OAuth1 and OpenId clients
                continue;
            }
            // Remove URL params
            $parts = parse_url($client->buildAuthUrl());
            $urls[] = $parts['scheme'] . '://' . $parts['host']
                . (isset($parts['port']) ? ':' . $parts['port'] : '')
                . ($parts['path'] ?? '');
        }

        return array_unique($urls);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'enableLinkService' => Yii::t('AdminModule.settings', 'Enable Link Redirection Service'),
            'fileAssetLinks' => Yii::t('AdminModule.settings', 'Well-known file {fileName}', [
                'fileName' => '"' . WellKnownService::getFileName('fileAssetLinks') . '"',
            ]),
            'fileAppleAssociation' => Yii::t('AdminModule.settings', 'Well-known file {fileName}', [
                'fileName' => '"' . WellKnownService::getFileName('fileAppleAssociation') . '"',
            ]),
            'whiteListedUrls' => Yii::t('AdminModule.settings', 'URLs to whitelist'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints(): array
    {
        return [
            'enableLinkService' => Yii::t('AdminModule.settings', 'In order for links to open in the app on mobile devices, rather than in the mobile browser, all links (e.g. notification emails) need to be routed through the HumHub proxy server.'),
            'fileAssetLinks' => Yii::t('AdminModule.settings', 'URL to the file {fileNameLink}', [
                'fileNameLink' => Link::to(
                    WellKnownService::getFileName('fileAssetLinks'),
                    WellKnownService::getFileRoute('fileAssetLinks'),
                )->target('_blank'),
            ]),
            'fileAppleAssociation' => Yii::t('AdminModule.settings', 'URL to the file {fileNameLink}', [
                'fileNameLink' => Link::to(
                    WellKnownService::getFileName('fileAppleAssociation'),
                    WellKnownService::getFileRoute('fileAppleAssociation'),
                )->target('_blank'),
            ]),
            'whiteListedUrls' => Yii::t('AdminModule.settings', 'List of URLs that should be opened in the mobile in-app browser instead of the external one, and to be recognized as part of the HumHub ecosystem by the mobile app. Add * to the end of the URL to match all sub-paths. Example: https://example.com/*'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settingsManager = Yii::$app->settings;
        $settingsManager->set('mailerLinkService', $this->enableLinkService);
        $settingsManager->set('fileAssetLinks', $this->fileAssetLinks);
        $settingsManager->set('fileAppleAssociation', $this->fileAppleAssociation);
        $settingsManager->setSerialized('whiteListedUrls', $this->whiteListedUrls);

        return true;
    }
}
