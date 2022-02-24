<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpaceDirectoryFilters;
use Yii;
use yii\data\Pagination;

/**
 * SpaceDirectoryQuery is used to query Space records on the Spaces page.
 *
 * @author luke
 */
class SpaceDirectoryQuery extends ActiveQuerySpace
{

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @var int
     */
    public $pageSize = 25;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct(Space::class, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->visible();

        $this->filterBlockedSpaces();
        $this->filterByKeyword();
        $this->filterByConnection();

        $this->order();

        $this->paginate();
    }

    public function filterByKeyword(): SpaceDirectoryQuery
    {
        $keyword = Yii::$app->request->get('keyword', '');

        return $this->search($keyword);
    }

    public function filterByConnection(): SpaceDirectoryQuery
    {
        $connection = Yii::$app->request->get('connection');

        $this->filterByConnectionArchived($connection === 'archived');

        switch ($connection) {
            case 'member':
                return $this->filterByConnectionMember();
            case 'follow':
                return $this->filterByConnectionFollow();
            case 'none':
                return $this->filterByConnectionNone();
        }

        return $this;
    }

    public function filterByConnectionMember(): SpaceDirectoryQuery
    {
        return $this->innerJoin('space_membership', 'space_membership.space_id = space.id')
            ->andWhere(['space_membership.user_id' => Yii::$app->user->id])
            ->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);
    }

    public function filterByConnectionFollow(): SpaceDirectoryQuery
    {
        return $this->innerJoin('user_follow', 'user_follow.object_model = :spaceClass AND user_follow.object_id = space.id', [':spaceClass' => Space::class])
            ->andWhere(['user_follow.user_id' => Yii::$app->user->id]);
    }

    public function filterByConnectionNone(): SpaceDirectoryQuery
    {
        return $this->andWhere('space.id NOT IN (SELECT space_id FROM space_membership WHERE user_id = :userId AND status = :memberStatus)')
            ->andWhere('space.id NOT IN (SELECT object_id FROM user_follow WHERE user_id = :userId AND user_follow.object_model = :spaceClass)')
            ->addParams([
                ':userId' => Yii::$app->user->id,
                ':memberStatus' => Membership::STATUS_MEMBER,
                ':spaceClass' => Space::class,
            ]);
    }

    public function filterByConnectionArchived(bool $showArchived = false): SpaceDirectoryQuery
    {
        return $this->andWhere('space.status ' . ($showArchived ? '=' : '!=') . ' :spaceStatus', [
            ':spaceStatus' => Space::STATUS_ARCHIVED,
        ]);
    }

    public function order(): SpaceDirectoryQuery
    {
        switch (SpaceDirectoryFilters::getValue('sort')) {
            case 'name':
                $this->addOrderBy('space.name');
                break;

            case 'newer':
                $this->addOrderBy('space.created_at DESC');
                break;

            case 'older':
                $this->addOrderBy('space.created_at');
                break;
        }

        return $this;
    }

    public function paginate(): SpaceDirectoryQuery
    {
        $countQuery = clone $this;
        $this->pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $this->pageSize]);

        return $this->offset($this->pagination->offset)->limit($this->pagination->limit);
    }

    public function isLastPage(): bool
    {
        return $this->pagination->getPage() == $this->pagination->getPageCount() - 1;
    }

}
