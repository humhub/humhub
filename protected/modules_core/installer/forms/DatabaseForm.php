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

/**
 * DatabaseForm holds all required database settings.
 *
 * @package humhub.modules_core.installer.forms
 * @since 0.5
 */
class DatabaseForm extends CFormModel {

    public $hostname;
    public $username;
    public $password;
    public $database;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('hostname, username, database', 'required'),
            array('password', 'safe'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'hostname' => Yii::t('InstallerModule.forms_DatabaseForm', 'Hostname'),
            'username' => Yii::t('InstallerModule.forms_DatabaseForm', 'Username'),
            'password' => Yii::t('InstallerModule.forms_DatabaseForm', 'Password'),
            'database' => Yii::t('InstallerModule.forms_DatabaseForm', 'Name of Database'),
        );
    }

}
