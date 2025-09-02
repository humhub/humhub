<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models;

use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Description of GroupSearch
 *
 * @author luke
 */
class GroupSearch extends Group
{
    public $type;

    public function rules()
    {
        return [
            [['name', 'description', 'type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Group::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'name',
                'descriptions',
                'type' => [
                    'asc' => ['`parent_group_id` IS NULL' => SORT_ASC, 'name' => SORT_ASC],
                    'desc' => ['`parent_group_id` IS NULL' => SORT_DESC, 'name' => SORT_ASC],
                ],
            ],
            'defaultOrder' => [
                'type' => SORT_DESC,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'description', $this->description]);

        if (!empty($this->type)) {
            $operator = $this->type === EditGroupForm::TYPE_NORMAL ? 'IS' : 'IS NOT';
            $query->andFilterWhere([$operator, 'parent_group_id', new Expression('NULL')]);
        }

        return $dataProvider;
    }

}
