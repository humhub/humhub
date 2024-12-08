<?php

namespace humhub\components\bootstrap;

use humhub\modules\admin\models\forms\MailingSettingsForm;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;

class SettingsLoader implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if (!$app) {
            return;
        }

        $this->setMailerConfig($app);
        $this->setUserConfig($app);
        $this->setCacheConfig($app);
    }

    private function updateComponentDefinition($app, $component, $definition)
    {
        $app->set(
            $component,
            ArrayHelper::merge(ArrayHelper::getValue($app->components, $component, []), $definition),
        );
    }

    private function setMailerConfig($app): void
    {
        $instantiated = $app->has('mailer', true);
        $transportType = $app->settings->get('mailer.transportType', MailingSettingsForm::TRANSPORT_PHP);

        if ($transportType === MailingSettingsForm::TRANSPORT_FILE) {
            if ($instantiated) {
                $app->mailer->hasMethod('setTransport') && $app->mailer->setTransport(['dsn' => 'native://default']);
                $app->mailer->useFileTransport = true;
            } else {
                $this->updateComponentDefinition($app, 'mailer', [
                    'transport' => ['dsn' => 'native://default'],
                    'useFileTransport' => true,
                ]);
            }
        } elseif ($transportType === MailingSettingsForm::TRANSPORT_CONFIG) {
            $app->set('mailer', false);
        } else {
            $transport = [];
            $app->mailer->useFileTransport = false;

            if ($transportType === MailingSettingsForm::TRANSPORT_SMTP) {
                if ($app->settings->get('mailer.hostname')) {
                    $transport['host'] = $app->settings->get('mailer.hostname');
                }
                if ($app->settings->get('mailer.port')) {
                    $transport['port'] = (int)$app->settings->get('mailer.port');
                } else {
                    $transport['port'] = 25;
                }
                if ($app->settings->get('mailer.username')) {
                    $transport['username'] = $app->settings->get('mailer.username');
                }
                if ($app->settings->get('mailer.password')) {
                    $transport['password'] = $app->settings->get('mailer.password');
                }
                $transport['scheme'] = (empty($app->settings->get('mailer.useSmtps'))) ? 'smtp' : 'smtps';

            } elseif ($transportType === MailingSettingsForm::TRANSPORT_PHP) {
                $transport['dsn'] = 'native://default';
            } elseif ($transportType === MailingSettingsForm::TRANSPORT_DSN) {
                $transport['dsn'] = $app->settings->get('mailer.dsn');
            }


            if ($instantiated) {
                $app->mailer->useFileTransport = false;
                $app->mailer->hasMethod('setTransport') && $app->mailer->setTransport($transport);
            } else {
                $this->updateComponentDefinition($app, 'mailer', [
                    'useFileTransport' => false,
                    'transport' => $transport,
                ]);
            }
        }
    }

    private function setUserConfig($app): void
    {
        if ($defaultUserIdleTimeoutSec = $app->getModule('user')->settings->get('auth.defaultUserIdleTimeoutSec')) {
            if ($app->has('user', true)) {
                $app->user->authTimeout = $defaultUserIdleTimeoutSec;
            } else {
                $this->updateComponentDefinition($app, 'user', [
                    'authTimeout' => $defaultUserIdleTimeoutSec,
                ]);
            }
        }
    }

    private function setCacheConfig($app): void
    {
        $cacheClass = $app->settings->get('cache.class');
        $cacheComponent = [];

        if (in_array($cacheClass, [\yii\caching\DummyCache::class, \yii\caching\FileCache::class])) {
            $cacheComponent = [
                'class' => $cacheClass,
            ];
        } elseif ($cacheClass == \yii\caching\ApcCache::class && (function_exists('apcu_add') || function_exists('apc_add'))) {
            $cacheComponent = [
                'class' => $cacheClass,
                'useApcu' => (function_exists('apcu_add')),
            ];
        } elseif ($cacheClass === \yii\redis\Cache::class) {
            $cacheComponent = [
                'class' => \yii\redis\Cache::class,
            ];
        }

        if (!empty($cacheComponent)) {
            $this->updateComponentDefinition($app, 'cache', ArrayHelper::merge($cacheComponent, [
                'keyPrefix' => $app->id,
            ]));
        }
    }
}
