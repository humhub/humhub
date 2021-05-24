<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\PeopleFilters;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;

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
        $this->filterByConnection();
        $this->filterByProfileFields();

        $this->order();

        $this->paginate();
    }

    public function filterByKeyword()
    {
        $keyword = Yii::$app->request->get('keyword', '');

        return $this->search($keyword);
    }

    public function filterByProfileFields()
    {
        $fields = Yii::$app->request->get('fields', []);

        // Remove empty filters
        $fields = array_filter($fields, function($value) {
            return $value !== '';
        });

        if (empty($fields)) {
            return $this;
        }

        // Skip fields if they are not defined for directory filters
        $filteredProfileFields = ProfileField::find()
            ->where(['directory_filter' => 1])
            ->andWhere(['IN', 'internal_name', array_keys($fields)])
            ->all();
        $checkedFilteredFields = [];
        foreach ($filteredProfileFields as $filteredField) {
            /* @var $filteredField ProfileField */
            if (!isset($fields[$filteredField->internal_name])) {
                // Skip unknown field
                continue;
            }
            $checkedFilteredFields[$filteredField->internal_name] = [
                'value' => $fields[$filteredField->internal_name],
                'condition' => $filteredField->getFieldType() instanceof Select ? '=' : 'LIKE',
            ];
        }

        if (empty($checkedFilteredFields)) {
            return $this;
        }

        $this->joinWith('profile');

        foreach ($checkedFilteredFields as $field => $data) {
            $this->andWhere([$data['condition'], 'profile.' . $field, $data['value']]);
        }

        return $this;
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

    public function filterByConnection()
    {
        switch (Yii::$app->request->get('connection')) {
            case 'followers':
                $this->innerJoin('user_follow', 'user_follow.object_model = :user_class AND user_follow.user_id = user.id', [':user_class' => User::class]);
                $this->andWhere(['user_follow.object_id' => Yii::$app->user->id]);
                break;

            case 'following':
                $this->innerJoin('user_follow', 'user_follow.object_model = :user_class AND user_follow.object_id = user.id', [':user_class' => User::class]);
                $this->andWhere(['user_follow.user_id' => Yii::$app->user->id]);
                break;

            case 'friends':
                $this->innerJoin('user_friendship AS uf_current', 'uf_current.friend_user_id = user.id');
                $this->andWhere(['uf_current.user_id' => Yii::$app->user->id]);
                $this->innerJoin('user_friendship AS uf_friend', 'uf_friend.user_id = user.id');
                $this->andWhere(['uf_friend.friend_user_id' => Yii::$app->user->id]);
                break;

            case 'pending_friends':
                $this->innerJoin('user_friendship AS uf_current', 'uf_current.friend_user_id = user.id');
                $this->andWhere(['uf_current.user_id' => Yii::$app->user->id]);
                $this->leftJoin('user_friendship AS uf_friend', 'uf_friend.user_id = user.id');
                $this->andWhere(['IS', 'uf_friend.friend_user_id', new Expression('NULL')]);
                break;
        }

        return $this;
    }

    public function isFilteredByGroup(): bool
    {
        return $this->filteredGroup instanceof Group;
    }

    public function order()
    {
        switch (PeopleFilters::getValue('sort')) {
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
