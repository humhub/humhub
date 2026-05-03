<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap\commands;

use Exception;
use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\helpers\LdapHelper;
use humhub\modules\ldap\services\LdapService;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * Console tools for manage Ldap
 */
class LdapController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'list';

    /**
     * Lists configured LDAP auth clients
     *
     * @return int the exit code
     */
    public function actionList()
    {
        $this->stdout("*** Configured LDAP AuthClients \n\n");

        $clients = [];
        foreach (Yii::$app->authClientCollection->getClients(true) as $id => $client) {
            if ($client instanceof LdapAuth) {
                /** @var LdapAuth $client */
                $clients[] = [$id, $client->getName() . ' (' . $client->getId() . ')', $client->hostname, $client->port, $client->baseDn];
            }
        }

        try {
            echo Table::widget(['headers' => ['AuthClient ID', 'Name (ClientId)', 'Host', 'Port', 'Base DN'], 'rows' => $clients]);
        } catch (Exception $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        print "\n\n";
    }

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
            $ldapService = LdapService::create($id);
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Host:\t\t" . $ldapService->authClient->hostname . "\n");
        $this->stdout("Port:\t\t" . $ldapService->authClient->port . "\n");
        $this->stdout("BaseDN:\t\t" . $ldapService->authClient->baseDn . "\n\n");

        $this->stdout("LDAP connection successful!\n\n", Console::FG_GREEN);

        $activeUserCount = User::find()->andWhere(['user_source' => $id, 'status' => User::STATUS_ENABLED])->count();
        $disabledUserCount = User::find()->andWhere(['user_source' => $id, 'status' => User::STATUS_DISABLED])->count();

        $this->stdout("LDAP user count:\t\t" . $ldapService->countUsers() . " users.\n");
        $this->stdout("HumHub user count (active):\t" . $activeUserCount . " users.\n");
        $this->stdout("HumHub user count (disabled):\t" . $disabledUserCount . " users.\n\n");

        return ExitCode::OK;
    }


    /**
     * Synchronizes all ldap users (if autoRefresh is enabled)
     *
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     */
    public function actionSync($id = 'ldap')
    {
        $this->stdout("*** LDAP Sync for AuthClient ID: " . $id . "\n\n");

        try {
            $ldapAuthClient = $this->getAuthClient($id);
            $ldapAuthClient->getUserSource()->syncUsers();
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
            $ldapService = LdapService::create($id);

            $users = [];
            foreach ($ldapService->getAuthClients() as $authClient) {
                $attributes = $authClient->getUserAttributes();

                $username = ($attributes['username'] ?? '---');
                $id = ($attributes['id'] ?? '---');
                $email = ($attributes['email'] ?? '---');

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
     * Clears the LDAP user_auth mappings for a given auth client.
     *
     * @param string $id the auth client id (default: ldap)
     * @param string $userName if set, clears only the mapping for this username
     * @return int status code
     */
    public function actionMappingClear($id = 'ldap', $userName = null)
    {
        $this->stdout("*** LDAP Clear user_auth mappings for AuthClient ID: " . $id . "\n\n");

        if ($userName === null) {
            $deleted = Auth::deleteAll(['source' => $id]);
        } else {
            $user = User::findOne(['username' => $userName, 'user_source' => $id]);
            if ($user === null) {
                $this->stderr("Error: User \"" . $userName . "\" not found.\n\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $deleted = Auth::deleteAll(['source' => $id, 'user_id' => $user->id]);
        }

        $this->stdout("Cleared " . $deleted . " mapping(s)!\n");
        return ExitCode::OK;
    }


    /**
     * Rebuilds the user_auth mappings by matching LDAP users to HumHub accounts via email or username.
     *
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     */
    public function actionMappingRebuild($id = 'ldap')
    {
        $this->stdout("*** LDAP Rebuild user_auth mappings for AuthClient ID: " . $id . "\n\n");

        $checked = 0;
        $created = 0;

        try {
            $ldapService = LdapService::create($id);

            foreach ($ldapService->getAuthClients() as $authClient) {
                $checked++;
                $attributes = $authClient->getUserAttributes();

                if (!isset($attributes['id'])) {
                    $this->stdout("Skipped - No ID for: " . ($attributes['dn'] ?? '?') . "\n");
                    continue;
                }

                $ldapId = (string)$attributes['id'];

                // Skip if mapping already exists
                if (Auth::find()->where(['source' => $id, 'source_id' => $ldapId])->exists()) {
                    continue;
                }

                // Try to find a matching HumHub user
                $user = null;
                if (isset($attributes['email'])) {
                    $user = User::findOne(['email' => $attributes['email']]);
                }
                if ($user === null && isset($attributes['username'])) {
                    $user = User::findOne(['username' => $attributes['username']]);
                }

                if ($user !== null) {
                    $auth = new Auth([
                        'user_id' => $user->id,
                        'source' => $id,
                        'source_id' => $ldapId,
                    ]);
                    if ($auth->save()) {
                        $created++;
                        $this->stdout("Created mapping for user: " . $user->username . " → " . $ldapId . "\n");
                    }
                }
            }

            $this->stdout("\nChecked:\t" . $checked . " LDAP users.\n");
            $this->stdout("Created:\t" . $created . " new mappings.\n");
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }


    /**
     * Shows all returned user attributes provided by the LDAP connection.
     *
     * @param string $user the username
     * @param string $id the auth client id (default: ldap)
     * @return int status code
     * @since 1.8
     */
    public function actionShowUser($user, $id = 'ldap')
    {
        $this->stdout("*** LDAP User Details for \"" . $user . "\" for AuthClient ID: " . $id . "\n\n");

        try {
            $ldapService = LdapService::create($id);

            $dn = $ldapService->getUserDn($user);
            if ($dn === null) {
                $this->stderr("Error: User or Email not found!\n\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $rows = [];
            foreach ($ldapService->getEntry($dn) as $name => $value) {
                if (!is_array($value) && LdapHelper::isBinary($value)) {
                    $value = '-Binary-';
                }
                $rows[] = [$name, $value];
            }

            echo Table::widget(['headers' => ['LDAP Attribute Name', 'Value'], 'rows' => $rows]) . "\n\n";
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
