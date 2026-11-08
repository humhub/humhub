<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\log;

use yii\log\Target;

/**
 * Log dispatcher that masks secrets in the context dump of every log target.
 *
 * The context dump contains `$_SERVER`, which also holds every variable loaded from `.env`. The masks are added to
 * all targets, including the ones configured outside HumHub's core configuration (e.g. an `EmailTarget` added via
 * `HUMHUB_CONFIG__COMPONENTS__LOG__TARGETS__EMAIL`) and the ones added at run time (`Yii::$app->log->targets[] = …`),
 * so that no target dumps a secret.
 *
 * Limit: Yii matches the patterns against the flattened context, so a key that itself contains a dot (e.g. the
 * session key `twofa.switchedUserId` or an `.env` variable `mail.smtp.password`) is not masked.
 *
 * @since 1.19.4
 * @see Target::$maskVars
 */
class Dispatcher extends \yii\log\Dispatcher
{
    /**
     * @var string[] values of keys whose name contains one of these words are masked (case-insensitive, e.g.
     * `HUMHUB_CONFIG__COMPONENTS__DB__PASSWORD`, `HUMHUB_FIXED_SETTINGS__BASE__MAILER_DSN`, `HTTP_AUTHORIZATION`)
     */
    public array $maskedWords = ['PASS', 'SECRET', 'KEY', 'TOKEN', 'DSN', 'CREDENTIAL', 'AUTH', 'PRIVATE', 'SALT', 'COOKIE'];

    /**
     * @var string[] PHP predefined variables whose keys are searched for [[maskedWords]]
     */
    public array $maskedWordsVars = ['_SERVER', '_GET', '_POST', '_FILES'];

    /**
     * @var string[] PHP predefined variables whose values are all masked (their keys are still logged)
     */
    public array $maskedVars = ['_COOKIE', '_SESSION'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->maskTargets();
    }

    /**
     * @inheritdoc
     */
    public function dispatch($messages, $final)
    {
        // Covers the targets added after the component was built
        $this->maskTargets();
        parent::dispatch($messages, $final);
    }

    /**
     * Adds [[getMaskVars()]] to the `maskVars` of every target.
     */
    protected function maskTargets(): void
    {
        $maskVars = $this->getMaskVars();
        foreach ($this->targets as $target) {
            if ($target instanceof Target) {
                $target->maskVars = array_values(array_unique(array_merge($target->maskVars, $maskVars)));
            }
        }
    }

    /**
     * @return string[] the patterns added to [[Target::$maskVars]] of every target
     */
    public function getMaskVars(): array
    {
        $maskVars = [];
        foreach ($this->maskedWordsVars as $var) {
            foreach ($this->maskedWords as $word) {
                $maskVars[] = $var . '.*' . $word . '*';
            }
        }
        foreach ($this->maskedVars as $var) {
            $maskVars[] = $var . '.*';
        }

        return $maskVars;
    }
}
