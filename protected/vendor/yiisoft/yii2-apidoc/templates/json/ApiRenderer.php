<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\json;

use yii\apidoc\models\Context;
use yii\apidoc\renderers\ApiRenderer as BaseApiRenderer;
use yii\base\ViewContextInterface;
use Yii;

/**
 * The class for outputting documentation data structures as a JSON text.
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @since 2.0.5
 */
class ApiRenderer extends BaseApiRenderer implements ViewContextInterface
{
    /**
     * Writes a given [[Context]] as JSON text to file 'types.json'.
     *
     * @param Context $context the api documentation context to render.
     * @param $targetDir
     */
    public function render($context, $targetDir)
    {
        $types = array_merge($context->classes, $context->interfaces, $context->traits);
        file_put_contents($targetDir . '/types.json', json_encode($types, JSON_PRETTY_PRINT));
    }

    /**
     * @inheritdoc
     */
    public function generateApiUrl($typeName)
    {
    }

    /**
     * @inheritdoc
     */
    protected function generateFileName($typeName)
    {
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
    }

    /**
     * @inheritdoc
     */
    protected function generateLink($text, $href, $options = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function getSourceUrl($type, $line = null)
    {
    }
}
