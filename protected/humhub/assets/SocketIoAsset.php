<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\CoreAssetBundle;

/**
 * Socket.IO client files
 *
 * @since 1.3
 * @author luke
 */
class SocketIoAsset extends CoreAssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/socket.io-client';

    /**
     * @inheritdoc
     */
    public $js = ['dist/socket.io.slim.js'];

}
