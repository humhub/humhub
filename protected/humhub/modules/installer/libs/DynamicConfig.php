<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer\libs;

use Yii;
use yii\base\InvalidConfigException;

final class DynamicConfig
{
    private string $fileName;

    public array $content = [];

    public function __construct($fileName = null)
    {
        if ($fileName === null) {
            $this->fileName = Yii::getAlias(Yii::$app->params['dynamicConfigFile']);
        } else {
            $this->fileName = Yii::getAlias($fileName);
        }

        if (file_exists($this->fileName)) {
            $this->load();
        }
    }

    private function load()
    {
        // Load config file with 'file_get_contents' and 'eval'
        // because 'require' don't reload the file when it's changed on runtime
        $configContent = str_replace(
            ['<' . '?php', '<' . '?', '?' . '>'],
            '',
            file_get_contents($this->fileName),
        );

        $this->config = eval($configContent);

        if (!is_array($this->config)) {
            $this->config = [];
        }
    }

    public function autoSetDatabase()
    {
        $this->config['components']['db'] = [];
    }

    public function save()
    {
        if (is_writable($this->fileName)) {
            throw new InvalidConfigException('File is not writable: ' . $this->fileName);
        }

        $content = '<' . '?php return ';
        $content .= var_export($this->content, true);
        $content .= ';';

        try {
            file_put_contents($this->fileName, $content);
        } catch (\Exception $ex) {
            throw new InvalidConfigException(
                Yii::t('InstallerModule.base', 'Make sure that the following file is writable: ' . $this->fileName),
            );
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->fileName);
        }

        if (function_exists('apc_compile_file')) {
            apc_compile_file($this->fileName);
        }
    }

}
