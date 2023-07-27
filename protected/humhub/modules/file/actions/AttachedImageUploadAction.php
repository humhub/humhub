<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2016-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use humhub\exceptions\InvalidConfigTypeException;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\file\models\forms\AttachedImageUpload;
use humhub\modules\file\models\forms\FileUploadInterface;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * UploadAction provides an Ajax/JSON way to upload new files
 *
 * @since  1.15
 *
 * @property-read AttachedImage $image
 */
class AttachedImageUploadAction extends UploadAction
{
    //  public properties
    /**
     * @var string|AttachedImageUpload
     */
    public string $fileClass = AttachedImageUpload::class;

    /**
     * @throws InvalidConfigTypeException
     */
    public function init()
    {
        parent::init();

        if (!$this->record instanceof AttachedImageOwnerInterface && !$this->isControllerInConfigDetection()) {
            throw new InvalidConfigTypeException(
                __METHOD__,
                'record',
                AttachedImageOwnerInterface::class,
                $this->record
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function afterFileUpload(FileUploadInterface $file): ?array
    {
        try {
            $result = (array)Yii::$app->request->get('Result', []);
//            $unset  = new UnsetArrayValue();

            return ArrayHelper::merge(
                $result,
                parent::afterFileUpload($file),
//                    'url'          => $file->getUrl(),
//                    'relUrl'       => $unset,
//                    'openLink'     => $unset,
//                    'thumbnailUrl' => $unset,
            );
        } catch (Throwable $e) {
            return [
                'name'   => $file->file_name,
                'error'  => true,
                'errors' => [$e->getMessage()],
            ];
        }
    }
}
