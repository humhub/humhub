<?php

/**
 * SIHttpSession extends CDbHttpSession to store sessions in database.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class SIHttpSession extends CDbHttpSession {

    /**
     * @var String is the name of the database table
     */
    public $sessionTableName = 'user_http_session';

    /**
     * Creates the session DB table.
     * @param CDbConnection $db the database connection
     * @param string $tableName the name of the table to be created
     */
    protected function createSessionTable($db, $tableName) {
        $driver = $db->getDriverName();
        if ($driver === 'mysql')
            $blob = 'LONGBLOB';
        elseif ($driver === 'pgsql')
            $blob = 'BYTEA';
        else
            $blob = 'BLOB';
        $db->createCommand()->createTable($tableName, array(
            'id' => 'CHAR(255) PRIMARY KEY',
            'expire' => 'integer',
            'user_id' => 'integer',
            'data' => $blob,
        ));
    }

    /**
     * Session write handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data) {

        $userId = "";
        if (Yii::app()->user) {
            $userId = Yii::app()->user->id;
        }

        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php
        try {
            $expire = time() + $this->getTimeout();
            $db = $this->getDbConnection();
            if ($db->createCommand()->select('id')->from($this->sessionTableName)->where('id=:id', array(':id' => $id))->queryScalar() === false)
                $db->createCommand()->insert($this->sessionTableName, array(
                    'id' => $id,
                    'data' => $data,
                    'user_id' => $userId,
                    'expire' => $expire,
                ));
            else
                $db->createCommand()->update($this->sessionTableName, array(
                    'data' => $data,
                    'user_id' => $userId,
                    'expire' => $expire
                        ), 'id=:id', array(':id' => $id));
        } catch (Exception $e) {
            if (YII_DEBUG)
                echo $e->getMessage();
            // it is too late to log an error message here
            return false;
        }
        return true;
    }

}

?>
