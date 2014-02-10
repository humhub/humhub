<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */
class m131203_110444_oembed extends EDbMigration {

    public function up() {
        $this->createTable('url_oembed', array(
            'url' => 'varchar(255) NOT NULL',
            'preview' => 'text NOT NULL',
            'PRIMARY KEY (`url`)'
        ));

        $this->renameColumn('post', 'message', 'message_2trash');
        $this->renameColumn('post', 'original_message', 'message');
    }

    public function down() {
        echo "m131203_110444_oembed does not support migration down.\n";
        return false;
    }

    /*
      // Use safeUp/safeDown to do migration with transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
