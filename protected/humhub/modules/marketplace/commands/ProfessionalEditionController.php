<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\commands;

use humhub\components\SettingsManager;
use humhub\modules\marketplace\components\LicenceManager;
use humhub\modules\marketplace\models\Licence;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Professional Edition CLI
 *
 * @property \humhub\modules\marketplace\Module $module
 * @since 1.8
 */
class ProfessionalEditionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        Yii::$app->moduleManager->flushCache();
        return parent::beforeAction($action);
    }

    /**
     * Displays the current registration details.
     *
     * @throws \yii\base\Exception
     */
    public function actionInfo()
    {
        $l = LicenceManager::get();

        if ($l->type === Licence::LICENCE_TYPE_PRO) {
            /** @var SettingsManager $settings */
            $settings = Yii::$app->getModule('marketplace')->settings;

            $this->stdout(Yii::t('MarketplaceModule.base', "PROFESSIONAL EDITION")."\n\n", Console::FG_GREY, Console::BOLD);
            $this->stdout('Licenced to: ' . $l->licencedTo . "\n");
            $this->stdout('Maximum users: ' . $l->maxUsers . "\n");
            $this->stdout('Last update: ' . Yii::$app->formatter->asDatetime($settings->get(LicenceManager::SETTING_KEY_PE_LAST_FETCH)) . "\n");
        } else {
            $this->stdout(Yii::t('MarketplaceModule.base', "\nNo active Professional Edition license found!\n"), Console::FG_RED, Console::BOLD);
        }
        $this->stdout("\n\n");
    }

    /**
     * Registers a Professional Edition using a license key.
     *
     * @param string the licence key
     * @throws \yii\base\Exception
     */
    public function actionRegister($licence)
    {
        $model = $this->getMarketplaceModule()->getLicence();
        $model->licenceKey = $licence;
        if ($model->register()) {
            LicenceManager::fetch();
            $this->stdout(Yii::t('MarketplaceModule.base', "\nThe license was successfully activated!\n\n"), Console::FG_GREEN, Console::BOLD);
            $this->actionInfo();
            return;
        } else {
            $this->stdout(Yii::t('MarketplaceModule.base', "\nThe license could not be activated:\n"), Console::FG_RED, Console::BOLD);
            foreach ($model->getErrors() as $attribute => $errors) {
                print "- " . implode(', ', $errors);
            }
        }
        $this->stdout("\n\n");
    }

    /**
     * Updates the license status via the license server
     */
    public function actionUpdate()
    {
        LicenceManager::get(false);
        LicenceManager::fetch();
        return $this->actionInfo();
    }

    /**
     * Removes the Professional Edition registration.
     */
    public function actionUnregister()
    {
        LicenceManager::remove();
        $this->stdout(Yii::t('MarketplaceModule.base', "\nThe license was successfully removed!\n\n"), Console::FG_GREEN, Console::BOLD);
    }

    /**
     * @return \humhub\modules\marketplace\Module
     */
    private function getMarketplaceModule()
    {
        return Yii::$app->getModule('marketplace');
    }

}
