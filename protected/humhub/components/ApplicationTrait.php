<?php
/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\interfaces\MailerInterface;
use yii\helpers\Url;

trait ApplicationTrait
{
    /**
     * @var string|array the homepage url
     */
    protected $_homeUrl = null;

    /**
     * @var string Minimum PHP version that recommended to work without issues
     */
    public $minRecommendedPhpVersion;

    /**
     * @var string Minimum PHP version that may works but probably with small issues
     */
    public $minSupportedPhpVersion;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        // Remove obsolete config params:
        unset($config['components']['formatterApp']);

        parent::__construct($config);
    }

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl(): string
    {
        if ($this->_homeUrl === null) {
            return Url::to(['/dashboard/dashboard']);
        }

        if (is_array($this->_homeUrl)) {
            return Url::to($this->_homeUrl);
        }

        return $this->_homeUrl;
    }

    /**
     * @param string|array $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }
}
