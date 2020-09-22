<?php
namespace humhub\modules\ui\icon\components;

use humhub\modules\ui\icon\widgets\Icon;

/**
 * IconProviders are used to render an icon for a specific icon library.
 *
 * The [[render()]] function of a IconProvider should ideally support all features of the [[\humhub\modules\ui\icon\widgets\Icon]]
 * widget and furthermore map all icon names available in [[\humhub\modules\ui\icon\widgets\Icon::$names]].
 *
 * @since 1.4
 */
interface IconProvider
{
    /**
     * @return string unique factory id used in the `lib` option of icons
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
     *
     * > Note: If the provider does not support a specific icon name or feature it should return null.
     *
     * @param $icon string|Icon|[]|null either an icon name, icon instance or icon array definition
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
     * > Note: If the provider does not support this feature, it should return null.
     *
     * @param $listDefinition
     * @return $
     */
    public function renderList($listDefinition);

    /**
     * Return all supported icon names.
     * @return string[]
     */
    public function getNames();

}