<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Filter of the grants list in the administration.
 *
 * @since 1.20
 */
class GrantSearch extends Model
{
    public $user;
    public $client_id;
    public $type;

    public function rules()
    {
        return [
            ['user', 'string', 'max' => 100],
            ['client_id', 'integer'],
            ['type', 'in', 'range' => [Grant::TYPE_OAUTH, Grant::TYPE_PAT]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user' => Yii::t('OauthModule.base', 'User'),
            'client_id' => Yii::t('OauthModule.base', 'Client'),
            'type' => Yii::t('OauthModule.base', 'Type'),
        ];
    }

    /**
     * The grants matching the filter. Shared by the list and "Revoke all", so both always act on the same set.
     */
    public function query(): ActiveQuery
    {
        $query = Grant::find()->alias('g');

        if (!$this->validate()) {
            // An invalid filter must never widen the set
            return $query->andWhere('0=1');
        }

        if ($this->user !== null && trim($this->user) !== '') {
            $like = trim($this->user);
            $query->andWhere(['in', 'g.user_id', (new Query())
                ->select('user.id')
                ->from('user')
                ->leftJoin('profile', 'profile.user_id = user.id')
                ->where(['or',
                    ['like', 'user.username', $like],
                    ['like', 'user.email', $like],
                    ['like', 'profile.firstname', $like],
                    ['like', 'profile.lastname', $like],
                    ['like', 'concat(profile.firstname, " ", profile.lastname)', $like],
                ])]);
        }

        $query->andFilterWhere(['g.client_id' => $this->client_id, 'g.type' => $this->type]);

        return $query;
    }

    /**
     * Whether any filter is set; bulk actions refuse to run without one.
     */
    public function hasFilter(): bool
    {
        return $this->getFilter() !== [];
    }

    /**
     * @return array<string, string> the set filter values; anything but a non-empty string is ignored
     */
    public function getFilter(): array
    {
        return array_filter($this->getAttributes(), static fn($value) => is_string($value) && trim($value) !== '');
    }

    public function search(array $params): ActiveDataProvider
    {
        $this->load($params);

        return new ActiveDataProvider([
            'query' => $this->query()->with(['user.profile', 'client']),
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['created_at', 'last_used_at', 'expires_at'],
            ],
            'pagination' => ['pageSize' => 50],
        ]);
    }
}
