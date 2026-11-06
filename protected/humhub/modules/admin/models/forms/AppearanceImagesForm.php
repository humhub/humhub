<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\file\validators\ImageSquareValidator;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Administration → Appearance → Logo & Images
 *
 * @since 1.20
 */
class AppearanceImagesForm extends Model
{
    public const LOGO_MIN_WIDTH = 100;
    public const LOGO_MIN_HEIGHT = 120;
    public const LOGO_RECOMMENDED_MIN_HEIGHT = 248;

    public const ICON_MIN_WIDTH = 256;
    public const ICON_MIN_HEIGHT = 256;
    public const ICON_RECOMMENDED_WIDTH = 512;
    public const ICON_RECOMMENDED_HEIGHT = 512;

    public const LOGIN_BG_MIN_WIDTH = 800;
    public const LOGIN_BG_MIN_HEIGHT = 600;
    public const LOGIN_BG_RECOMMENDED_WIDTH = 1920;
    public const LOGIN_BG_RECOMMENDED_HEIGHT = 1080;

    public const MAIL_HEADER_MIN_WIDTH = 50;
    public const MAIL_HEADER_MIN_HEIGHT = 50;
    public const MAIL_HEADER_RECOMMENDED_WIDTH = 600;
    public const MAIL_HEADER_RECOMMENDED_HEIGHT = 150;

    /**
     * Form attribute => image of `Yii::$app->img`
     */
    public const IMAGES = [
        'logo' => 'logo',
        'icon' => 'icon',
        'loginBackgroundImage' => 'loginBackground',
        'mailHeaderImage' => 'mailHeader',
    ];

    public $logo;
    public $icon;
    public $loginBackgroundImage;
    public $mailHeaderImage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['logo', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => static::LOGO_MIN_WIDTH, 'minHeight' => static::LOGO_MIN_WIDTH],
            ['icon', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => static::ICON_MIN_WIDTH, 'minHeight' => static::ICON_MIN_HEIGHT],
            ['icon', ImageSquareValidator::class],
            ['loginBackgroundImage', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => static::LOGIN_BG_MIN_WIDTH, 'minHeight' => static::LOGIN_BG_MIN_HEIGHT],
            ['mailHeaderImage', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => static::MAIL_HEADER_MIN_WIDTH, 'minHeight' => static::MAIL_HEADER_MIN_HEIGHT],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'logo' => Yii::t('AdminModule.settings', 'Logo upload'),
            'icon' => Yii::t('AdminModule.settings', 'Icon upload'),
            'loginBackgroundImage' => Yii::t('AdminModule.settings', 'Login Background'),
            'mailHeaderImage' => Yii::t('AdminModule.settings', 'Email Header Image'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'logo' => Yii::t('AdminModule.settings', 'Recommended minimum height: {recommendedMinHeight}px (minimum {minWidth} x {minHeight} px).', [
                'recommendedMinHeight' => static::LOGO_RECOMMENDED_MIN_HEIGHT,
                'minWidth' => static::LOGO_MIN_WIDTH,
                'minHeight' => static::LOGO_MIN_HEIGHT,
            ]),
            'icon' => Yii::t('AdminModule.settings', 'Recommended size: {recommendedWidth} × {recommendedHeight}px (minimum {minWidth} x {minHeight} px).', [
                'recommendedWidth' => static::ICON_RECOMMENDED_WIDTH,
                'recommendedHeight' => static::ICON_RECOMMENDED_HEIGHT,
                'minWidth' => static::ICON_MIN_WIDTH,
                'minHeight' => static::ICON_MIN_HEIGHT,
            ]),
            'loginBackgroundImage' => Yii::t('AdminModule.settings', 'Recommended size: {recommendedWidth} × {recommendedHeight}px (minimum {minWidth} x {minHeight} px).', [
                'recommendedWidth' => static::LOGIN_BG_RECOMMENDED_WIDTH,
                'recommendedHeight' => static::LOGIN_BG_RECOMMENDED_HEIGHT,
                'minWidth' => static::LOGIN_BG_MIN_WIDTH,
                'minHeight' => static::LOGIN_BG_MIN_HEIGHT,
            ]),
            'mailHeaderImage' => Yii::t('AdminModule.settings', 'Recommended size: {recommendedWidth} × {recommendedHeight}px (minimum {minWidth} x {minHeight} px).', [
                'recommendedWidth' => static::MAIL_HEADER_RECOMMENDED_WIDTH,
                'recommendedHeight' => static::MAIL_HEADER_RECOMMENDED_HEIGHT,
                'minWidth' => static::MAIL_HEADER_MIN_WIDTH,
                'minHeight' => static::MAIL_HEADER_MIN_HEIGHT,
            ]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $loaded = false;
        foreach (array_keys(static::IMAGES) as $attribute) {
            $files = UploadedFile::getInstancesByName($attribute);
            if ($files !== []) {
                $this->$attribute = $files[0];
                $loaded = true;
            }
        }

        return parent::load($data, $formName) || $loaded;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        foreach (static::IMAGES as $attribute => $image) {
            if ($this->$attribute instanceof UploadedFile) {
                Yii::$app->img->$image->setUploadedFile($this->$attribute);
            }
        }

        return true;
    }
}
