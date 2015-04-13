<?php

/**
 * Add-on singleton class HSync permits syncing of model objects to remote datastores
 * @package humhub.libs
 * @since   0.11.1
 * @author  Chris Tembreull <ctembreull@roosterpark.com>
 */

class HSync extends CComponent
{

  /**
   * @var HSync instance
   */
  static private $instance = null;

  /**
   * @var Recommendation Engine instance url
   */
  static private $api_url;

  static public function getInstance() {
      if (null === self::$instance) {
          self::$instance = new self;
      }
      return self::$instance;
  }

  private function __construct() {
    $config = Yii::app()->params['sync'];
    self::$api_url = $config['proto'].'://'.$config['host'].':'.$config['port'];
  }

  public function addUser($user) {
    $config = Yii::app()->params['sync'];
    $path   = self::$api_url.'/'.$config['paths']['user'];

    if (is_array($user)) {
      $user = CJSON::encode($user);
    }

    $response = Yii::app()->curl->postJSON($path, $user);

    return($response);
  }

  public function deleteUser($user) {
    $config = Yii::app()->params['sync'];
    $path   = self::$api_url.'/'.$config['paths']['user'].'/'

    if (is_array($user)) {
      $path = $path . $user['guid'];
    } else {
      $path = $path . $user;
    }

    $response = Yii::app()->curl->delete($path);

    return($response);
  }

}
?>
