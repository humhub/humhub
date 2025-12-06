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

        $this->setRequestConfig($app);
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
        $transportType = $app->settings->get('mailerTransportType', MailingSettingsForm::TRANSPORT_PHP);

        // Check if Test environment
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
        } elseif (in_array($transportType, [MailingSettingsForm::TRANSPORT_SMTP, MailingSettingsForm::TRANSPORT_PHP, MailingSettingsForm::TRANSPORT_DSN])) {
            $definition = [];

            if ($transportType === MailingSettingsForm::TRANSPORT_SMTP) {
                if ($app->settings->get('mailerHostname')) {
                    $definition['transport']['host'] = $app->settings->get('mailerHostname');
                }
                if ($app->settings->get('mailerPort')) {
                    $definition['transport']['port'] = (int)$app->settings->get('mailerPort');
                } else {
                    $definition['transport']['port'] = 25;
                }
                if ($app->settings->get('mailerUsername')) {
                    $definition['transport']['username'] = $app->settings->get('mailerUsername');
                }
                if ($app->settings->get('mailerPassword')) {
                    $definition['transport']['password'] = $app->settings->get('mailerPassword');
                }

                if ((empty($app->settings->get('mailerUseSmtps')))) {
                    $definition['transport']['scheme'] = 'smtp';
                } else {
                    $definition['transport']['scheme'] = 'smtps';
                    if (!empty($app->settings->get('mailerAllowSelfSignedCerts'))) {
                        $definition['transport']['options'] = ['verify_peer' => false];
                    }
                }

            } elseif ($transportType === MailingSettingsForm::TRANSPORT_PHP) {
                $definition['transport']['dsn'] = 'native://default';
            } elseif ($transportType === MailingSettingsForm::TRANSPORT_DSN) {
                $definition['transport']['dsn'] = $app->settings->get('mailerDsn');
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

    private function setRequestConfig(\yii\base\Application $app)
    {
        if ($app->installationState->hasState(InstallationState::STATE_INSTALLED)) {
            $secret = $app->settings->get('secret');
            if ($secret != "") {
                $app->request->cookieValidationKey = $secret;
            }
        }

        if ($app->request->cookieValidationKey == '') {
            $app->requestcookieValidationKey = 'installer';
        }

        if (
            defined('YII_ENV_TEST') && YII_ENV_TEST && $_SERVER['SCRIPT_FILENAME'] === 'index-test.php' && in_array(
                $_SERVER['SCRIPT_NAME'],
                ['/sw.js', '/offline.pwa.html', '/manifest.json'],
                true,
            )
        ) {
            $app->request->setScriptUrl('/index.php');
        }
    }
}
