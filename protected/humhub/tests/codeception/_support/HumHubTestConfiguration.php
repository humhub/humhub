<?php

namespace tests\codeception\_support;

use Yii;

/**
 * Utility class for merging test configuration.
 */
class HumHubTestConfiguration
{
   /**
    * This function is used for retrieving the humhub configuration for
    * a given test suite by merging default configuration with the test configuration and
    * environment configuration of the user.
    * 
    * @param type $suite
    * @return type
    */
    public static function getSuiteConfig($suite)
    {
        $config = self::initConfig($suite);
        return self::mergeWithEnvironmentConfig($config, $suite);
    }
    
    /**
     * Initializes the configuration for the given suite by merging
     * 
     *  @humhubTests/codeception/config/<suite>.php -> Default config for this suite
     *  @tests/config/common.php -> Common config of the current test module
     *  @test/config/<suite>.php -> Suite config of the current test module
     * 
     * @param type $suite the given suite e.g acceptance/functional/unit
     * @return type merged config
     */
    private static function initConfig($suite)
    {
        return \yii\helpers\ArrayHelper::merge(
            // Default Test Config
            require(Yii::getAlias('@humhubTests/codeception/config/'.$suite.'.php')),
            // User Overwrite Common Config
            require(Yii::getAlias('@tests/config/common.php')),
            // User Overwrite Suite Config
            require(Yii::getAlias('@tests/config/'.$suite.'.php'))
        );
    }
    
    /**
     * Merges environmental configuration if existing.
     * By running "codecept run functional --env myEnvironment" you can choose the execution environment
     * and overwrite the default configuration in your @tests/config/env/myEnvironment directory.
     * 
     * @param type $result
     * @param type $cfg
     * @param type $suite
     * @return type
     */
    private static function mergeWithEnvironmentConfig($result, $suite)
    {
        $cfg = \Codeception\Configuration::config();
        
        // If a environment was set we use the first environment as execution environment and try including a environment specific cfg
        if (isset($cfg['environment'])) {
            
            $env = $cfg['environment'][0][0];
            $envCfgCommonFile = Yii::getAlias('@env/'. $env .'/common.php');
            $envCfgFile = Yii::getAlias('@env/'. $env .'/'.$suite. '.php');
            
            //Merge with common environment config
            if (file_exists($envCfgCommonFile)) {
                $result = \yii\helpers\ArrayHelper::merge(
                    $result,
                    // Environment common config
                    require($envCfgCommonFile)
                );
            }
            
            //Merge with suite envornment config
            if (file_exists($envCfgFile)) {
                $result = \yii\helpers\ArrayHelper::merge(
                    $result,
                    // Environment config
                    require($envCfgFile)
                );
            }
        }
        
        return $result;
    }
}
