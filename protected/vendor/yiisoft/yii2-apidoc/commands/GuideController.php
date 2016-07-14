<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\commands;

use yii\apidoc\components\BaseController;
use yii\apidoc\helpers\ApiMarkdown;
use yii\apidoc\models\Context;
use yii\apidoc\renderers\GuideRenderer;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use Yii;
use yii\helpers\Json;

/**
 * This command can render documentation stored as markdown files such as the yii guide
 * or your own applications documentation setup.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideController extends BaseController
{
    /**
     * @var string path or URL to the api docs to allow links to classes and properties/methods.
     */
    public $apiDocs;
    /**
     * @var string prefix to prepend to all output file names generated for the guide.
     */
    public $guidePrefix = 'guide-';


    /**
     * Renders API documentation files
     * @param array $sourceDirs
     * @param string $targetDir
     * @return integer
     */
    public function actionIndex(array $sourceDirs, $targetDir)
    {
        $renderer = $this->findRenderer($this->template);
        $targetDir = $this->normalizeTargetDir($targetDir);
        if ($targetDir === false || $renderer === false) {
            return 1;
        }

        if ($this->pageTitle !== null) {
            $renderer->pageTitle = $this->pageTitle;
        }
        if ($renderer->guideUrl === null) {
            $renderer->guideUrl = './';
        }
        $renderer->guidePrefix = $this->guidePrefix;

        // setup reference to apidoc
        if ($this->apiDocs !== null) {
            $path = $this->apiDocs;
            if ($renderer->apiUrl === null) {
                $renderer->apiUrl = $path;
            }
            // use relative paths relative to targetDir
            if (strncmp($path, '.', 1) === 0) {
                $renderer->apiContext = $this->loadContext("$targetDir/$path");
            } else {
                $renderer->apiContext = $this->loadContext($path);
            }
        } elseif (file_exists($targetDir . '/cache/apidoc.data')) {
            if ($renderer->apiUrl === null) {
                $renderer->apiUrl = './';
            }
            $renderer->apiContext = $this->loadContext($targetDir);
        } else {
            $renderer->apiContext = new Context();
        }
        $this->updateContext($renderer->apiContext);

        // read blocktypes translations
        ApiMarkdown::$blockTranslations = [];
        foreach($sourceDirs as $dir) {
            if (is_file("$dir/blocktypes.json")) {
                ApiMarkdown::$blockTranslations = Json::decode(file_get_contents("$dir/blocktypes.json"), true);
            }
        }

        // search for files to process
        if (($files = $this->searchFiles($sourceDirs)) === false) {
            return 1;
        }

        $renderer->controller = $this;
        $renderer->render($files, $targetDir);

        $this->stdout('Publishing images...');
        foreach ($sourceDirs as $source) {
            $imageDir = rtrim($source, '/\\') . '/images';
            if (file_exists($imageDir)) {
                FileHelper::copyDirectory($imageDir, $targetDir . '/images');
            }
        }
        $this->stdout('done.' . PHP_EOL, Console::FG_GREEN);
    }


    /**
     * @inheritdoc
     */
    protected function findFiles($path, $except = [])
    {
        $path = FileHelper::normalizePath($path);
        $options = [
            'only' => ['*.md'],
            'except' => $except,
        ];

        return FileHelper::findFiles($path, $options);
    }

    /**
     * @inheritdoc
     * @return GuideRenderer
     */
    protected function findRenderer($template)
    {
        // find renderer by class name
        if (class_exists($template)) {
            return new $template();
        }

        $rendererClass = 'yii\\apidoc\\templates\\' . $template . '\\GuideRenderer';
        if (!class_exists($rendererClass)) {
            $this->stderr('Renderer not found.' . PHP_EOL);

            return false;
        }

        return new $rendererClass();
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apiDocs', 'guidePrefix']);
    }
}
