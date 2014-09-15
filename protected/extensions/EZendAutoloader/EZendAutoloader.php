<?php
/**
 * EZendAutoloader
 *
 * @author Alexander Makarov
 * @version 1.1
 *
 * See readme for instructions.
 */
class EZendAutoloader {
	/**
	 * @var array class prefixes
	 */
	static $prefixes = array(
		'Zend'
	);

	/**
	 * @var string path to where Zend classes root is located
	 */
    static $basePath = null;

    /**
     * Class autoload loader.
     *
     * @static
     * @param string $className
     * @return boolean
     */
    static function loadClass($className){
		foreach(self::$prefixes as $prefix){
			if(strpos($className, $prefix.'_')!==false){
				if(!self::$basePath) self::$basePath = Yii::getPathOfAlias("application.vendors").'/';
				include self::$basePath.str_replace('_','/',$className).'.php';
				return class_exists($className, false) || interface_exists($className, false);
			}
		}
		return false;
    }
}
