<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\console\WithoutModuleAutoload;
use humhub\components\InstallationState;
use humhub\modules\marketplace\models\Licence;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Shows the HumHub version and information about the installation.
 *
 * ```
 * yii version        # prints the version only, e.g. for scripts
 * yii version/info   # prints the version with further details
 * ```
 *
 * @since 1.20
 */
#[WithoutModuleAutoload]
class VersionController extends Controller
{
    /**
     * Prints the HumHub version.
     */
    public function actionIndex(): int
    {
        $this->stdout(Yii::$app->version . PHP_EOL);

        return ExitCode::OK;
    }

    /**
     * Prints the HumHub version with details about the installation and the environment.
     */
    public function actionInfo(): int
    {
        $info = $this->getInfo();
        $width = max(array_map('mb_strlen', array_keys($info))) + 2;

        foreach ($info as $label => $value) {
            $this->stdout(str_pad($label . ':', $width), Console::BOLD);
            $this->stdout($value . PHP_EOL);
        }

        return ExitCode::OK;
    }

    /**
     * Returns the details shown by `version/info`.
     *
     * Details that cannot be determined (e.g. without a database connection) are left out
     * or marked as unknown, so the command also works on a broken or not yet installed system.
     *
     * @return array<string, string> label => value
     */
    public function getInfo(): array
    {
        $installed = $this->isInstalled();

        $info = [
            'HumHub version' => Yii::$app->version,
        ];

        if ($installed) {
            $edition = $this->getEdition();
            if ($edition !== null) {
                $info['Edition'] = $edition;
            }
        }

        $info['Installed'] = $installed ? 'Yes' : 'No';
        $info['Debug mode'] = YII_DEBUG ? 'Enabled' : 'Disabled';
        $info['PHP version'] = PHP_VERSION;
        $info['Database'] = $this->getDatabaseInfo();

        foreach ($this->getDockerImageInfo() as $label => $value) {
            $info[$label] = $value;
        }

        return $info;
    }

    private function isInstalled(): bool
    {
        try {
            return Yii::$app->installationState->hasState(InstallationState::STATE_INSTALLED);
        } catch (\Throwable) {
            return false;
        }
    }

    private function getEdition(): ?string
    {
        $marketplace = Yii::$app->getModule('marketplace');
        if ($marketplace === null || !$marketplace->enabled) {
            return null;
        }

        try {
            return $marketplace->getLicence()->type === Licence::LICENCE_TYPE_PRO ? 'Professional' : 'Community';
        } catch (\Throwable) {
            return null;
        }
    }

    private function getDatabaseInfo(): string
    {
        $db = Yii::$app->db;

        try {
            return $db->getDriverName() . ' ' . $db->getServerVersion();
        } catch (\Throwable) {
            return 'Unknown (no connection)';
        }
    }

    /**
     * Returns the build information provided by the official HumHub Docker image
     * via the environment variables `HUMHUB_CONFIG__PARAMS__DOCKER__*`.
     *
     * @return array<string, string> label => value, empty when not running in the Docker image
     */
    private function getDockerImageInfo(): array
    {
        $params = Yii::$app->params['docker'] ?? null;
        if (!is_array($params)) {
            return [];
        }

        $labels = [
            'imageTag' => 'Docker image tag',
            'imagePlatform' => 'Docker image platform',
            'imageCreated' => 'Docker image build time',
            'imageRevision' => 'Docker repository commit',
            'coreRevision' => 'HumHub core commit',
        ];

        $info = [];
        foreach ($labels as $key => $label) {
            if (isset($params[$key]) && is_scalar($params[$key]) && (string) $params[$key] !== '') {
                $info[$label] = (string) $params[$key];
            }
        }

        return $info;
    }
}
