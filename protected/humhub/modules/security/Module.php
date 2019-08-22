<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\security;

use humhub\components\Module as BaseModule;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Security base module
 *
 * @author buddh4
 * @since 1.4
 */
class Module extends BaseModule
{
    public $configPath = '@config';
    public $defaultConfigFile =  'security.default.json';
    public $customConfigFile = 'security.json';

    /**
     * @return bool|string
     * @throws InvalidConfigException
     */
    public function getConfigFilePath()
    {
        $directory = $this->configPath;
        $customFilePath = Yii::getAlias($directory.'/'.$this->customConfigFile);

        if(file_exists($customFilePath)) {
            return $customFilePath;
        }

        $defaultConfigFile = Yii::getAlias($directory.'/'.$this->defaultConfigFile);

        if(file_exists($defaultConfigFile)) {
            return $defaultConfigFile;
        }

        throw new InvalidConfigException(Yii::t('SecurityModule.error', 'Invalid security file path defined {path}!', ['path' => $this->configPath]));
    }
}
