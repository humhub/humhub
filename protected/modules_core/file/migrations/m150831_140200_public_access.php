<?php
class m150831_140200_public_access extends EDbMigration {
	public function up() {
		$this->addColumn ( 'file', 'public_access', 'boolean DEFAULT 0' );
	}
	public function down() {
		$this->dropColumn ( 'file', 'public_access' );
		return true;
	}
}
