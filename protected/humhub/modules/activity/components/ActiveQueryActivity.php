<?php

namespace humhub\modules\activity\components;

use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\live\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;

class ActiveQueryActivity extends ActiveQuery
{

    public function init()
    {
        parent::init();

        $this->leftJoin('content', 'content.id = activity.content_id');
        $this->leftJoin('user', 'user.id = activity.created_by');
    }

    public function visible(): static
    {
        $this->andWhere([
            'OR',
            'user.id IS NULL',
            [
                'AND',
                ['!=', 'user.status', User::STATUS_NEED_APPROVAL],
                ['!=', 'user.visibility', User::VISIBILITY_HIDDEN]
            ]
        ]);

        return $this;
    }

    public function excludeUser(User $user): static
    {
        // Exclude Own Activites
        $this->andWhere(['!=', 'activity.created_by', $user->id]);

        return $this;
    }

    public function contentContainer(ContentContainer $contentContainer, User $user): static
    {
        $this->andWhere(['activity.contentcontainer_id' => $contentContainer->id]);

        // ToDO: Check if given user has access to private content
        $this->andWhere(['content.visibility' => Content::VISIBILITY_PUBLIC]);

        return $this;
    }

    public function subscribedContentContainers(User $user): static
    {
        /** @var Module $liveModule */
        $liveModule = Yii::$app->getModule('live');
        $containerIds = $liveModule->getLegitimateContentContainerIds($user);

        // Fix Empty Array IN Condition Query Builder Problem
        if (empty($containerIds[Content::VISIBILITY_PUBLIC])) {
            $containerIds[Content::VISIBILITY_PUBLIC][] = -1;
        }

        $this->andWhere([
            'OR',
            // Content of Private/Owner in all Visibilities
            [
                'IN',
                'activity.contentcontainer_id',
                array_merge($containerIds[Content::VISIBILITY_PRIVATE], $containerIds[Content::VISIBILITY_OWNER])
            ],
            // Content of Public, in Public Only
            [
                'AND',
                ['IN', 'activity.contentcontainer_id', $containerIds[Content::VISIBILITY_PUBLIC]],
                ['content.visibility' => Content::VISIBILITY_PUBLIC],
            ]
        ]);

        return $this;
    }
}
