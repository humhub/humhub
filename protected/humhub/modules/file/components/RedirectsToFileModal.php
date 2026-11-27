<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\models\File;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Response;

/**
 * For controllers whose action only exists as a modal of a file
 *
 * @since 1.20
 */
trait RedirectsToFileModal
{
    /**
     * A modal is no page, so on a direct request (e.g. a copied link) it is opened on top of the
     * page of the underlying content instead
     *
     * @param File $file
     * @param array $modalRoute route of the modal, e.g. `['/file/view', 'guid' => $file->guid]`
     * @return Response
     */
    protected function redirectToFileModal(File $file, array $modalRoute): Response
    {
        $url = Url::home();
        $record = $file->getPolymorphicRelation();
        if ($record instanceof ContentActiveRecord && $record->content->canView()) {
            $url = $record->content->getUrl();
        }

        $modalUrl = Json::htmlEncode(Url::to($modalRoute));
        Yii::$app->session->setFlash(
            'executeJavascript',
            'var openFileModal = function () { humhub.require("ui.modal").global.load(' . $modalUrl . '); };'
            . 'humhub.initialized ? openFileModal() : $(document).one("humhub:ready", openFileModal);',
        );

        return $this->redirect($url);
    }
}
