<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;

/**
 * SpaceSearch for administration
 *
 * @author luke
 */
class SpaceSearch extends Space
{

    public $freeText;
    public $memberCount;
    public $owner;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'visibility', 'join_policy'], 'integer'],
            [['freeText'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        //Bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public static function className()
    {
        return Space::class;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'memberCount' => 'Members'
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $memberCountSubSelect = Membership::find()->select('COUNT(*) as counter')->where('space_id=space.id')->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);
        $query = self::find();
        $query->joinWith(['ownerUser', 'ownerUser.profile']);
        $query->addSelect(['space.*', 'memberCount' => $memberCountSubSelect]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'name',
                'visibility',
                'join_policy',
                'memberCount',
            ]
        ]);
        $dataProvider->sort->attributes['ownerUser.profile.lastname'] = [
            'asc' => ['profile.lastname' => SORT_ASC],
            'desc' => ['profile.lastname' => SORT_DESC],
        ];
        $this->load($params);

        if (!$this->validate()) {
            $query->emulateExecution();
            return $dataProvider;
        }


        // Freetext filters
        if (!empty($this->freeText)) {
            $query->andWhere([
                'OR',
                ['like', 'space.name', $this->freeText],
                ['like', 'user.id', $this->freeText],
                ['like', 'user.username', $this->freeText],
                ['like', 'user.email', $this->freeText],
                ['like', 'profile.firstname', $this->freeText],
                ['like', 'profile.lastname', $this->freeText]
            ]);
        }

        if ($this->visibility == Space::VISIBILITY_NONE) {
            $query->andFilterWhere(['space.visibility' => Space::VISIBILITY_NONE]);
        } else {
            $query->andWhere([
                'OR',
                ['space.visibility' => Space::VISIBILITY_REGISTERED_ONLY],
                ['space.visibility' => Space::VISIBILITY_ALL]
            ]);
        }

        return $dataProvider;
    }

    public function getVisibilityAttributes()
    {
        $countPublic = Space::find()->where(['visibility' => Space::VISIBILITY_ALL])->orWhere(['visibility' => Space::VISIBILITY_REGISTERED_ONLY])->count();
        $countPrivate = Space::find()->where(['visibility' => Space::VISIBILITY_NONE])->count();

        return [
            Space::VISIBILITY_REGISTERED_ONLY => Yii::t('SpaceModule.base', 'Public') . ' (' . $countPublic . ')',
            Space::VISIBILITY_NONE => Yii::t('SpaceModule.base', 'Private') . ' (' . $countPrivate . ')',
        ];
    }

}
