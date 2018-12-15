<?php
namespace humhub\modules\ui\icon;


use humhub\modules\ui\icon\widgets\Icon;

/**
 * Interface IconFactory
 * @package humhub\modules\ui\icon
 *
 * @since 1.4
 */
interface IconFactory
{
    /**
     * @return string unique factory id
     */
    public function getId();

    /**
     * Use as follows:
     *
     * ```php
     * IconFactory::render('task');
     *
     * IconFactory::render('task', ['fixedWith' => true]);
     *
     * IconFactory::render(['name' => 'task', 'fixedWith' => true]);
     *
     * IconFactory::render(new Icon(['name' => 'task', 'fixedWith' => true]));
     * ```
     * @param $icon string|Icon|[] either an icon name, icon instance or icon array definition
     * @return mixed
     */
    public function render($icon, $options = []);

    /**
     * Renders a icon list:
     *
     * <?= IconFactory::renderList([
     *     ['task' => 'My List Item 1', 'options' => []],
     *     ['tachometer' => 'My List Item 2', 'options' => []]
     * ]); ?>
     *
     * @param $listDefinition
     * @return $
     */
    public function renderList($listDefinition);

}