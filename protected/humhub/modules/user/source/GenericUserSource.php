<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use Yii;
use yii\helpers\VarDumper;

/**
 * GenericUserSource is a fully config-driven UserSource for REST-API-based
 * provisioning or custom integrations that do not require custom code.
 *
 * Example config/common.php:
 *
 * ```php
 * 'userSourceCollection' => [
 *     'userSources' => [
 *         'hr_system' => [
 *             'class' => GenericUserSource::class,
 *             'title' => 'Workday HR',
 *             'managedAttributes' => ['email', 'firstname', 'lastname'],
 *             'approval' => false,
 *             'allowedAuthClientIds' => ['saml-sso'],
 *         ],
 *     ],
 * ],
 * ```
 *
 * @since 1.19
 */
class GenericUserSource extends BaseUserSource
{
    public function createUser(array $attributes): ?User
    {
        $user = new User();
        $user->user_source = $this->getId();

        if (empty($attributes['username'])) {
            $resolved = $this->getUsernameResolver()->resolve($attributes, $this->getUsernameStrategy());
            if ($resolved === null) {
                Yii::warning('GenericUserSource (' . $this->getId() . '): could not resolve username.', 'user');
                return null;
            }
            $attributes['username'] = $resolved;
        }

        $user->setAttributes($attributes, false);

        if (!$user->save()) {
            Yii::warning(
                'GenericUserSource (' . $this->getId() . '): could not create user. Errors: '
                . VarDumper::dumpAsString($user->getErrors()),
                'user',
            );
            return null;
        }

        $user->profile->setAttributes($attributes, false);
        $user->profile->save();

        UserSourceService::triggerAfterCreate($user);

        return $user;
    }
}
