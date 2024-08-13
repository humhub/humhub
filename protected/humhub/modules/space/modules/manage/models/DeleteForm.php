<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\models;

use humhub\modules\user\components\CheckPasswordValidator;
use Yii;
use yii\base\Model;

/**
 * Form Model for Space Deletion
 *
 * @since 0.5
 */
class DeleteForm extends Model
{

    /**
     * @var string the space name to check
     */
    public $spaceName;


    /**
     * @var string the space name given by the user
     */
    public $confirmSpaceName;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['confirmSpaceName', 'required'],
            ['confirmSpaceName', 'compare', 'compareValue' => $this->spaceName],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'confirmSpaceName' => Yii::t('SpaceModule.manage', 'Space name'),
        ];
    }

}
