<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models;

use yii\base\InvalidArgumentException;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use humhub\modules\user\models\User;

/**
 * Description of UserSearch
 *
 * @author luke
 */
class UserSearch extends User
{

    /**
     * @var \humhub\modules\user\components\ActiveQueryUser
     */
    public $query;

    /**
     * @var string a free text search
     */
    public $freeText;

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), ['profile.firstname', 'profile.lastname']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'created_at', 'profile.firstname', 'profile.lastname', 'last_login', 'freeText'], 'safe'],
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
        $query = ($this->query == null) ? User::find() : $this->query;
        /* @var $query \humhub\modules\user\components\ActiveQueryUser */
        $query->joinWith('profile');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);
        $dataProvider->setSort([
            'attributes' => [
                'id',
                'username',
                'email',
                'last_login',
                'profile.firstname',
                'profile.lastname',
                'created_at',
            ]
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        $this->load($params);

        if (!$this->validate()) {
            $query->emulateExecution();
            return $dataProvider;
        }


        $query->joinWith(['profile']);


        // Freetext filters
        if (!empty($this->freeText)) {
            $query->andWhere([
                'OR',
                ['like', 'user.id', $this->freeText],
                ['like', 'user.username', $this->freeText],
                ['like', 'user.email', $this->freeText],
                ['like', 'profile.firstname', $this->freeText],
                ['like', 'profile.lastname', $this->freeText],
                ['like', 'concat(profile.firstname, " ", profile.lastname)', $this->freeText],
                ['like', 'concat(profile.lastname, " ", profile.firstname)', $this->freeText],
            ]);

            if (!empty($this->status)) {
                $query->andFilterWhere(['user.status' => $this->status]);
            }
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['user.status' => $this->status]);
        $query->andFilterWhere(['like', 'user.id', $this->id]);
        $query->andFilterWhere(['like', 'user.username', $this->username]);
        $query->andFilterWhere(['like', 'user.email', $this->email]);
        $query->andFilterWhere(['like', 'profile.firstname', $this->getAttribute('profile.firstname')]);
        $query->andFilterWhere(['like', 'profile.lastname', $this->getAttribute('profile.lastname')]);



        if ($this->getAttribute('last_login') != "") {
            try {
                $last_login = \humhub\libs\DateHelper::parseDateTime($this->getAttribute('last_login'));

                $query->andWhere([
                    '=',
                    new \yii\db\Expression("DATE(last_login)"),
                    new \yii\db\Expression("DATE(:last_login)", [':last_login' => $last_login])
                ]);
            } catch (InvalidArgumentException $e) {
                // do not change the query if the date is wrong formatted
            }
        }

        return $dataProvider;
    }

    public static function getStatusAttributes()
    {
        $countActive = User::find()->where(['user.status' => User::STATUS_ENABLED])->count();
        $countDisabled = User::find()->where(['user.status' => User::STATUS_DISABLED])->count();
        $countSoftDeleted = User::find()->where(['user.status' => User::STATUS_SOFT_DELETED])->count();

        return [
            User::STATUS_ENABLED => Yii::t('AdminModule.user', 'Active users') . ' (' . $countActive . ')',
            User::STATUS_DISABLED => Yii::t('AdminModule.user', 'Disabled users') . ' (' . $countDisabled . ')',
            User::STATUS_SOFT_DELETED => Yii::t('AdminModule.user', 'Deleted users') . ' (' . $countSoftDeleted . ')',
        ];
    }

}
