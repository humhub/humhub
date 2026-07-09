<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Migration;
use yii\db\Query;

/**
 * Phase 2 of the UserSource refactoring: migrates LDAP identity data from the
 * user table into the user_auth table, then drops the now-redundant columns.
 *
 * Before running this migration, m260502_000001_add_user_source must be applied
 * (it pre-populated user_source = 'ldap' for all LDAP users).
 *
 * Written to be restartable: every step is guarded so the migration can be
 * re-run after a partial failure (e.g. a lock timeout while dropping a column on
 * a large user table) without erroring out or duplicating user_auth rows.
 */
class m260502_000002_ldap_migration extends Migration
{
    public function up()
    {
        // Only migrate while the legacy columns still exist. Once they are gone
        // the data move has already completed, so a re-run becomes a no-op here.
        if ($this->columnExists('authclient_id', 'user') && $this->columnExists('auth_mode', 'user')) {
            // Guard against duplicates on a re-run within the window where the
            // legacy columns are still present. source='ldap' rows in user_auth
            // are created exclusively by this migration, so their presence means
            // the INSERT already ran (a single INSERT ... SELECT is atomic, so a
            // failed attempt leaves no partial rows).
            $alreadyMigrated = (new Query())
                ->from('user_auth')
                ->where(['source' => 'ldap'])
                ->exists($this->db);

            if (!$alreadyMigrated) {
                $this->execute("
                    INSERT IGNORE INTO `user_auth` (`user_id`, `source`, `source_id`)
                    SELECT `id`, `auth_mode`, `authclient_id`
                    FROM `user`
                    WHERE `auth_mode` = 'ldap'
                      AND `authclient_id` IS NOT NULL
                ");
            }
        }

        // Idempotent cleanup – safe to run again if a previous attempt was interrupted.
        $this->safeDropIndex('unique_authclient_id', 'user');
        $this->safeDropColumn('user', 'authclient_id');
        $this->safeDropColumn('user', 'auth_mode');
    }

    public function down()
    {
        $this->safeAddColumn('user', 'auth_mode', $this->string(10)->notNull()->defaultValue('local'));
        $this->safeAddColumn('user', 'authclient_id', $this->string(60)->null());
        $this->safeCreateIndex('unique_authclient_id', 'user', ['authclient_id'], true);

        // Restore auth_mode from user_source
        $this->execute("UPDATE `user` SET `auth_mode` = `user_source`");

        // Restore authclient_id from user_auth for LDAP users
        $this->execute("
            UPDATE `user` u
            INNER JOIN `user_auth` ua ON ua.user_id = u.id AND ua.source = 'ldap'
            SET u.authclient_id = ua.source_id
        ");
    }
}
