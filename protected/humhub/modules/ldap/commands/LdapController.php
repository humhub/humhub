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
use humhub\modules\ldap\Module;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\user\models\Auth;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

/**
 * Console tools for managing LDAP
 */
class LdapController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'list';

    /**
     * Lists configured LDAP connections / auth clients
     */
    public function actionList()
    {
        $this->stdout("*** Configured LDAP Connections \n\n");

        $registry = $this->getModule()->getConnectionRegistry();
        $rows = [];
        foreach ($registry->getIds() as $id) {
            $config = $registry->getConfig($id);
            $rows[] = [$id, $config->title, $config->hostname, $config->port, $config->baseDn];
        }

        try {
            echo Table::widget(['headers' => ['ID', 'Title', 'Host', 'Port', 'Base DN'], 'rows' => $rows]);
        } catch (Exception $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        print "\n\n";
        return ExitCode::OK;
    }

    /**
     * Returns status information
     *
     * @param string $id the connection id (default: ldap)
     */
    public function actionStatus($id = 'ldap')
    {
        $this->stdout("*** LDAP Status for connection: " . $id . "\n\n");

        try {
            $config = $this->getModule()->getConnectionRegistry()->getConfig($id);
            $service = $this->getModule()->getConnectionRegistry()->getService($id);
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Host:\t\t" . $config->hostname . "\n");
        $this->stdout("Port:\t\t" . $config->port . "\n");
        $this->stdout("BaseDN:\t\t" . $config->baseDn . "\n\n");

        $this->stdout("LDAP connection successful!\n\n", Console::FG_GREEN);

        $activeUserCount = User::find()->andWhere(['user_source' => $id, 'status' => User::STATUS_ENABLED])->count();
        $disabledUserCount = User::find()->andWhere(['user_source' => $id, 'status' => User::STATUS_DISABLED])->count();

        $this->stdout("LDAP user count:\t\t" . $service->countUsers() . " users.\n");
        $this->stdout("HumHub user count (active):\t" . $activeUserCount . " users.\n");
        $this->stdout("HumHub user count (disabled):\t" . $disabledUserCount . " users.\n\n");

        return ExitCode::OK;
    }

    /**
     * Synchronizes all LDAP users for a given connection.
     *
     * @param string $id the connection id (default: ldap)
     */
    public function actionSync($id = 'ldap')
    {
        $this->stdout("*** LDAP Sync for connection: " . $id . "\n\n");

        try {
            $source = $this->getUserSource($id);
            $source->syncUsers();
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("\nLDAP sync completed!\n\n", Console::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * Lists all users found in the LDAP directory for the given connection.
     */
    public function actionListUsers($id = 'ldap')
    {
        $this->stdout("*** LDAP User List for connection: " . $id . "\n\n");

        try {
            $authClient = $this->getAuthClient($id);
            $service = $this->getModule()->getConnectionRegistry()->getService($id);

            $users = [];
            foreach ($service->getAllUserEntries() as $entry) {
                $client = clone $authClient;
                $client->init();
                $client->setUserAttributes($entry);
                $attributes = $client->getUserAttributes();

                $users[] = [
                    $attributes['id'] ?? '---',
                    $attributes['username'] ?? '---',
                    $attributes['email'] ?? '---',
                ];
            }

            echo Table::widget(['headers' => ['ID', 'Username', 'E-Mail'], 'rows' => $users]);
        } catch (Exception $ex) {
            $this->stderr("Error: " . $ex->getMessage() . "\n\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Clears the LDAP user_auth mappings for a given connection.
     */
    public function actionMappingClear($id = 'ldap', $userName = null)
    {
        $this->stdout("*** LDAP Clear user_auth mappings for connection: " . $id . "\n\n");

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
     * Rebuilds the user_auth mappings by matching LDAP users to HumHub accounts
     * via email or username.
     */
    public function actionMappingRebuild($id = 'ldap')
    {
        $this->stdout("*** LDAP Rebuild user_auth mappings for connection: " . $id . "\n\n");

        $checked = 0;
        $created = 0;

        try {
            $authClient = $this->getAuthClient($id);
            $service = $this->getModule()->getConnectionRegistry()->getService($id);

            foreach ($service->getAllUserEntries() as $entry) {
                $checked++;

                $client = clone $authClient;
                $client->init();
                $client->setUserAttributes($entry);
                $attributes = $client->getUserAttributes();

                if (!isset($attributes['id'])) {
                    $this->stdout("Skipped - No ID for: " . ($attributes['dn'] ?? '?') . "\n");
                    continue;
                }

                $ldapId = (string)$attributes['id'];

                if (Auth::find()->where(['source' => $id, 'source_id' => $ldapId])->exists()) {
                    continue;
                }

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
     * Shows all returned LDAP attributes for a given username.
     */
    public function actionShowUser($user, $id = 'ldap')
    {
        $this->stdout("*** LDAP User Details for \"" . $user . "\" - connection: " . $id . "\n\n");

        try {
            $service = $this->getModule()->getConnectionRegistry()->getService($id);

            $dn = $service->getUserDn($user);
            if ($dn === null) {
                $this->stderr("Error: User or Email not found!\n\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $rows = [];
            foreach ($service->getEntry($dn) as $name => $value) {
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

    private function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        return $module;
    }

    private function getAuthClient(string $id): LdapAuth
    {
        /** @var LdapAuth $client */
        $client = Yii::$app->authClientCollection->getClient($id, true);
        if (!$client instanceof LdapAuth) {
            throw new InvalidArgumentException("The specified ID does not match an LDAP AuthClient");
        }
        return $client;
    }

    private function getUserSource(string $id): LdapUserSource
    {
        $collection = UserSourceService::getCollection();
        if (!$collection->hasUserSource($id)) {
            throw new InvalidArgumentException("Unknown LDAP UserSource: '{$id}'");
        }
        $source = $collection->getUserSource($id);
        if (!$source instanceof LdapUserSource) {
            throw new InvalidArgumentException("UserSource '{$id}' is not an LDAP source");
        }
        return $source;
    }
}
