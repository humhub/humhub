<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\commands;

use Exception;
use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;
use Zend\Ldap\Ldap;

/**
 * Console tools for manage Ldap
 */
class LdapController extends \yii\console\Controller
{

    /**
     * Returns status information
     *
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     */
    public function actionStatus($id = 'ldap')
    {
        $this->stdout("*** LDAP Status for AuthClient ID: " . $id . "\n\n");

        try {
            $ldapAuthClient = $this->getAuthClient($id);

            $ldap = $ldapAuthClient->getLdap();
            $userCount = $ldap->count($ldapAuthClient->userFilter, $ldapAuthClient->baseDn, Ldap::SEARCH_SCOPE_SUB);
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Host:\t\t" . $ldapAuthClient->hostname . "\n");
        $this->stdout("Port:\t\t" . $ldapAuthClient->port . "\n");
        $this->stdout("BaseDN:\t\t" . $ldapAuthClient->baseDn . "\n\n");

        $this->stdout("LDAP connection successful!\n\n", Console::FG_GREEN);

        $activeUserCount = User::find()->andWhere(['auth_mode' => $ldapAuthClient->getId(), 'status' => User::STATUS_ENABLED])->count();
        $disabledUserCount = User::find()->andWhere(['auth_mode' => $ldapAuthClient->getId(), 'status' => User::STATUS_DISABLED])->count();

        $this->stdout("LDAP user count:\t\t" . $userCount . " users.\n");;
        $this->stdout("HumHub user count (active):\t" . $activeUserCount . " users.\n");
        $this->stdout("HumHub user count (disabled):\t" . $disabledUserCount . " users.\n\n");

        return ExitCode::OK;
    }


    /**
     * Synchronizes all ldap users if autoRefresh is enabled
     *
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     */
    public function actionSync($id = 'ldap')
    {
        $this->stdout("*** LDAP User List for AuthClient ID: " . $id . "\n\n");

        try {
            $ldapAuthClient = $this->getAuthClient($id);
            $ldapAuthClient->syncUsers();

        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("\nLDAP sync completed!\n\n", Console::FG_GREEN);

        return ExitCode::OK;
    }


    /**
     * Lists all users found in the LDAP server
     *
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     */
    public function actionListUsers($id = 'ldap')
    {
        $this->stdout("*** LDAP User List for AuthClient ID: " . $id . "\n\n");

        try {
            $ldapAuthClient = $this->getAuthClient($id);

            $users = [];
            foreach ($ldapAuthClient->getUserCollection() as $user) {
                $authClient = $ldapAuthClient->getAuthClientInstance($user);
                $attributes = $authClient->getUserAttributes();

                $username = (isset($attributes['username']) ? $attributes['username'] : '---');
                $id = (isset($attributes['id']) ? $attributes['id'] : '---');
                $email = (isset($attributes['email']) ? $attributes['email'] : '---');

                $users[] = [$id, $username, $email];
            }

            echo Table::widget(['headers' => ['ID', 'Username', 'E-Mail'], 'rows' => $users]);


        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }


    /**
     * @param $id
     * @return LdapAuth
     */
    protected function getAuthClient($id)
    {
        /** @var LdapAuth $ldapAuthClient */
        $ldapAuthClient = Yii::$app->authClientCollection->getClient($id, true);

        if (!$ldapAuthClient instanceof LdapAuth) {
            throw new InvalidArgumentException("The specified ID does not match to a LDAP AuthClient");
        }

        return $ldapAuthClient;
    }
}
