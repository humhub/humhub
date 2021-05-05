<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PeopleFilters;
use Yii;
use yii\data\Pagination;

/**
 * PeopleQuery is used to query User records on the People page.
 *
 * @author luke
 */
class PeopleQuery extends ActiveQueryUser
{
    /**
     * @var Group
     */
    public $filteredGroup;

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * @var int
     */
    public $pageSize = 10;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct(User::class, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->visible();

        $this->filterByKeyword();
        $this->filterByGroup();

        $this->order();

        $this->paginate();
    }

    public function filterByKeyword()
    {
        $keyword = Yii::$app->request->get('keyword', '');

        return $this->search($keyword);
    }

    public function filterByGroup()
    {
        $groupId = Yii::$app->request->get('groupId', 0);

        if ($groupId) {
            $group = Group::findOne(['id' => $groupId, 'show_at_directory' => 1]);
            if ($group) {
                $this->filteredGroup = $group;
                $this->isGroupMember($group);
            }
        }

        return $this;
    }

    public function isFilteredByGroup(): bool
    {
        return $this->filteredGroup instanceof Group;
    }

    public function order()
    {
        switch (PeopleFilters::getOrder()) {
            case 'firstname':
                $this->joinWith('profile');
                $this->addOrderBy('profile.firstname');
                break;

            case 'lastname':
                $this->joinWith('profile');
                $this->addOrderBy('profile.lastname');
                break;

            case 'lastlogin':
                $this->addOrderBy('last_login DESC');
                break;
        }

        return $this;
    }

    public function paginate()
    {
        $countQuery = clone $this;
        $this->pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $this->pageSize]);

        return $this->offset($this->pagination->offset)->limit($this->pagination->limit);
    }

}
