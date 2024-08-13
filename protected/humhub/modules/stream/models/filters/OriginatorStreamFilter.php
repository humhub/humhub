<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\stream\models\filters;


use humhub\modules\stream\models\filters\StreamQueryFilter;
use humhub\modules\user\models\User;

class OriginatorStreamFilter extends StreamQueryFilter
{

    public $originators = [];

    public function rules() {
        return [
            [['originators'], 'safe']
        ];
    }

    public function init() {
        parent::init();
        if($this->streamQuery->originator) {
            $this->originators = [$this->streamQuery->originator];
        }
    }

    public function apply()
    {

        if(empty($this->originators)) {
            return;
        }

        if($this->originators instanceof User) {
            $this->originators = [$this->originators->id];
        } elseif(!is_array($this->originators)) {
            $this->originators = [$this->originators];
        }

        $this->query->joinWith('contentContainer');

        if (count($this->originators) === 1) {
            $this->query->andWhere(["user.guid" => $this->originators[0]]);
        } elseif (!empty($this->originators)) {
            $this->query->andWhere(['IN', 'user.guid', $this->originators]);
        }
    }
}
