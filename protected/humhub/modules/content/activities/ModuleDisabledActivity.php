<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\activities;

use humhub\modules\activity\components\BaseActivity;
use yii\base\InvalidConfigException;

/**
 * @since 1.3
 */
class ModuleDisabledActivity extends BaseActivity
{
    /**
     * @inheritdoc
     */
    public $moduleId = 'content';

    /**
     * @inheritdoc
     */
    public $viewName = 'moduleDisabled';

    public $enabledModuleName;

    /**
     * @inheritdoc
     */
    public function getViewParams($params = [])
    {
        $params['moduleName'] = $this->enabledModuleName === null ? '' : $this->enabledModuleName;

        return parent::getViewParams($params);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        //TODO Need saved activity getViewParams in database ...
//        if ($this->enabledModuleName === null) {
//            throw new InvalidConfigException('Missing the param enabledModuleName!');
//        }

        parent::init();
    }
}
