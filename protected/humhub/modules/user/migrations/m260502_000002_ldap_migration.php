<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\db\Migration;

/**
 * Phase 2 of the UserSource refactoring: migrates LDAP identity data from the
 * user table into the user_auth table, then drops the now-redundant columns.
 *
 * Before running this migration, m260502_000001_add_user_source must be applied
 * (it pre-populated user_source = 'ldap' for all LDAP users).
 */
class m260502_000002_ldap_migration extends Migration
{
    public function up()
    {
        // Migrate LDAP users with a stored authclient_id to the user_auth table.
        // Duplicate entries are silently skipped via INSERT IGNORE.
        $this->execute("
            INSERT IGNORE INTO `user_auth` (`user_id`, `source`, `source_id`)
            SELECT `id`, `auth_mode`, `authclient_id`
            FROM `user`
            WHERE `auth_mode` = 'ldap'
              AND `authclient_id` IS NOT NULL
        ");

        // Drop the unique index before dropping the column
        $this->dropIndex('unique_authclient_id', 'user');
        $this->dropColumn('user', 'authclient_id');
        $this->dropColumn('user', 'auth_mode');
    }

    public function down()
    {
        $this->addColumn('user', 'auth_mode', $this->string(10)->notNull()->defaultValue('local'));
        $this->addColumn('user', 'authclient_id', $this->string(60)->null());
        $this->createIndex('unique_authclient_id', 'user', ['authclient_id'], true);

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
