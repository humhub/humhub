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
class m131023_165755_initial extends ZDbMigration {

    public function up() {

        $this->createTable('setting', array(
            'id' => 'pk',
            'name' => 'varchar(100) NOT NULL',
            'value' => 'varchar(255) NOT NULL',
            'value_text' => 'text DEFAULT NULL',
            'module_id' => 'varchar(100) DEFAULT NULL',
            'created_at' => 'datetime NOT NULL',
            'created_by' => 'int(11) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
            'updated_by' => 'int(11) NOT NULL',
                ), '');


        $this->createTable('module_enabled', array(
            'module_id' => 'varchar(100) NOT NULL',
                ), '');

        $this->addPrimaryKey('pk_module_enabled', 'module_enabled', 'module_id');
    }

    public function down() {
        echo "m131023_165755_initial does not support migration down.\n";
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
