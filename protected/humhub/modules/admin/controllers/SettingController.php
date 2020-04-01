<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\libs\LogoImage;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use humhub\modules\admin\models\forms\CacheSettingsForm;
use humhub\modules\admin\models\forms\DesignSettingsForm;
use humhub\modules\admin\models\forms\FileSettingsForm;
use humhub\modules\admin\models\forms\LogsSettingsForm;
use humhub\modules\admin\models\forms\MailingSettingsForm;
use humhub\modules\admin\models\forms\OEmbedProviderForm;
use humhub\modules\admin\models\forms\ProxySettingsForm;
use humhub\modules\admin\models\forms\StatisticSettingsForm;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\web\pwa\widgets\SiteIcon;
use Yii;
use humhub\libs\Helpers;
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
            ['permissions' => ManageSettings::class]
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
        $form = new BasicSettingsForm();
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
        LogoImage::set(null);

        Yii::$app->response->format = 'json';
        return [];
    }

    /**
     * Delete Icon Image
     */
    public function actionDeleteIconImage()
    {
        $this->forcePostRequest();
        SiteIcon::set(null);
        return $this->asJson([]);
    }

    /**
     * Caching Options
     */
    public function actionCaching()
    {
        $form = new CacheSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->cache->flush();
            Yii::$app->assetManager->clear();
            Yii::$app->view->theme->variables->flushCache();
            $this->view->success(Yii::t('AdminModule.settings', 'Saved and flushed cache'));
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
        $form = new StatisticSettingsForm;
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
        $form = new MailingSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            return $this->redirect(['/admin/setting/mailing-server-test']);
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
            'transportTypes' => $transportTypes,
            'settings' => Yii::$app->settings
        ]);
    }

    public function actionMailingServerTest()
    {
        try {
            $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], [
                'message' => Yii::t('AdminModule.settings', 'Test message')
            ]);
            $mail->setTo(Yii::$app->user->getIdentity()->email);
            $mail->setSubject(Yii::t('AdminModule.settings', 'Test message'));

            if ($mail->send()) {
                $this->view->saved();
            } else {
                $this->view->error(Yii::t('AdminModule.settings', 'Could not send test email.'));
            }
        } catch (\Exception $e) {
            $this->view->error(Yii::t('AdminModule.settings', 'Could not send test email.') . ' ' . $e->getMessage());
        }

        return $this->redirect(['/admin/setting/mailing-server']);
    }


    public function actionDesign()
    {
        $form = new DesignSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/design'
            ]);
        }

        return $this->render('design', [
            'model' => $form,
        ]);
    }

    /**
     * File Settings
     */
    public function actionFile()
    {
        $form = new FileSettingsForm;
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

        return $this->render(
            'file',
            [
                'model' => $form,
                'maxUploadSize' => $maxUploadSize,
                'maxUploadSizeText' => $maxUploadSizeText,
            ]
        );
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        $form = new ProxySettingsForm;


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

        $form = new LogsSettingsForm;
        $limitAgeOptions = $form->options;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {

            $timeAgo = strtotime($form->logsDateLimit);
            Log::deleteAll(['<', 'log_time', $timeAgo]);
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.settings', 'Saved'));
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
        $form = new OEmbedProviderForm;

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
