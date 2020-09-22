<?php


namespace humhub\libs;

/**
 * Class RestrictedCallException
 * @package humhub\libs
 * @since 1.3.13
 */
class RestrictedCallException extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Restricted Call';
    }
}