<?php
// models/DigitalAsset.php
namespace app\humhub\modules\prompt\models;

use Yii;
use yii\db\ActiveRecord;

class DigitalAsset extends ActiveRecord
{
    public static function tableName()
    {
        return 'digital_asset';
    }

    public function rules()
    {
        return [
            [['name', 'description', 'creator_id', 'asset_url'], 'required'],
            [['description'], 'string'],
            [['creator_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['asset_url'], 'url'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'description' => 'Description',
            'creator_id' => 'Creator ID',
            'asset_url' => 'Asset URL',
        ];
    }
}