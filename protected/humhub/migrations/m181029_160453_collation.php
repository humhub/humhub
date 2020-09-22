<?php

use yii\db\Migration;

/**
 * Class m181029_160453_collation
 */
class m181029_160453_collation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $dbName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();
            $this->db->createCommand('ALTER DATABASE `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')->execute();
        } catch (\Exception $e) {
            Yii::error('Could not convert database to utf8mb4: ' . $e->getMessage());
            return;
        }

        try {
            $sqlGetTables = 'SELECT table_name FROM information_schema.tables WHERE table_schema=:schema AND table_type="BASE TABLE"';
            $tables = $this->db->createCommand($sqlGetTables, [':schema' => $dbName])->queryAll();

            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
            foreach ($tables as $table) {
                $this->migrateTable($dbName, $table['table_name']);
            }

            $this->db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
        } catch (\Exception $e) {
            Yii::error('Could not migrate tables to utf8mb4: ' . $e->getMessage());
        }
    }

    protected function migrateTable($dbName, $tableName)
    {
        #print 'Migrate table ' . $tableName . " to collation: utf8mb4\n";
        
        try {
            $this->db->createCommand('ALTER TABLE `' . $tableName . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')->execute();
        } catch (\Exception $e) {
            Yii::error('Could not convert table to utf8mb4: ' . $e->getMessage());
        }

        try {
            $this->db->createCommand('ALTER TABLE `' . $tableName . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')->execute();
        } catch (\Exception $e) {
            Yii::error('Could not set default collation to utf8mb4: ' . $e->getMessage());
        }
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181029_160453_collation cannot be reverted.\n";

        return false;
    }
}
