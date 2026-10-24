<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\commands;

use humhub\modules\oauth\Module;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * OAuth server maintenance.
 *
 * @since 1.20
 */
class OauthController extends Controller
{
    /**
     * Generates a new signing key pair. Access tokens signed with the old key become invalid;
     * clients refresh them.
     */
    public function actionRotateKey(): int
    {
        $keyStore = Module::instance()->getKeyStore();

        if ($keyStore->isOverridden()) {
            $this->stderr("The key pair is configured outside the database; rotate it there.\n");
            return ExitCode::CONFIG;
        }

        $keyStore->rotateKeyPair();
        $this->stdout('New key id: ' . $keyStore->getKeyId() . "\n");

        return ExitCode::OK;
    }
}
