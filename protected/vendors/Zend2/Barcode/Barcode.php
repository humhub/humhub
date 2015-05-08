<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Barcode;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Class for generate Barcode
 */
abstract class Barcode
{
    /**
     * Default barcode TTF font name
     *
     * It's used by standard barcode objects derived from
     * {@link Object\AbstractObject} class
     * if corresponding constructor option is not provided.
     *
     * @var string
     */
    protected static $staticFont = null;

    /**
     * The parser plugin manager
     *
     * @var ObjectPluginManager
     */
    protected static $objectPlugins;

    /**
     * The renderer plugin manager
     *
     * @var RendererPluginManager
     */
    protected static $rendererPlugins;

    /**
     * Get the parser plugin manager
     *
     * @return ObjectPluginManager
     */
    public static function getObjectPluginManager()
    {
        if (!static::$objectPlugins instanceof ObjectPluginManager) {
            static::$objectPlugins = new ObjectPluginManager();
        }

        return static::$objectPlugins;
    }

    /**
     * Get the renderer plugin manager
     *
     * @return RendererPluginManager
     */
    public static function getRendererPluginManager()
    {
        if (!static::$rendererPlugins instanceof RendererPluginManager) {
            static::$rendererPlugins = new RendererPluginManager();
        }

        return static::$rendererPlugins;
    }

    /**
     * Factory for Zend\Barcode classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'int25' corresponds to class Object\Int25.  This
     * is case-insensitive.
     *
     * First argument may alternatively be an object of type Traversable.
     * The barcode class base name is read from the 'barcode' property.
     * The barcode config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the barcode constructor.
     *
     * If the first argument is of type Traversable, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $barcode         String name of barcode class, or Traversable object.
     * @param  mixed $renderer        String name of renderer class
     * @param  mixed $barcodeConfig   OPTIONAL; an array or Traversable object with barcode parameters.
     * @param  mixed $rendererConfig  OPTIONAL; an array or Traversable object with renderer parameters.
     * @param  bool $automaticRenderError  OPTIONAL; set the automatic rendering of exception
     * @return Barcode
     * @throws Exception\ExceptionInterface
     */
    public static function factory($barcode,
                                   $renderer = 'image',
                                   $barcodeConfig = array(),
                                   $rendererConfig = array(),
                                   $automaticRenderError = true)
    {
        /*
         * Convert Traversable argument to plain string
         * barcode name and separate config object.
         */
        if ($barcode instanceof Traversable) {
            $barcode = ArrayUtils::iteratorToArray($barcode);
            if (isset($barcode['rendererParams'])) {
                $rendererConfig = $barcode['rendererParams'];
            }
            if (isset($barcode['renderer'])) {
                $renderer = (string) $barcode['renderer'];
            }
            if (isset($barcode['barcodeParams'])) {
                $barcodeConfig = $barcode['barcodeParams'];
            }
            if (isset($barcode['barcode'])) {
                $barcode = (string) $barcode['barcode'];
            } else {
                $barcode = null;
            }
        }

        try {
            $barcode  = static::makeBarcode($barcode, $barcodeConfig);
            $renderer = static::makeRenderer($renderer, $rendererConfig);
        } catch (Exception\ExceptionInterface $e) {
            if ($automaticRenderError && !($e instanceof Exception\RendererCreationException)) {
                $barcode  = static::makeBarcode('error', array('text' => $e->getMessage()));
                $renderer = static::makeRenderer($renderer, array());
            } else {
                throw $e;
            }
        }

        $renderer->setAutomaticRenderError($automaticRenderError);
        return $renderer->setBarcode($barcode);
    }

    /**
     * Barcode Constructor
     *
     * @param mixed $barcode        String name of barcode class, or Traversable object, or barcode object.
     * @param mixed $barcodeConfig  OPTIONAL; an array or Traversable object with barcode parameters.
     * @throws Exception\InvalidArgumentException
     * @return Object
     */
    public static function makeBarcode($barcode, $barcodeConfig = array())
    {
        if ($barcode instanceof Object\ObjectInterface) {
            return $barcode;
        }

        /*
         * Convert Traversable argument to plain string
         * barcode name and separate configuration.
         */
        if ($barcode instanceof Traversable) {
            $barcode = ArrayUtils::iteratorToArray($barcode);
            if (isset($barcode['barcodeParams']) && is_array($barcode['barcodeParams'])) {
                $barcodeConfig = $barcode['barcodeParams'];
            }
            if (isset($barcode['barcode'])) {
                $barcode = (string) $barcode['barcode'];
            } else {
                $barcode = null;
            }
        }
        if ($barcodeConfig instanceof Traversable) {
            $barcodeConfig = ArrayUtils::iteratorToArray($barcodeConfig);
        }

        /*
         * Verify that barcode parameters are in an array.
         */
        if (!is_array($barcodeConfig)) {
            throw new Exception\InvalidArgumentException(
                'Barcode parameters must be in an array or a Traversable object'
            );
        }

        /*
         * Verify that an barcode name has been specified.
         */
        if (!is_string($barcode) || empty($barcode)) {
            throw new Exception\InvalidArgumentException(
                'Barcode name must be specified in a string'
            );
        }

        return static::getObjectPluginManager()->get($barcode, $barcodeConfig);
    }

    /**
     * Renderer Constructor
     *
     * @param mixed $renderer           String name of renderer class, or Traversable object.
     * @param mixed $rendererConfig     OPTIONAL; an array or Traversable object with renderer parameters.
     * @throws Exception\RendererCreationException
     * @return Renderer\RendererInterface
     */
    public static function makeRenderer($renderer = 'image', $rendererConfig = array())
    {
        if ($renderer instanceof Renderer\RendererInterface) {
            return $renderer;
        }

        /*
         * Convert Traversable argument to plain string
         * barcode name and separate config object.
         */
        if ($renderer instanceof Traversable) {
            $renderer = ArrayUtils::iteratorToArray($renderer);
            if (isset($renderer['rendererParams'])) {
                $rendererConfig = $renderer['rendererParams'];
            }
            if (isset($renderer['renderer'])) {
                $renderer = (string) $renderer['renderer'];
            }
        }
        if ($rendererConfig instanceof Traversable) {
            $rendererConfig = ArrayUtils::iteratorToArray($rendererConfig);
        }

        /*
         * Verify that barcode parameters are in an array.
         */
        if (!is_array($rendererConfig)) {
            throw new Exception\RendererCreationException(
                'Barcode parameters must be in an array or a Traversable object'
            );
        }

        /*
         * Verify that an barcode name has been specified.
         */
        if (!is_string($renderer) || empty($renderer)) {
            throw new Exception\RendererCreationException(
                'Renderer name must be specified in a string'
            );
        }

        return static::getRendererPluginManager()->get($renderer, $rendererConfig);
    }

    /**
     * Proxy to renderer render() method
     *
     * @param string | Object\ObjectInterface | array | Traversable $barcode
     * @param string | Renderer\RendererInterface $renderer
     * @param array  | Traversable $barcodeConfig
     * @param array  | Traversable $rendererConfig
     */
    public static function render($barcode,
                                  $renderer,
                                  $barcodeConfig = array(),
                                  $rendererConfig = array())
    {
        static::factory($barcode, $renderer, $barcodeConfig, $rendererConfig)->render();
    }

    /**
     * Proxy to renderer draw() method
     *
     * @param string | Object\ObjectInterface | array | Traversable $barcode
     * @param string | Renderer\RendererInterface $renderer
     * @param array | Traversable $barcodeConfig
     * @param array | Traversable $rendererConfig
     * @return mixed
     */
    public static function draw($barcode,
                                $renderer,
                                $barcodeConfig = array(),
                                $rendererConfig = array())
    {
        return static::factory($barcode, $renderer, $barcodeConfig, $rendererConfig)->draw();
    }

    /**
     * Set the default font for new instances of barcode
     *
     * @param string $font
     * @return void
     */
    public static function setBarcodeFont($font)
    {
        static::$staticFont = $font;
    }

    /**
     * Get current default font
     *
     * @return string
     */
    public static function getBarcodeFont()
    {
        return static::$staticFont;
    }
}
