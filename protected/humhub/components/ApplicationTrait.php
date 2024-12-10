<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\helpers\DatabaseHelper;
use humhub\helpers\EnvHelper;
use humhub\interfaces\MailerInterface;
use humhub\libs\DynamicConfig;
use humhub\libs\SelfTest;
use humhub\libs\TimezoneHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\i18n\Formatter;

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

    private function initLocales(): void
    {
        if ($this->isDatabaseInstalled(true)) {
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
     * @return bool
     * @since 1.16
     */
    public function isInstalled(): bool
    {
        return isset(Yii::$app->params['installed']) && Yii::$app->params['installed'];
    }

    /**
     * Sets application in installed state (disables installer)
     *
     * @since 1.16
     */
    public function setInstalled()
    {
        $config = DynamicConfig::load();
        $config['params']['installed'] = true;
        DynamicConfig::save($config);
    }


    /**
     * Checks if settings table exists or application is not installed yet
     *
     * @since 1.16
     */
    public function isDatabaseInstalled(bool $checkConnection = false): bool
    {
        $dieOnError = $this->params['databaseInstalled'] ?? null;

        if (!$checkConnection && $dieOnError !== null) {
            return $dieOnError;
        }

        try {
            $db = Yii::$app->db;
            $db->open();
        } catch (\Exception $ex) {
            if ($dieOnError) {
                DatabaseHelper::handleConnectionErrors($ex);
            }
            return false;
        }

        return Yii::$app->params['databaseInstalled'] = in_array('setting', $db->schema->getTableNames());
    }

    /**
     * Sets the application database in installed state
     *
     * @since 1.16
     */
    public function setDatabaseInstalled()
    {
        $config = DynamicConfig::load();
        $config['params']['databaseInstalled'] = true;
        DynamicConfig::save($config);
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
