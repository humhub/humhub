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
 * SetupController checks prerequisites and is responsible for database
 * connection and schema setup.
 *
 * @package humhub.modules_core.installer.controllers
 * @since 0.5
 */
class SetupController extends Controller
{

    /**
     * @var String layout to use
     */
    public $layout = '_layout';

    const PASSWORD_PLACEHOLDER = 'n0thingToSeeHere!';

    public function actionIndex()
    {
        $this->redirect(Yii::app()->createUrl('prerequisites'));
    }

    /**
     * Prequisites action checks application requirement using the SelfTest
     * Libary
     *
     * (Step 2)
     */
    public function actionPrerequisites()
    {

        $checks = SelfTest::getResults();

        $hasError = false;
        foreach ($checks as $check) {
            if ($check['state'] == 'ERROR')
                $hasError = true;
        }

        // Render Template
        $this->render('prerequisites', array('checks' => $checks, 'hasError' => $hasError));
    }

    /**
     * Database action is responsible for all database related stuff.
     * Checking given database settings, writing them into a config file.
     *
     * (Step 3)
     */
    public function actionDatabase()
    {

        Yii::import('installer.forms.*');

        $success = false;
        $errorMessage = "";

        $config = HSetting::getConfiguration();

        $form = new DatabaseForm;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'database-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['DatabaseForm'])) {
            $form->attributes = $_POST['DatabaseForm'];

            if ($form->validate()) {

                $connectionString = "mysql:host=" . $form->hostname . ";dbname=" . $form->database;

                $password = $form->password;
                if ($password == self::PASSWORD_PLACEHOLDER)
                    $password = $config['components']['db']['password'];

                // Create Test DB Connection
                Yii::app()->setComponent('db', array(
                    'connectionString' => $connectionString,
                    'username' => $form->username,
                    'password' => $password,
                    'class' => 'CDbConnection',
                    'charset' => 'utf8'
                ));

                try {
                    // Check DB Connection
                    Yii::app()->db->getServerVersion();

                    // Write Config
                    $config['components']['db']['connectionString'] = $connectionString;
                    $config['params']['installer']['db']['installer_hostname'] = $form->hostname;
                    $config['params']['installer']['db']['installer_database'] = $form->database;

                    $config['components']['db']['username'] = $form->username;

                    if ($form->password != self::PASSWORD_PLACEHOLDER)
                        $config['components']['db']['password'] = $form->password;

                    HSetting::setConfiguration($config);

                    $success = true;
                    
                    $this->redirect(array('init'));
                    
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }
        } else {

            if (isset($config['params']['installer']['db']['installer_hostname']))
                $form->hostname = $config['params']['installer']['db']['installer_hostname'];

            if (isset($config['params']['installer']['db']['installer_database']))
                $form->database = $config['params']['installer']['db']['installer_database'];

            if (isset($config['components']['db']['username']))
                $form->username = $config['components']['db']['username'];

            if (isset($config['components']['db']['password']))
                $form->password = self::PASSWORD_PLACEHOLDER;
        }

        // Render Template
        $this->render('database', array('model' => $form, 'success' => $success, 'submitted' => isset($_POST['DatabaseForm']), 'errorMessage' => $errorMessage));
    }

    /**
     * The init action imports the database structure & inital data
     */
    public function actionInit()
    {

        if (!$this->getModule()->checkDBConnection())
            $this->redirect(Yii::app()->createUrl('//installer/setup/database'));

        // Flush Caches
        Yii::app()->cache->flush();

        // Disable max execution time to avoid timeouts during database installation
        @ini_set('max_execution_time', 0);
        
        // Migrate Up Database
        Yii::import('application.commands.shell.ZMigrateCommand');
        ZMigrateCommand::AutoMigrate();

        $this->redirect(Yii::app()->createUrl('//installer/config/index'));
    }

}
