<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\fixtures;

use Yii;
use yii\test\ActiveFixture;

class SettingFixture extends ActiveFixture
{
    public $modelClass = 'humhub\models\Setting';

    /**
     * @inheritdoc
     */
    public function load()
    {
        parent::load();
        $this->reloadSettings();
    }

    /**
     * @inheritdoc
     */
    public function unload()
    {
        parent::unload();
        $this->reloadSettings();
    }

    /**
     * `ActiveFixture` writes the `setting` table via raw SQL, bypassing the `SettingsManager` and
     * therefore leaving its cache untouched. Reload the (base) settings so the fixture-loaded
     * values — including the `InstallationState` the app checks on every request — actually take
     * effect. Without this a reader that shares the settings cache (notably the acceptance test
     * server) keeps serving stale settings and, once the table has been reset, redirects every
     * request to the installer.
     */
    protected function reloadSettings(): void
    {
        if (Yii::$app->has('settings')) {
            Yii::$app->settings->reload();
        }
    }
}
