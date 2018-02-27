<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit;


use humhub\modules\user\tests\codeception\fixtures\UserFixture;
use tests\codeception\_support\HumHubDbTestCase;

class ActiveRecordTest extends HumHubDbTestCase
{
    /**
     * Test for ActiveRecord of HumHub
     * @see \humhub\components\ActiveRecord
     */
    public function testCoreActiveRecordModels()
    {
        /**
         * TODO: becomeUser should be remove. ActiveRecord should not be required User session
         */
        $this->getYiiModule()->haveFixtures([UserFixture::class]);
        $this->becomeUser('Admin');

        foreach ($this->getCoreActiveRecordsModels() as $activeRecordName) {
            /** @var \humhub\components\ActiveRecord $activeRecord */
            $activeRecord = new $activeRecordName;
            if (isset($activeRecord->fileManager)) {
                $this->assertInstanceOf(\humhub\modules\file\components\FileManager::class, $activeRecord->fileManager);
                codecept_debug('Used fileManager - ' . $activeRecordName);
            } else {
                codecept_debug('Not Used fileManager - ' . $activeRecordName);
            }
        }

        foreach ($this->getCoreActiveRecordsModels() as $activeRecordName) {
            /** @var \humhub\components\ActiveRecord $activeRecord */
            $activeRecord = new $activeRecordName;
            $this->assertNull($activeRecord->createdBy, $activeRecordName);
            $this->assertNull($activeRecord->updatedBy, $activeRecordName);
            $this->assertEquals('', $activeRecord->errorMessage, $activeRecordName);
            $this->assertNotEmpty($activeRecord->uniqueId, $activeRecordName);

            /**
             * Some core ActiveRecord not use functional from beforeSave()
             * Some core ActiveRecord use only updated_by, created_by - not
             * See test info by --debug flag
             */
            if ($activeRecord->hasAttribute('created_by')
                && $activeRecord->hasAttribute('created_at')
                && $activeRecord->hasAttribute('updated_by')
                && $activeRecord->hasAttribute('updated_at')) {

                codecept_debug('Used beforeSave() - ' . $activeRecordName);

                // before create record
                $this->assertTrue($activeRecord->beforeSave(true));
                $this->assertNotNull($activeRecord->created_by, $activeRecordName);
                $this->assertNotNull($activeRecord->created_at, $activeRecordName);
                $this->assertNotNull($activeRecord->updated_by, $activeRecordName);
                $this->assertNotNull($activeRecord->updated_at, $activeRecordName);
                $this->assertEquals('', $activeRecord->errorMessage, $activeRecordName);

                // before update record
                $activeRecord->updated_by = null;
                $activeRecord->updated_at = null;
                $this->assertTrue($activeRecord->beforeSave(false));
                $this->assertNotNull($activeRecord->created_by, $activeRecordName);
                $this->assertNotNull($activeRecord->created_at, $activeRecordName);
                $this->assertNotNull($activeRecord->updated_by, $activeRecordName);
                $this->assertNotNull($activeRecord->updated_at, $activeRecordName);
                $this->assertEquals('', $activeRecord->errorMessage, $activeRecordName);
            } else {
                codecept_debug('Missed beforeSave() - ' . $activeRecordName);
            }
        }
    }

    protected function getCoreActiveRecordsModels()
    {
        return [
            \humhub\modules\content\models\ContentTag::class,
            \humhub\modules\content\models\ContentTagRelation::class,
            \humhub\modules\friendship\models\Friendship::class,
            \humhub\modules\live\models\Live::class,
            \humhub\modules\notification\models\Notification::class,
            \humhub\modules\space\models\Membership::class,
            \humhub\modules\user\models\Group::class,
            \humhub\modules\user\models\GroupUser::class,
            // \humhub\modules\user\models\Invite::class,
            \humhub\modules\user\models\Mentioning::class,
            \humhub\modules\user\models\ProfileField::class,
            \humhub\modules\user\models\ProfileFieldCategory::class,
            \humhub\modules\user\models\Session::class,

            /**
             * This ActiveRecord's haven't tables in database.
             * TODO: Think about ActiveRecord functional, may be better use trait...
             */
            // \humhub\modules\content\components\ContentActiveRecord::class,
            // \humhub\modules\content\components\ContentAddonActiveRecord::class,
            // \humhub\modules\content\components\ContentContainerActiveRecord::class,
            // \humhub\modules\file\models\FileCompat::class,
            // \humhub\modules\user\models\GroupAdmin::class,
        ];
    }
}
