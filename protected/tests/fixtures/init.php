<?php

Yii::app()->params['installed'] = false;

// Migrate up the database
Yii::import('application.commands.shell.ZMigrateCommand');
ZMigrateCommand::AutoMigrate();

Yii::app()->params['installed'] = true;

// Create empty dynamic configuration file
$content = "<" . "?php return ";
$content .= var_export(array(), true);
$content .= "; ?" . ">";
file_put_contents(Yii::app()->params['dynamicConfigFile'], $content);

foreach ($this->getFixtures() as $tableName => $fixturePath) {
    $this->resetTable($tableName);
    $this->loadFixture($tableName);
}

// initialize a controller (which defaults to null in tests)
$c = new CController('phpunit');
$c->setAction(new CInlineAction($c, 'urltest'));
Yii::app()->setController($c);


