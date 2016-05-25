<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace humhub\generators\module;

use yii\gii\CodeFile;
use yii\helpers\Html;
use Yii;
use yii\helpers\StringHelper;

/**
 * This generator will generate the skeleton code needed by a module.
 *
 * @property string $controllerNamespace The controller namespace of the module. This property is read-only.
 * @property boolean $modulePath The directory that contains the module class. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    public $moduleClass = "app\\modules\\example\\Module";
    public $moduleID = "example";


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'HumHub Module Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator helps you to generate the skeleton code needed by a HumHub module.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleID', 'moduleClass'], 'filter', 'filter' => 'trim'],
            [['moduleID', 'moduleClass'], 'required'],
            [['moduleID'], 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
            [['moduleClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['moduleClass'], 'validateModuleClass'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'moduleID' => 'Module ID',
            'moduleClass' => 'Module Class',
        ];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'moduleID' => 'This refers to the ID of the module, e.g., <code>admin</code>.',
            'moduleClass' => 'This is the fully qualified class name of the module, e.g., <code>app\modules\admin\Module</code>.',
        ];
    }

    /**
     * @inheritdoc
     */
    public function successMessage()
    {
        $output = "<p>The module has been generated successfully.</p>";
        $output .= "<p>To access the module, you must enable it via the <a href=\"".Yii::$app->getUrlManager()->createUrl('/admin/module')."\">module admin panel</a></p>";

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return [
            'config.php',
            'module.json.php',
            'Module.php',
            'Events.php',
            'controllers/AdminController.php',
            'controllers/DefaultController.php',
            'views/default/index.php',
            'views/admin/index.php',
        ];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $modulePath = $this->getModulePath();

        $files[] = new CodeFile(
            $modulePath . '/config.php',
            $this->render("config.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/module.json',
            $this->render("module.json.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/' . StringHelper::basename($this->moduleClass) . '.php',
            $this->render("Module.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/Events.php',
            $this->render("Events.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/controllers/AdminController.php',
            $this->render("controllers/AdminController.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/controllers/DefaultController.php',
            $this->render("controllers/DefaultController.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/views/admin/index.php',
            $this->render("views/admin/index.php")
        );

        $files[] = new CodeFile(
            $modulePath . '/views/default/index.php',
            $this->render("views/default/index.php")
        );

        return $files;
    }

    /**
     * Validates [[moduleClass]] to make sure it is a fully qualified class name.
     */
    public function validateModuleClass()
    {
        if (strpos($this->moduleClass, '\\') === false || Yii::getAlias('@' . str_replace('\\', '/', $this->moduleClass), false) === false) {
            $this->addError('moduleClass', 'Module class must be properly namespaced.');
        }
        if (empty($this->moduleClass) || substr_compare($this->moduleClass, '\\', -1, 1) === 0) {
            $this->addError('moduleClass', 'Module class name must not be empty. Please enter a fully qualified class name. e.g. "app\\modules\\admin\\Module".');
        }
    }

    /**
     * @return boolean the directory that contains the module class
     */
    public function getModulePath()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\'))));
    }

    /**
     * @return string the controller namespace of the module.
     */
    public function getControllerNamespace()
    {
        return substr($this->moduleClass, 0, strrpos($this->moduleClass, '\\')) . '\controllers';
    }

    /**
     * @return string of the Module class name
     */
    public function getModuleClassName()
    {
        $className = $this->moduleClass;
        $pos = strrpos($className, '\\');
        $ns = ltrim(substr($className, 0, $pos), '\\');
        return substr($className, $pos + 1);
    }
}
