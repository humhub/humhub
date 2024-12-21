<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\helpers\EnvHelper;
use humhub\interfaces\MailerInterface;
use humhub\libs\SelfTest;
use humhub\libs\TimezoneHelper;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\i18n\Formatter;

/**
 * @property-read InstallationState $installationState
 */
trait ApplicationTrait
{
    /**
     * @var string|array the homepage url
     */
    protected $_homeUrl = null;

    /**
     * @var string Minimum PHP version that recommended to work without issues
     */
    public $minRecommendedPhpVersion;

    /**
     * @var string Minimum PHP version that may works but probably with small issues
     */
    public $minSupportedPhpVersion;

    /**
     * @readonly
     */
    public array $loadedAppConfig;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $config = EnvHelper::resolveConfigAliases($config);

        $this->loadedAppConfig = $config;

        $config = $this->removeLegacyConfigSettings($config);

        parent::__construct($config);

        $this->initLocales();
    }

    public function getInstallationState(): InstallationState
    {
        return InstallationState::instance();
    }

    private function initLocales(): void
    {
        if ($this->installationState->isDatabaseInstalled()) {
            if ($this->settings instanceof SettingsManager) {
                $this->timeZone = $this->settings->get('serverTimeZone', $this->timeZone);
                if ($this->formatter instanceof Formatter) {
                    $this->formatter->defaultTimeZone = $this->timeZone;
                }
            }
            $this->db->pdo->exec('SET time_zone = ' . $this->db->quoteValue(TimezoneHelper::convertToTime($this->timeZone)));
        }
    }

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl(): string
    {
        if ($this->_homeUrl === null) {
            return Url::to(['/dashboard/dashboard']);
        }

        if (is_array($this->_homeUrl)) {
            return Url::to($this->_homeUrl);
        }

        return $this->_homeUrl;
    }

    /**
     * @param string|array $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    public function getMailer(): MailerInterface
    {
        return parent::getMailer();
    }

    /**
     * Checks if Humhub is installed
     *
     * @todo check in modules
     * @return bool
     * @since 1.16
     */
    public function isInstalled(): bool
    {
        throw new NotSupportedException('This method is not supported anymore. Use InstallationState::hasState() instead.');
    }

    /**
     * Sets application in installed state (disables installer)
     *
     * @todo check in modules
     *
     * @since 1.16
     */
    public function setInstalled()
    {
        throw new NotSupportedException('This method is not supported anymore. Use InstallationState::setState() instead.');
    }


    /**
     * Checks if settings table exists or application is not installed yet
     *
     * @todo check in modules
     *
     * @since 1.16
     */
    public function isDatabaseInstalled(bool $checkConnection = false): bool
    {
        throw new NotSupportedException('This method is not supported anymore. Use InstallationState::hasState() instead.');
    }


    private function removeLegacyConfigSettings($applicationConfig)
    {
        return ArrayHelper::merge(
            [
                'modules' => [],
                'components' => [],
            ],
            $applicationConfig,
            SelfTest::getLegacyConfigSettings(),
        );
    }
}
