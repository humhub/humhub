<?php

use humhub\components\Migration;
use humhub\models\Setting;
use humhub\modules\content\models\ContentContainerSetting;
use yii\db\Query;

/**
 * The activity classes were renamed in `m260108_115053_new_structure` (1.19.0), but only in the `activity` table.
 * This migration renames them also in the list of excluded activities of the e-mail summaries
 * (`mailSummaryActivitySuppress`), both the global default and the per-user values.
 */
class m261027_090000_mail_summary_suppress_class_names extends Migration
{
    private const SETTING_NAME = 'mailSummaryActivitySuppress';
    private const MODULE_ID = 'activity';

    public function up()
    {
        // Fixed map (not read from other migrations), as this migration must not change when classes are renamed again
        $renameClasses = [
            'humhub\modules\comment\activities\NewComment' => 'humhub\modules\comment\activities\NewCommentActivity',
            'humhub\modules\like\activities\Liked' => 'humhub\modules\like\activities\LikeActivity',
            'humhub\modules\content\activities\ContentCreated' => 'humhub\modules\content\activities\ContentCreatedActivity',
            'humhub\modules\space\activities\Created' => 'humhub\modules\space\activities\SpaceCreatedActivity',
            'humhub\modules\space\activities\MemberAdded' => 'humhub\modules\space\activities\MemberAddedActivity',
            'humhub\modules\space\activities\MemberRemoved' => 'humhub\modules\space\activities\MemberRemovedActivity',
            'humhub\modules\space\activities\SpaceArchived' => 'humhub\modules\space\activities\SpaceArchivedActivity',
            'humhub\modules\space\activities\SpaceUnArchived' => 'humhub\modules\space\activities\SpaceUnArchivedActivity',
            'humhub\modules\user\activities\UserFollow' => 'humhub\modules\user\activities\FollowActivity',
        ];

        $condition = ['name' => self::SETTING_NAME, 'module_id' => self::MODULE_ID];

        $globalChanged = false;
        foreach ((new Query())->select(['value'])->from('setting')->where($condition)->column() as $value) {
            $newValue = self::renameInList((string)$value, $renameClasses);
            if ($newValue !== (string)$value) {
                $this->updateSilent('setting', ['value' => $newValue], $condition);
                $globalChanged = true;
            }
        }

        $rows = (new Query())
            ->select(['contentcontainer_id', 'value'])
            ->from('contentcontainer_setting')
            ->where($condition)
            ->all();
        foreach ($rows as $row) {
            $newValue = self::renameInList((string)$row['value'], $renameClasses);
            if ($newValue !== (string)$row['value']) {
                $this->updateSilent(
                    'contentcontainer_setting',
                    ['value' => $newValue],
                    $condition + ['contentcontainer_id' => $row['contentcontainer_id']],
                );
                $this->deleteCache(ContentContainerSetting::getCacheKey(self::MODULE_ID, $row['contentcontainer_id']));
            }
        }

        if ($globalChanged) {
            $this->deleteCache(Setting::getCacheKey(self::MODULE_ID));
        }
    }

    public function down()
    {
        echo "m261027_090000_mail_summary_suppress_class_names cannot be reverted.\n";

        return false;
    }

    /**
     * Renames whole entries of a comma separated class list, so `MemberAdded` is not matched in `MemberAddedActivity`.
     */
    private static function renameInList(string $list, array $renameClasses): string
    {
        $entries = array_map(
            static fn(string $entry) => $renameClasses[$entry] ?? $entry,
            array_map('trim', explode(',', $list)),
        );

        return implode(',', array_unique($entries));
    }

    private function deleteCache(string $key): void
    {
        Yii::$app->cache?->delete($key);
    }
}
