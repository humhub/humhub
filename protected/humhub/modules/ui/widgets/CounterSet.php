<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\widgets;


/**
 * Class CounterSet
 *
 * @since 1.3
 * @package humhub\modules\ui\widgets
 */
class CounterSet extends \humhub\components\Widget
{
    /**
     * @var CounterSetItem[]
     */
    public $counters = [];


    /**
     * @var string the template to use
     */
    public $template = '@ui/widgets/views/counterSetHeader';


    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render($this->template, ['counters' => $this->counters]);
    }
}
