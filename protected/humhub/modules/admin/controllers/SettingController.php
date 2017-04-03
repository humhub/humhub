<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\libs\Helpers;
use humhub\libs\ThemeHelper;
use humhub\models\UrlOembed;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\Log;
use humhub\modules\notification\models\forms\NotificationSettings;

/**
 * SettingController
 *
 * @since 0.5
 */
class SettingController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'basic' => Yii::t('AdminModule.base', 'Basic'),
            'caching' => Yii::t('AdminModule.base', 'Caching'),
            'statistic' => Yii::t('AdminModule.base', 'Statistics'),
            'mailing' => Yii::t('AdminModule.base', 'Mailing'),
            'mailing-server' => Yii::t('AdminModule.base', 'Mailing'),
            'design' => Yii::t('AdminModule.base', 'Design'),
            'security' => Yii::t('AdminModule.base', 'Security'),
            'file' => Yii::t('AdminModule.base', 'Files'),
            'cronjobs' => Yii::t('AdminModule.base', 'Cronjobs'),
            'proxy' => Yii::t('AdminModule.base', 'Proxy'),
            'oembed' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'oembed-edit' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'self-test' => Yii::t('AdminModule.base', 'Self test'),
        ]);
        $this->subLayout = '@admin/views/layouts/setting';

        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => \humhub\modules\admin\permissions\ManageSettings::className()]
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['basic']);
    }

    /**
     * Basic Settings
     */
    public function actionBasic()
    {
        $form = new \humhub\modules\admin\models\forms\BasicSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['/admin/setting/basic']);
        }

        return $this->render('basic', [
            'model' => $form
        ]);
    }

    /**
     * Deletes Logo Image
     */
    public function actionDeleteLogoImage()
    {
        $this->forcePostRequest();
        $image = new \humhub\libs\LogoImage();

        if ($image->hasImage()) {
            $image->delete();
        }

        Yii::$app->response->format = 'json';
        return [];
    }

    /**
     * Caching Options
     */
    public function actionCaching()
    {
        $form = new \humhub\modules\admin\models\forms\CacheSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->cache->flush();
            Yii::$app->assetManager->clear();
            $this->view->success(Yii::t('AdminModule.controllers_SettingController', 'Saved and flushed cache'));
            return $this->redirect(['/admin/setting/caching']);
        }

        return $this->render('caching', [
            'model' => $form,
            'cacheTypes' => $form->getTypes()
        ]);
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic()
    {
        $form = new \humhub\modules\admin\models\forms\StatisticSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/statistic'
            ]);
        }

        return $this->render('statistic', [
            'model' => $form
        ]);
    }

    /**
     * Notification Mailing Settings
     */
    public function actionNotification()
    {
        $form = new NotificationSettings();
        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
        }

        return $this->render('notification', [
            'model' => $form
        ]);
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailingServer()
    {
        $form = new \humhub\modules\admin\models\forms\MailingSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/mailing-server'
            ]);
        }

        $encryptionTypes = [
            '' => 'None',
            'ssl' => 'SSL',
            'tls' => 'TLS'
        ];
        $transportTypes = [
            'file' => 'File (Use for testing/development)',
            'php' => 'PHP',
            'smtp' => 'SMTP'
        ];

        return $this->render('mailing_server', [
            'model' => $form,
            'encryptionTypes' => $encryptionTypes,
            'transportTypes' => $transportTypes
        ]);
    }

    public function actionDesign()
    {
        $form = new \humhub\modules\admin\models\forms\DesignSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/design'
            ]);
        }

        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $this->render('design', [
            'model' => $form,
            'themes' => $themes,
            'logo' => new \humhub\libs\LogoImage()
        ]);
    }

    /**
     * File Settings
     */
    public function actionFile()
    {
        $form = new \humhub\modules\admin\models\forms\FileSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/file'
            ]);
        }

        // Determine PHP Upload Max FileSize
        $maxUploadSize = Helpers::getBytesOfIniValue(ini_get('upload_max_filesize'));
        $fileSizeKey = 'upload_max_filesize';
        if ($maxUploadSize > Helpers::getBytesOfIniValue(ini_get('post_max_size'))) {
            $maxUploadSize = Helpers::getBytesOfIniValue(ini_get('post_max_size'));
            $fileSizeKey = 'post_max_size';
        }

        $maxUploadSize = floor($maxUploadSize / 1024 / 1024);
        $maxUploadSizeText = "(" . $fileSizeKey . "): " . $maxUploadSize;

        // Determine currently used ImageLibary
        $currentImageLibrary = 'GD';
        if (Yii::$app->getModule('file')->settings->get('imageMagickPath')) {
            $currentImageLibrary = 'ImageMagick';
        }

        return $this->render(
            'file',
            [
                'model' => $form,
                'maxUploadSize' => $maxUploadSize,
                'maxUploadSizeText' => $maxUploadSizeText,
                'currentImageLibrary' => $currentImageLibrary,
            ]
        );
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        $form = new \humhub\modules\admin\models\forms\ProxySettingsForm;


        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect(['/admin/setting/proxy']);
        }

        return $this->render('proxy', ['model' => $form]);
    }

    /**
     * List of OEmbed Providers
     */
    public function actionOembed()
    {
        $providers = UrlOembed::getProviders();
        return $this->render('oembed',
        [
            'providers' => $providers
        ]);
    }

    public function actionLogs()
    {
        $logsCount = Log::find()->count();
        $dating = Log::find()
                ->orderBy('log_time', 'asc')
                ->limit(1)
                ->one();

        // I wish..
        if ($dating) {
            $dating = date('Y-m-d H:i:s', $dating->log_time);
        } else {
            $dating = "the begining of time";
        }

        $form = new \humhub\modules\admin\models\forms\LogsSettingsForm;
        $limitAgeOptions = $form->options;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {

            $timeAgo = strtotime($form->logsDateLimit);
            Log::deleteAll(['<', 'log_time', $timeAgo]);
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect([
                '/admin/setting/logs'
            ]);
        }

        return $this->render('logs', [
            'logsCount' => $logsCount,
            'model' => $form,
            'limitAgeOptions' => $limitAgeOptions,
            'dating' => $dating
        ]);
    }

    /**
     * Add or edit an OEmbed Provider
     */
    public function actionOembedEdit()
    {
        $form = new \humhub\modules\admin\models\forms\OEmbedProviderForm;

        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            $form->prefix = $prefix;
            $form->endpoint = $providers[$prefix];
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($prefix && isset($providers[$prefix])) {
                unset($providers[$prefix]);
            }
            $providers[$form->prefix] = $form->endpoint;
            UrlOembed::setProviders($providers);

            return $this->redirect(
            [
                '/admin/setting/oembed'
            ]);
        }

        return $this->render('oembed_edit',
        [
            'model' => $form,
            'prefix' => $prefix
        ]);
    }

    /**
     * Deletes OEmbed Provider
     */
    public function actionOembedDelete()
    {
        $this->forcePostRequest();
        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            unset($providers[$prefix]);
            UrlOembed::setProviders($providers);
        }
        return $this->redirect([
            '/admin/setting/oembed'
        ]);
    }

    public function actionAdvanced()
    {
        return $this->redirect(
        [
            'caching'
        ]);
    }

}
