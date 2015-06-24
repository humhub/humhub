<?php
/**
 * This is a wrapper for making CodeIgniter's CI_Security component work with Yii
 */

define('BASEPATH', __DIR__);

/**
 * @param $level
 * @param $message
 */
function log_message($level, $message)
{
    Yii::log($message, $level);
}

//Need this for basic CI functions (like is_php() and remove_invisible_characters())
Yii::import('application.vendors.Codeigniter.Common', true);

Yii::import('application.vendors.Codeigniter.CI_Security');
class Security extends CI_Security
{
    public function __construct()
    {
        //We ignore entire CSRF part from constructor, so need to set charset only
        $this->charset = strtoupper('UTF-8');
    }

    public function csrf_verify()
    {
        throw new Exception('Not supported');
    }

    public function csrf_set_cookie()
    {
        throw new Exception('Not supported');
    }

    public function csrf_show_error()
    {
        throw new Exception('Not supported');
    }

    public function get_csrf_hash()
    {
        throw new Exception('Not supported');
    }

    public function get_csrf_token_name()
    {
        throw new Exception('Not supported');
    }
}