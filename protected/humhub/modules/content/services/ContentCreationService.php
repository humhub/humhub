<?php

namespace humhub\modules\content\services;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\post\permissions\CreatePost;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use Yii;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * @since 1.17.2
 */
class ContentCreationService
{
    private ?ContentActiveRecord $record = null;

    private ?IdentityInterface $user = null;

    public function __construct(?ContentActiveRecord $contentRecord = null, ?IdentityInterface $user = null)
    {
        $this->record = $contentRecord;
        $this->user = $user ?? Yii::$app->user->identity;
    }

    public function searchSpaces(?string $keyword = null): array
    {
        $spaces = Space::find()
            ->visible($this->user)
            ->filterBlockedSpaces($this->user)
            ->andWhere(['space.status' => Space::STATUS_ENABLED])
            ->search($keyword);

        if ($this->record?->content?->container instanceof Space) {
            $spaces->andWhere(['!=', 'space.id', $this->record->content->container->id]);
        }

        if (!$this->user->isSystemAdmin()) {
            // Check the User can create a Post in the searched Spaces
            $spaces->leftJoin('space_membership', 'space_membership.space_id = space.id')
                ->leftJoin(
                    'contentcontainer_permission',
                    'contentcontainer_permission.contentcontainer_id = space.contentcontainer_id
                    AND contentcontainer_permission.group_id = space_membership.group_id
                    AND contentcontainer_permission.permission_id = :permission_id',
                )
                ->andWhere(['space_membership.user_id' => $this->user->id])
                ->andWhere(['OR',
                    // Allowed by default
                    ['AND',
                        ['IN', 'space_membership.group_id', $this->getDefaultAllowedGroups()],
                        ['IS', 'contentcontainer_permission.permission_id', new Expression('NULL')],
                    ],
                    // Set to allow
                    ['contentcontainer_permission.state' => CreatePost::STATE_ALLOW],
                ])
                ->addParams(['permission_id' => CreatePost::class]);
        }

        $result = [];
        foreach ($spaces->all() as $space) {
            $result[] = Chooser::getSpaceResult($space);
        }

        return $result;
    }
}
