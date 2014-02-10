<?php

class m131030_122743_longer_questions extends ZDbMigration {

    public function up() {
        $this->alterColumn('poll', 'question', 'TEXT NOT NULL');
    }

    public function down() {
        echo "m131030_122743_longer_questions does not support migration down.\n";
        return false;
    }

}