<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\services;

use humhub\modules\admin\libs\HumHubAPI;
use Yii;

class MarketplaceService
{
    public static function addLicenceKey(?string $licenceKey): array
    {
        $result = [
            'licenceKey' => $licenceKey,
            'hasError' => false,
            'message' => ''
        ];

        if (empty($licenceKey)) {
            return $result;
        }

        $response = HumHubAPI::request('v1/modules/registerPaid', ['licenceKey' => $licenceKey]);

        if (!isset($response['status'])) {
            $result['hasError'] = true;
            $result['message'] = Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!');
            return $result;
        }

        if ($response['status'] !== 'ok' && $response['status'] !== 'created') {
            $result['hasError'] = true;
            $result['message'] = Yii::t('MarketplaceModule.base', 'Invalid module licence key!');
            return $result;
        }

        $result['licenceKey'] = '';
        $result['message'] = Yii::t('MarketplaceModule.base', 'Module licence added!');
        return $result;
    }
}
