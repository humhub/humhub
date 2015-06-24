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
 * ZDbMigration extends EDbMigration with better interactive support.
 *
 * @package humhub.commands
 * @since 0.5
 */
class ZDbMigration extends CDbMigration {

    /**
     * @var EMigrateCommand
     */
    private $migrateCommand;
    protected $interactive = true;

    /**
     * @param EMigrateCommand $migrateCommand
     */
    public function setCommand($migrateCommand) {
        $this->migrateCommand = $migrateCommand;
        $this->interactive = $migrateCommand->interactive;
    }

    /**
     * @see CConsoleCommand::confirm()
     * @param string $message
     * @return bool
     */
    public function confirm($message) {
        if (!$this->interactive) {
            return true;
        }
        return $this->migrateCommand->confirm($message);
    }

    /**
     * @see CConsoleCommand::prompt()
     * @param string $message
     * @param mixed  $defaultValue will be returned when interactive is false
     * @return string
     */
    public function prompt($message, $defaultValue) {
        if (!$this->interactive) {
            return $defaultValue;
        }
        return $this->migrateCommand->prompt($message);
    }

    /**
     * Executes a SQL statement. Silently. (only show sql on exception)
     * This method executes the specified SQL statement using {@link dbConnection}.
     * @param string $sql the SQL statement to be executed
     * @param array $params input parameters (name=>value) for the SQL execution. See {@link CDbCommand::execute} for more details.
     * @param boolean $verbose
     */
    public function execute($sql, $params = array(), $verbose = true) {
        if ($verbose) {
            parent::execute($sql, $params);
        } else {
            try {
                echo "    > execute SQL ...";
                $time = microtime(true);
                $this->getDbConnection()->createCommand($sql)->execute($params);
                echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
            } catch (CException $e) {
                echo " failed.\n\n";
                throw $e;
            }
        }
    }

}
