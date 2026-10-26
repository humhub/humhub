<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\table\ColumnDefinition;
use humhub\components\table\DataTable;
use humhub\components\table\RowAction;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;

/**
 * The administration's space table (Administration › Spaces): the columns, row actions and the
 * list ({@see AdminSpaceList}) of `admin\widgets\SpaceTable` and `GET /api/v2/admin/space`.
 * Created through the DI container, so a module may replace it or add columns on
 * {@see self::EVENT_INIT} (e.g. a space category).
 *
 * @since 1.20
 */
class SpaceTable extends DataTable
{
    /**
     * @inheritdoc
     */
    protected function createList(): FilterableList
    {
        return new AdminSpaceList();
    }

    /**
     * @inheritdoc
     */
    protected function columns(): array
    {
        return [
            new ColumnDefinition('name', ColumnDefinition::TYPE_MAIN, Yii::t('SpaceModule.base', 'Name'), sort: 'name', sortOrder: 100),
            new ColumnDefinition('members', ColumnDefinition::TYPE_NUMBER, Yii::t('SpaceModule.base', 'Members'), priority: 1, sort: 'members', sortOrder: 200),
            new ColumnDefinition('owner', ColumnDefinition::TYPE_TEXT, Yii::t('SpaceModule.base', 'Owner'), priority: 3, sort: 'owner', sortOrder: 300, visible: false),
            new ColumnDefinition('visibility', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.space', 'Visibility'), priority: 2, sort: 'visibility', filter: 'visibility', sortOrder: 400),
            new ColumnDefinition('status', ColumnDefinition::TYPE_CHIP, Yii::t('AdminModule.space', 'Status'), priority: 1, sort: 'status', filter: 'status', sortOrder: 500),
            new ColumnDefinition('createdAt', ColumnDefinition::TYPE_DATE, Yii::t('AdminModule.space', 'Created'), priority: 3, sort: 'createdAt', sortOrder: 600, visible: false),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function actions(): array
    {
        // The space's own pages, through the administration's deep link
        $open = static fn(?string $section = null) => static fn(Space $space) => array_filter(['/admin/space/open', 'id' => $space->id, 'section' => $section]);

        return [
            new RowAction('edit', Yii::t('base', 'Edit'), RowAction::KIND_LINK, $open('edit'), icon: 'pencil', sortOrder: 100),
            new RowAction('members', Yii::t('AdminModule.space', 'Manage members'), RowAction::KIND_LINK, $open('members'), icon: 'users', sortOrder: 200),
            new RowAction('owner', Yii::t('AdminModule.space', 'Change owner'), RowAction::KIND_LINK, $open('owner'), icon: 'user-share', sortOrder: 300),
            new RowAction('modules', Yii::t('AdminModule.space', 'Manage modules'), RowAction::KIND_LINK, $open('modules'), icon: 'puzzle', sortOrder: 400),
            new RowAction('open', Yii::t('AdminModule.space', 'Open space'), RowAction::KIND_LINK, static fn(Space $space) => $space->getUrl(), icon: 'external-link', sortOrder: 500),
            new RowAction('delete', Yii::t('base', 'Delete'), RowAction::KIND_LINK, $open('delete'), icon: 'trash', destructive: true, sortOrder: 100),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function cells($model): array
    {
        /** @var Space $model */
        $owner = $model->ownerUser;

        return [
            'name' => [
                'text' => $model->name,
                'subtitle' => $model->description ?: null,
                // Without an image of its own, the space's initials on its colour, as `space\widgets\Image`
                'image' => $model->image->exists() ? $model->getProfileImage()->getUrl() : null,
                'initials' => self::initials($model->name),
                'color' => $model->color ?: null,
                'url' => $model->getUrl(),
            ],
            'members' => ['text' => (string)Membership::find()
                ->where(['space_id' => $model->id, 'status' => Membership::STATUS_MEMBER])
                ->count()],
            'owner' => $owner !== null ? ['text' => $owner->displayName] : null,
            'visibility' => [$model->visibility == Space::VISIBILITY_NONE
                ? ['text' => Yii::t('SpaceModule.base', 'Private'), 'value' => AdminSpaceList::VISIBILITY_PRIVATE]
                : ['text' => Yii::t('SpaceModule.base', 'Public'), 'value' => AdminSpaceList::VISIBILITY_PUBLIC]],
            'status' => self::statusCell((int)$model->status),
            'createdAt' => $model->created_at ? [
                'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:c'),
                'text' => Yii::$app->formatter->asDate($model->created_at),
                'title' => Yii::$app->formatter->asDatetime($model->created_at),
            ] : null,
        ];
    }

    /**
     * Whether the viewer may see the table: those who may manage spaces.
     */
    public function canAccess(): bool
    {
        return Yii::$app->user->can(ManageSpaces::class);
    }

    /**
     * The initials of the space's name, as `space\widgets\Image::getAcronym()`.
     */
    private static function initials(string $name): string
    {
        $initials = '';
        foreach (preg_split('/\s+/u', preg_replace('/[^\p{L}\d\s]+/u', '', $name), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return mb_substr(mb_strtoupper($initials), 0, 2);
    }

    /**
     * The space's status as a chip setting the list's `status` filter.
     */
    private static function statusCell(int $status): ?array
    {
        return match ($status) {
            Space::STATUS_ENABLED => [['text' => Yii::t('AdminModule.space', 'Active'), 'value' => AdminSpaceList::STATUS_ACTIVE, 'tone' => 'success']],
            Space::STATUS_ARCHIVED => [['text' => Yii::t('SpaceModule.base', 'Archived'), 'value' => AdminSpaceList::STATUS_ARCHIVED]],
            default => null,
        };
    }
}
