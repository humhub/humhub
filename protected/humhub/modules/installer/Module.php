<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\installer;

use Yii;
use yii\helpers\Url;
use yii\base\Exception;

/**
 * InstallerModule provides an web installation interface for the applcation
 *
 * @package humhub.modules_core.installer
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    const EVENT_INIT_CONFIG_STEPS = 'stpes';

    public $controllerNamespace = 'humhub\modules\installer\controllers';

    /**
     * Array of config steps
     * 
     * @var array 
     */
    public $configSteps = [];

    public function init()
    {
        parent::init();
        $this->layout = '@humhub/modules/installer/views/layouts/main.php';
        $this->initConfigSteps();
        $this->sortConfigSteps();
    }

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
        if (\humhub\models\Setting::Get('secret') != "") {
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

    protected function initConfigSteps()
    {

        /**
         * Step 1:  Basic Configuration
         */
        $this->configSteps['basic'] = [
            'sort' => 100,
            'url' => Url::to(['/installer/config/basic']),
            'isCurrent' => function() {
        return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'basic');
    },
        ];

        /**
         * Step 2:  Setup Admin User
         */
        $this->configSteps['admin'] = [
            'sort' => 200,
            'url' => Url::to(['/installer/config/admin']),
            'isCurrent' => function() {
        return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'admin');
    },
        ];

        /**
         * Step 2:  Setup Admin User
         */
        $this->configSteps['finished'] = [
            'sort' => 500,
            'url' => Url::to(['/installer/config/finished']),
            'isCurrent' => function() {
        return (Yii::$app->controller->id == 'config' && Yii::$app->controller->action->id == 'finished');
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

    protected function sortConfigSteps()
    {
        usort($this->configSteps, function($a, $b) {
            return ($a['sort'] > $b['sort']) ? 1 : -1;
        });
    }

}
