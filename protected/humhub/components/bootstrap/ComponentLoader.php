<?php

namespace humhub\components\bootstrap;

use humhub\components\InstallationState;
use humhub\components\mail\Mailer;
use humhub\modules\admin\models\forms\MailingSettingsForm;
use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\web\Application;

class ComponentLoader implements BootstrapInterface
{
    private static $loadedComponents = [];

    public function bootstrap($app)
    {
        if (!$app) {
            return;
        }

        $this->setMailerConfig($app);
        $this->setUserConfig($app);
        $this->setParams($app);
    }

    public static function isFixed($component)
    {
        return !ArrayHelper::keyExists($component, self::$loadedComponents);
    }

    private function updateComponentDefinition($app, $component, $definition)
    {
        self::$loadedComponents[$component] = true;

        $app->set(
            $component,
            ArrayHelper::merge($this->getComponentDefinition($app, $component), $definition),
        );
    }

    private function getComponentDefinition($app, $component, $property = null)
    {
        if (!is_null($property)) {
            return ArrayHelper::getValue($app->components, [$component, $property]);
        } else {
            return ArrayHelper::getValue($app->components, $component, []);
        }
    }

    private function setMailerConfig($app): void
    {
        if ($app->has('mailer', true)) {
            $app->log->logger->log('`mailer` component should not be instantiated before settings are loaded.', Logger::LEVEL_WARNING);
        }

        $transportType = $app->settings->get('mailer.transportType', MailingSettingsForm::TRANSPORT_PHP);

        //Check if Test environment
        if ($this->getComponentDefinition($app, 'mailer', 'class') !== Mailer::class) {
            $app->mailer->useFileTransport = true;

            return;
        }

        if ($transportType === MailingSettingsForm::TRANSPORT_FILE) {
            $definition = [
                'transport' => ['dsn' => 'native://default'],
                'useFileTransport' => true,
            ];

            $this->updateComponentDefinition($app, 'mailer', $definition);
        } elseif ($transportType === MailingSettingsForm::TRANSPORT_CONFIG) {
            $app->set('mailer', false);
        } else {
            $definition = [];

            if ($transportType === MailingSettingsForm::TRANSPORT_SMTP) {
                if ($app->settings->get('mailer.hostname')) {
                    $definition['transport']['host'] = $app->settings->get('mailer.hostname');
                }
                if ($app->settings->get('mailer.port')) {
                    $definition['transport']['port'] = (int)$app->settings->get('mailer.port');
                } else {
                    $definition['transport']['port'] = 25;
                }
                if ($app->settings->get('mailer.username')) {
                    $definition['transport']['username'] = $app->settings->get('mailer.username');
                }
                if ($app->settings->get('mailer.password')) {
                    $definition['transport']['password'] = $app->settings->get('mailer.password');
                }
                $definition['transport']['scheme'] = (empty($app->settings->get('mailer.useSmtps'))) ? 'smtp' : 'smtps';

            } elseif ($transportType === MailingSettingsForm::TRANSPORT_PHP) {
                $definition['transport']['dsn'] = 'native://default';
            } elseif ($transportType === MailingSettingsForm::TRANSPORT_DSN) {
                $definition['transport']['dsn'] = $app->settings->get('mailer.dsn');
            }

            $this->updateComponentDefinition($app, 'mailer', $definition);
        }
    }

    private function setUserConfig($app): void
    {
        if ($app->has('user', true)) {
            $app->log->logger->log('`user` component should not be instantiated before settings are loaded.', Logger::LEVEL_WARNING);
        } else {
            if ($app instanceof Application) {
                $definition = [
                    'enableSession' => $app->installationState->hasState(InstallationState::STATE_INSTALLED),
                ];
            } else {
                $definition = [];
            }

            if ($authTimeout = $app->getModule('user')->settings->get('auth.defaultUserIdleTimeoutSec')) {
                $definition['authTimeout'] = $authTimeout;
            }
            $this->updateComponentDefinition($app, 'user', $definition);
        }
    }

    protected function setParams($app)
    {
        $app->name = $app->settings->get('name');
    }
}
