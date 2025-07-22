<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\services\WellKnownService;
use humhub\widgets\Link;
use Yii;
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
    public $whiteListedDomains;

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
        $this->whiteListedDomains = $settingsManager->get('whiteListedDomains');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['enableLinkService'], 'boolean'],
            [['fileAssetLinks', 'fileAppleAssociation', 'whiteListedDomains'], 'string'],
            [['whiteListedDomains'], 'validateWhiteListedDomains'],
        ];
    }

    public function validateWhiteListedDomains($attribute, $params): void
    {
        if (!$this->$attribute) {
            return;
        }

        foreach ($this->getWhiteListedDomainsArray() as $domain) {
            if (!$domain) {
                continue;
            }
            if (!filter_var($domain, FILTER_VALIDATE_URL)) {
                $this->addError($attribute, Yii::t('AdminModule.settings', 'Invalid URL format: {domain}', ['domain' => $domain]));
                return;
            }
        }
    }

    public function getWhiteListedDomainsArray(): array
    {
        return array_map('trim', explode(',', $this->whiteListedDomains ?? ''));
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
            'whiteListedDomains' => Yii::t('AdminModule.settings', 'Domain URLs to whitelist, separated by comma'),
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
            'whiteListedDomains' => Yii::t('AdminModule.settings', 'E.g. URLs of the SSO service providers'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->whiteListedDomains = implode(',', $this->getWhiteListedDomainsArray());

        $settingsManager = Yii::$app->settings;
        $settingsManager->set('mailerLinkService', $this->enableLinkService);
        $settingsManager->set('fileAssetLinks', $this->fileAssetLinks);
        $settingsManager->set('fileAppleAssociation', $this->fileAppleAssociation);
        $settingsManager->set('whiteListedDomains', $this->whiteListedDomains);

        return true;
    }
}
