<?php

namespace humhub\components\assets;

use humhub\helpers\ArrayHelper;
use yii\web\AssetConverter;

class ViteAssetConverter extends AssetConverter
{
    public function init()
    {
        parent::init();

        $this->commands = ArrayHelper::merge(
            $this->commands, [
                'vue.js' => ['js', 'npm run build-vue entry {from} dist {to}'],
                'jsx' => ['js', 'npm run build-react entry {from} dist {to}'],
            ]
        );
    }

    public function convert($asset, $basePath)
    {
        $pos = strpos($asset, '.');
        if ($pos !== false) {
            $ext = substr($asset, $pos + 1);
            if (isset($this->commands[$ext])) {
                list($ext, $command) = $this->commands[$ext];
                $result = substr($asset, 0, $pos + 1) . $ext;
                if ($this->forceConvert || @filemtime("$basePath/$result") < @filemtime("$basePath/$asset")) {
                    $this->runCommand($command, $basePath, $asset, $result);
                }

                return $result;
            }
        }

        return $asset;
    }
}
