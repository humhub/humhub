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
use humhub\modules\user\models\User;

/**
 * Description of UserSearch
 *
 * @author luke
 */
class UserApprovalSearch extends User
{

    public function attributes()
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), ['profile.firstname', 'profile.lastname', 'group.name', 'group.id']);
    }

    public function rules()
    {
        return [
            [['id', 'group.id'], 'integer'],
            [['username', 'email', 'created_at', 'profile.firstname', 'profile.lastname'], 'safe'],
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
     * @return ActiveDataProvider
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params = [])
    {
        $query = User::find()->joinWith(['profile']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'username',
                'email',
                'super_admin',
                'profile.firstname',
                'profile.lastname',
                'created_at',
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->administrableBy(Yii::$app->user->getIdentity());

        $query->andWhere(['user.status' => User::STATUS_NEED_APPROVAL]);
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'user.id', $this->id]);
        $query->andFilterWhere(['like', 'user.username', $this->username]);
        $query->andFilterWhere(['like', 'user.email', $this->email]);
        $query->andFilterWhere(['like', 'profile.firstname', $this->getAttribute('profile.firstname')]);
        $query->andFilterWhere(['like', 'profile.lastname', $this->getAttribute('profile.lastname')]);

        return $dataProvider;
    }

    public static function getUserApprovalCount()
    {
        return User::find()->where(['user.status' => User::STATUS_NEED_APPROVAL])->count();
    }

}
