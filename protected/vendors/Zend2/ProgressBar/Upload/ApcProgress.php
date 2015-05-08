<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ProgressBar\Upload;

use Traversable;
use Zend\ProgressBar\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Progress Bar Upload Handler for the APC extension
 */
class ApcProgress extends AbstractUploadHandler
{
    /**
     * @param  string $id
     * @return array|bool
     * @throws Exception\PhpEnvironmentException
     */
    protected function getUploadProgress($id)
    {
        if (!$this->isApcAvailable()) {
            throw new Exception\PhpEnvironmentException('APC extension is not installed');
        }

        $uploadInfo = apc_fetch(ini_get('apc.rfc1867_prefix') . $id);
        if (!is_array($uploadInfo)) {
            return false;
        }

        $status  = array(
            'total'    => 0,
            'current'  => 0,
            'rate'     => 0,
            'message'  => '',
            'done'     => false
        );
        $status = $uploadInfo + $status;
        if (!empty($status['cancel_upload'])) {
            $status['done'] = true;
            $status['message'] = 'The upload has been canceled';
        }

        return $status;
    }

    /**
     * Checks for the APC extension
     *
     * @return bool
     */
    public function isApcAvailable()
    {
        return (bool) ini_get('apc.enabled')
            && (bool) ini_get('apc.rfc1867')
            && is_callable('apc_fetch');
    }
}
