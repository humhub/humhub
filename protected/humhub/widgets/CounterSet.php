<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;

/**
 * Class CounterSet
 *
 * @since 1.3
 * @package humhub\widgets
 */
class CounterSet extends Widget
{
    /**
     * @var CounterSetItem[]
     */
    public $counters = [];


    /**
     * @var string the template to use
     */
    public $template = '@humhub/widgets/views/counterSetHeader';


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render($this->template, ['counters' => $this->counters]);
    }
}
