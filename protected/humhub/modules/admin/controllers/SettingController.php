<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Exception;
use humhub\libs\LogoImage;
use humhub\models\UrlOembed;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\forms\AddTopicForm;
use humhub\modules\admin\models\forms\BasicSettingsForm;
use humhub\modules\admin\models\forms\CacheSettingsForm;
use humhub\modules\admin\models\forms\DesignSettingsForm;
use humhub\modules\admin\models\forms\FileSettingsForm;
use humhub\modules\admin\models\forms\LogsSettingsForm;
use humhub\modules\admin\models\forms\MailingSettingsForm;
use humhub\modules\admin\models\forms\OEmbedProviderForm;
use humhub\modules\admin\models\forms\OEmbedSettingsForm;
use humhub\modules\admin\models\forms\ProxySettingsForm;
use humhub\modules\admin\models\forms\StatisticSettingsForm;
use humhub\modules\admin\models\Log;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\notification\models\forms\NotificationSettings;
use humhub\modules\topic\models\Topic;
use humhub\modules\user\models\User;
use humhub\modules\web\pwa\widgets\SiteIcon;
use humhub\widgets\ModalClose;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\web\NotFoundHttpException;

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
            'permissions' => Yii::t('AdminModule.base', 'Permissions'),
            'file' => Yii::t('AdminModule.base', 'Files'),
            'cronjobs' => Yii::t('AdminModule.base', 'Cronjobs'),
            'proxy' => Yii::t('AdminModule.base', 'Proxy'),
            'oembed' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'oembed-edit' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'self-test' => Yii::t('AdminModule.base', 'Self test'),
        ]);
        $this->subLayout = '@admin/views/layouts/setting';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageSettings::class],
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
            'model' => $form,
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
        $form = new CacheSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->success(Yii::t('AdminModule.settings', 'Saved and flushed cache'));
            return $this->redirect(['/admin/setting/caching']);
        }

        return $this->render('caching', [
            'model' => $form,
            'cacheTypes' => $form->getTypes(),
        ]);
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic()
    {
        $form = new StatisticSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/statistic',
            ]);
        }

        return $this->render('statistic', [
            'model' => $form,
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
            'model' => $form,
        ]);
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailingServer()
    {
        $form = new MailingSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            return $this->redirect(['/admin/setting/mailing-server-test']);
        }

        return $this->render('mailing_server', [
            'model' => $form,
            'settings' => Yii::$app->settings,
        ]);
    }

    public function actionMailingServerTest()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();

        try {
            $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], [
                'message' => Yii::t('AdminModule.settings', 'Test message'),
            ]);
            $mail->setTo($user->email);
            $mail->setSubject(Yii::t('AdminModule.settings', 'Test message'));

            if ($mail->send()) {
                $this->view->info(
                    Yii::t(
                        'AdminModule.settings',
                        'Saved and sent test email to: {address}',
                        ['address' => $user->email],
                    ),
                );
            } else {
                $this->view->error(Yii::t('AdminModule.settings', 'Could not send test email.'));
            }
        } catch (Exception $e) {
            $this->view->error(Yii::t('AdminModule.settings', 'Could not send test email.') . ' ' . $e->getMessage());
        }

        return $this->redirect(['/admin/setting/mailing-server']);
    }


    public function actionDesign()
    {
        $form = new DesignSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
            return $this->redirect([
                '/admin/setting/design',
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
        $form = new FileSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            $this->view->saved();
        }

        return $this->render('file', ['model' => $form]);
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        $form = new ProxySettingsForm();


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
        $settings = new OEmbedSettingsForm();


        if ($settings->load(Yii::$app->request->post()) && $settings->save()) {
            $this->view->saved();
            return $this->redirect(['/admin/setting/oembed']);
        }

        return $this->render('oembed', [
            'providers' => $providers,
            'settings' => $settings,
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
            $dating = date('Y-m-d H:i:s', (int)$dating->log_time);
        } else {
            $dating = "the begining of time";
        }

        $form = new LogsSettingsForm();
        $limitAgeOptions = $form->options;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {

            $timeAgo = strtotime($form->logsDateLimit);
            Log::deleteAll(['<', 'log_time', $timeAgo]);
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.settings', 'Saved'));
            return $this->redirect([
                '/admin/setting/logs',
            ]);
        }

        return $this->render('logs', [
            'logsCount' => $logsCount,
            'model' => $form,
            'limitAgeOptions' => $limitAgeOptions,
            'dating' => $dating,
        ]);
    }

    public function actionTopics()
    {
        $model = new AddTopicForm();
        $suggestGlobalConversion = false;

        if ($model->load(Yii::$app->request->post())) {
            $model->on($model::EVENT_GLOBAL_CONVERSION_SUGGESTION, function () use (&$suggestGlobalConversion) {
                $suggestGlobalConversion = true;
            });

            if (!!$model->convertToGlobal) {
                Topic::convertToGlobal(null, $model->name);

                $model->name = '';
                $this->view->saved();
            } elseif ($model->save()) {
                $model->name = '';
                $this->view->saved();
            }
        }

        return $this->render('topics', [
            'contentContainer' => null,
            'dataProvider' => new ActiveDataProvider([
                'query' => Topic::find()
                    ->orderBy('sort_order, name')
                    ->where(['is', 'contentcontainer_id', new Expression('NULL')])
                    ->andWhere(['module_id' => (new Topic())->moduleId, 'type' => Topic::class]),
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]),
            'addModel' => $model,
            'suggestGlobalConversion' => $suggestGlobalConversion,
        ]);
    }

    public function actionDeleteTopic($id)
    {
        $this->forcePostRequest();

        $topic = Topic::find()
            ->where(['id' => $id])
            ->andWhere(['is', 'contentcontainer_id', new Expression('NULL')])
            ->one();

        if (!$topic) {
            throw new NotFoundHttpException();
        }

        $topic->delete();

        return $this->asJson([
            'success' => true,
            'message' => Yii::t('AdminModule.settings', 'Topic has been deleted!'),
        ]);
    }

    public function actionEditTopic($id)
    {
        $topic = Topic::find()
            ->where(['id' => $id])
            ->andWhere(['is', 'contentcontainer_id', new Expression('NULL')])
            ->one();

        if (!$topic) {
            throw new NotFoundHttpException();
        }

        if ($topic->load(Yii::$app->request->post()) && $topic->save()) {
            return ModalClose::widget([
                'saved' => true,
                'reload' => true,
            ]);
        }

        return $this->renderAjax('@topic/views/manage/editModal', [
            'model' => $topic,
        ]);
    }

    /**
     * Add or edit an OEmbed Provider
     */
    public function actionOembedEdit()
    {
        $form = new OEmbedProviderForm();

        $name = Yii::$app->request->get('name');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$name])) {
            $form->name = $name;
            $form->endpoint = $providers[$name]['endpoint'];
            $form->pattern = $providers[$name]['pattern'];
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($name && isset($providers[$name])) {
                unset($providers[$name]);
            }
            $providers[$form->name] = [
                'endpoint' => $form->endpoint,
                'pattern' => $form->pattern,
            ];
            UrlOembed::setProviders($providers);

            return $this->redirect(['/admin/setting/oembed']);
        }

        return $this->render('oembed_edit', [
            'model' => $form,
            'name' => $name,
        ]);
    }

    /**
     * Deletes OEmbed Provider
     */
    public function actionOembedDelete()
    {
        $this->forcePostRequest();
        $name = Yii::$app->request->get('name');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$name])) {
            unset($providers[$name]);
            UrlOembed::setProviders($providers);
        }
        return $this->redirect([
            '/admin/setting/oembed',
        ]);
    }

    public function actionAdvanced()
    {
        return $this->redirect(
            [
                'caching',
            ],
        );
    }
}
