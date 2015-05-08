<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode\Renderer;

use Zend\Barcode\Object\ObjectInterface;

/**
 * Class for rendering the barcode
 */
interface RendererInterface
{
    /**
     * Constructor
     * @param array|\Traversable $options
     */
    public function __construct($options = null);

    /**
     * Set renderer state from options array
     * @param  array $options
     * @return RendererInterface
     */
    public function setOptions($options);

    /**
     * Set renderer namespace for autoloading
     *
     * @param string $namespace
     * @return RendererInterface
     */
    public function setRendererNamespace($namespace);

    /**
     * Retrieve renderer namespace
     *
     * @return string
     */
    public function getRendererNamespace();

    /**
     * Retrieve renderer type
     * @return string
     */
    public function getType();

    /**
     * Manually adjust top position
     * @param int $value
     * @return RendererInterface
     */
    public function setTopOffset($value);

    /**
     * Retrieve vertical adjustment
     * @return int
     */
    public function getTopOffset();

    /**
     * Manually adjust left position
     * @param int $value
     * @return RendererInterface
     */
    public function setLeftOffset($value);

    /**
     * Retrieve vertical adjustment
     * @return int
     */
    public function getLeftOffset();

    /**
     * Activate/Deactivate the automatic rendering of exception
     * @param  bool $value
     */
    public function setAutomaticRenderError($value);

    /**
     * Horizontal position of the barcode in the rendering resource
     * @param string $value
     * @return RendererInterface
     */
    public function setHorizontalPosition($value);

    /**
     * Horizontal position of the barcode in the rendering resource
     * @return string
     */
    public function getHorizontalPosition();

    /**
     * Vertical position of the barcode in the rendering resource
     * @param string $value
     * @return RendererInterface
     */
    public function setVerticalPosition($value);

    /**
     * Vertical position of the barcode in the rendering resource
     * @return string
     */
    public function getVerticalPosition();

    /**
     * Set the size of a module
     * @param float $value
     * @return RendererInterface
     */
    public function setModuleSize($value);

    /**
     * Set the size of a module
     * @return float
     */
    public function getModuleSize();

    /**
     * Retrieve the automatic rendering of exception
     * @return bool
     */
    public function getAutomaticRenderError();

    /**
     * Set the barcode object
     * @param  ObjectInterface $barcode
     * @return RendererInterface
     */
    public function setBarcode(ObjectInterface $barcode);

    /**
     * Retrieve the barcode object
     * @return ObjectInterface
     */
    public function getBarcode();

    /**
     * Checking of parameters after all settings
     * @return bool
     */
    public function checkParams();

    /**
     * Draw the barcode in the rendering resource
     * @return mixed
     */
    public function draw();

    /**
     * Render the resource by sending headers and drawed resource
     * @return mixed
     */
    public function render();
}
