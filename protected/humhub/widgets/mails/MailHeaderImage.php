<?php

namespace humhub\widgets\mails;

use Yii;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * @since 1.18
 */
class MailHeaderImage extends Widget
{
    public const MAX_WIDTH = 600; // For image resizing after upload
    public const MAX_HEIGHT = 300; // For image resizing after upload
    public const LOGO_MAX_HEIGHT = 60; // For image resizing after upload

    public int $verticalMargin = 10; // In pixels
    public ?string $backgroundColor = null;

    public function run()
    {
        $hasMailHeaderImage = Yii::$app->img->mailHeader->exists();
        $hasLogoImage = Yii::$app->img->logo->exists();
        $showNameInsteadOfLogo = (bool)Yii::$app->settings->get('showNameInsteadOfLogo');

        // Get relative image URL
        $imgUrl = null;
        if ($hasMailHeaderImage) {
            $imgUrl = Yii::$app->img->logo->getUrl(
                ['maxHeight' => MailHeaderImage::MAX_HEIGHT, 'maxWidth' => MailHeaderImage::MAX_WIDTH],
            );
        } elseif ($hasLogoImage && !$showNameInsteadOfLogo) {
            $imgUrl = Yii::$app->img->logo->getUrl([
                'maxWidth' => static::MAX_WIDTH,
                'maxHeight' => static::LOGO_MAX_HEIGHT,
            ]);
        }

        // Change it to absolute URL
        $imgUrl = $imgUrl ? Url::to($imgUrl, true) : null;

        return $this->render('mailHeaderImage', [
            'imgUrl' => $imgUrl,
            'appName' => Yii::$app->name,
            'verticalMargin' => $this->verticalMargin,
            'backgroundColor' => $this->backgroundColor,
        ]);
    }
}
