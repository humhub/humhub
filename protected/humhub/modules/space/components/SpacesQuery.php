<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\SpacesFilters;
use Yii;
use yii\data\Pagination;

/**
 * SpacesQuery is used to query Space records on the Spaces page.
 *
 * @author luke
 */
class SpacesQuery extends ActiveQuerySpace
{

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @var int
     */
    public $pageSize = 18;

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

        $this->filterByKeyword();
        $this->filterByConnection();

        $this->order();

        $this->paginate();
    }

    public function filterByKeyword(): SpacesQuery
    {
        $keyword = Yii::$app->request->get('keyword', '');

        return $this->search($keyword);
    }

    public function filterByConnection(): SpacesQuery
    {
        switch (Yii::$app->request->get('connection')) {
            case 'member':
                return $this->filterByConnectionMember();
            case 'follow':
                return $this->filterByConnectionFollow();
        }

        return $this;
    }

    public function filterByConnectionMember(): SpacesQuery
    {
        return $this->innerJoin('space_membership', 'space_membership.space_id = space.id')
            ->andWhere(['space_membership.user_id' => Yii::$app->user->id])
            ->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);
    }

    public function filterByConnectionFollow(): SpacesQuery
    {
        return $this->innerJoin('user_follow', 'user_follow.object_model = :space_class AND user_follow.object_id = space.id', [':space_class' => Space::class])
            ->andWhere(['user_follow.user_id' => Yii::$app->user->id]);
    }

    public function order(): SpacesQuery
    {
        switch (SpacesFilters::getValue('sort')) {
            case 'name':
                $this->addOrderBy('name');
                break;

            case 'newer':
                $this->addOrderBy('created_at DESC');
                break;

            case 'older':
                $this->addOrderBy('created_at');
                break;
        }

        return $this;
    }

    public function paginate(): SpacesQuery
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
