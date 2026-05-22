<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use yii\db\Migration;

/**
 * Adds user_source column to the user table.
 *
 * Phase 1 of the UserSource refactoring: introduces the column with 'local' as
 * default for all existing users. auth_mode and authclient_id are removed in
 * Phase 2 once LdapUserSource is fully implemented.
 */
class m260502_000001_add_user_source extends Migration
{
    public function up()
    {
        $this->addColumn(
            'user',
            'user_source',
            $this->string(50)->notNull()->defaultValue('local')->after('auth_mode'),
        );

        // Existing LDAP users: pre-populate user_source so Phase 2 migration can rely on it
        $this->execute("UPDATE `user` SET `user_source` = `auth_mode` WHERE `auth_mode` = 'ldap'");
    }

    public function down()
    {
        $this->dropColumn('user', 'user_source');
    }
}
