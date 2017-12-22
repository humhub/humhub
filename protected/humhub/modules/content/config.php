<?php

use humhub\modules\content\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\search\engine\Search;
use humhub\modules\content\components\ContentActiveRecord;

return [
    'id' => 'content',
    'class' => \humhub\modules\content\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')],
        ['class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')],
        ['class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => Space::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onSpaceDelete']],
        ['class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => [Events::className(), 'onSearchRebuild']],
        ['class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_AFTER_INSERT, 'callback' => [Events::className(), 'onContentActiveRecordSave']],
        ['class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_AFTER_UPDATE, 'callback' => [Events::className(), 'onContentActiveRecordSave']],
        ['class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_AFTER_DELETE, 'callback' => [Events::className(), 'onContentActiveRecordDelete']],
    ),
];
?>