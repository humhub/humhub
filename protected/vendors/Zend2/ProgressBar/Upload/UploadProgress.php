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
 * Progress Bar Upload Handler for the UploadProgress extension
 */
class UploadProgress extends AbstractUploadHandler
{
    /**
     * @param  string $id
     * @return array|bool
     * @throws Exception\PhpEnvironmentException
     */
    protected function getUploadProgress($id)
    {
        if (!$this->isUploadProgressAvailable()) {
            throw new Exception\PhpEnvironmentException(
                'UploadProgress extension is not installed'
            );
        }

        $uploadInfo = uploadprogress_get_info($id);
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
        $status['total']   = $status['bytes_total'];
        $status['current'] = $status['bytes_uploaded'];
        $status['rate']    = $status['speed_average'];
        if ($status['total'] == $status['current']) {
            $status['done'] = true;
        }

        return $status;
    }

    /**
     * Checks for the UploadProgress extension
     *
     * @return bool
     */
    public function isUploadProgressAvailable()
    {
        return is_callable('uploadprogress_get_info');
    }
}
