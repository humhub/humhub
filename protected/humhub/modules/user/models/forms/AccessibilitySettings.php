<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use humhub\components\Appearance;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Account → Appearance & Accessibility → Accessibility
 *
 * @since 1.20
 */
class AccessibilitySettings extends Model
{
    public $contrast;
    public $motion;
    public $font;

    public User $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $appearance = Yii::$app->appearance;
        $this->contrast = $appearance->get(Appearance::CONTRAST, $this->user);
        $this->motion = $appearance->get(Appearance::MOTION, $this->user);
        $this->font = $appearance->get(Appearance::FONT, $this->user) === Appearance::ON;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contrast', 'motion'], 'in', 'range' => array_keys($this->getOptions())],
            ['font', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contrast' => Yii::t('UserModule.account', 'High contrast'),
            'motion' => Yii::t('UserModule.account', 'Reduce motion'),
            'font' => Yii::t('UserModule.account', 'Readable font'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        $appearance = Yii::$app->appearance;
        $options = $this->getOptions();

        return [
            'contrast' => Yii::t('UserModule.account', 'Stronger text and border colours, underlined links and a clearly visible focus.')
                . ' ' . Yii::t('UserModule.account', 'Default: {value}', ['value' => $options[$appearance->getDefault(Appearance::CONTRAST)]]),
            'motion' => Yii::t('UserModule.account', 'Turns off animations and transitions.')
                . ' ' . Yii::t('UserModule.account', 'Default: {value}', ['value' => $options[$appearance->getDefault(Appearance::MOTION)]]),
            'font' => Yii::t('UserModule.account', 'Lexend, a font designed for easier reading, also with dyslexia.')
                . ' ' . Yii::t('UserModule.account', 'Default: {value}', ['value' => $options[$appearance->getDefault(Appearance::FONT)]]),
        ];
    }

    public function getOptions(): array
    {
        return [
            Appearance::SYSTEM => Yii::t('UserModule.account', 'Use system setting'),
            Appearance::OFF => Yii::t('UserModule.account', 'Off'),
            Appearance::ON => Yii::t('UserModule.account', 'On'),
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $appearance = Yii::$app->appearance;
        $appearance->set(Appearance::CONTRAST, $this->contrast, $this->user);
        $appearance->set(Appearance::MOTION, $this->motion, $this->user);
        $appearance->set(Appearance::FONT, $this->font ? Appearance::ON : Appearance::OFF, $this->user);

        return true;
    }
}
