<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer;

use Yii;
use yii\helpers\Url;
use yii\base\Exception;

/**
 * InstallerModule provides an web installation interface for the applcation
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * @event on configuration steps init
     */
    const EVENT_INIT_CONFIG_STEPS = 'steps';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\installer\controllers';

    /**
     * Array of config steps
     *
     * @var array
     */
    public $configSteps = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            return;
        }

        $this->layout = '@humhub/modules/installer/views/layouts/main.php';
        $this->initConfigSteps();
        $this->sortConfigSteps();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        // Block installer, when it's marked as installed
        if (Yii::$app->params['installed']) {
            throw new \yii\web\HttpException(500, 'HumHub is already installed!');
        }

        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Checks if database connections works
     *
     * @return boolean state of database connection
     */
    public function checkDBConnection()
    {

        try {
            // call setActive with true to open connection.
            Yii::$app->db->open();
            // return the current connection state.
            return Yii::$app->db->getIsActive();
        } catch (Exception $e) {
            
        } catch (\yii\base\Exception $e) {
            
        } catch (\PDOException $e) {
            
        }
        return false;
    }

    /**
     * Checks if the application is already configured.
     */
    public function isConfigured()
    {
        if (Yii::$app->settings->get('secret') != "") {
            return true;
        }
        return false;
    }

    /**
     * Sets application in installed state (disables installer)
     */
    public function setInstalled()
    {
        $config = \humhub\libs\DynamicConfig::load();
        $config['params']['installed'] = true;
        \humhub\libs\DynamicConfig::save($config);
    }

    /**
     * Sets application database in installed state
     */
    public function setDatabaseInstalled()
    {
        $config = \humhub\libs\DynamicConfig::load();
        $config['params']['databaseInstalled'] = true;
        \humhub\libs\DynamicConfig::save($config);
    }

    protected function initConfigSteps()
    {

        /**
         * Step:  Basic Configuration
         */
        $this->configSteps['basic'] = [
            'sort' => 100,
            'url' => Url::to(['/installer/config/basic']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'basic');
            },
        ];


        /**
         * Step: Use Case
         */
        $this->configSteps['usecase'] = [
            'sort' => 150,
            'url' => Url::to(['/installer/config/use-case']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'use-case');
            },
        ];

        /**
         * Step: Security
         */
        $this->configSteps['security'] = [
            'sort' => 200,
            'url' => Url::to(['/installer/config/security']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'security');
            },
        ];

        /**
         * Step: Sample Data
         */
        $this->configSteps['modules'] = [
            'sort' => 300,
            'url' => Url::to(['/installer/config/modules']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'modules');
            },
        ];

        /**
         * Step:  Setup Admin User
         */
        $this->configSteps['admin'] = [
            'sort' => 400,
            'url' => Url::to(['/installer/config/admin']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'admin');
            },
        ];


        /**
         * Step: Sample Data
         */
        $this->configSteps['sample-data'] = [
            'sort' => 450,
            'url' => Url::to(['/installer/config/sample-data']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'sample-data');
            },
        ];

        /**
         * Step:  Setup Admin User
         */
        $this->configSteps['finish'] = [
            'sort' => 1000,
            'url' => Url::to(['/installer/config/finish']),
            'isCurrent' => function() {
                return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'finish');
            },
        ];

        $this->trigger(self::EVENT_INIT_CONFIG_STEPS);
    }

    /**
     * Get Next Step
     */
    public function getNextConfigStepUrl()
    {
        $foundCurrent = false;
        foreach ($this->configSteps as $step) {
            if ($foundCurrent) {
                return $step['url'];
            }

            if (call_user_func($step['isCurrent'])) {
                $foundCurrent = true;
            }
        }

        return $this->configSteps[0]['url'];
    }

    /**
     * Sorts all configSteps on sort attribute
     */
    protected function sortConfigSteps()
    {
        usort($this->configSteps, function($a, $b) {
            return ($a['sort'] > $b['sort']) ? 1 : -1;
        });
    }

}
