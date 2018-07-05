<?php
/**
 * Created by PhpStorm.
 * User: kingb
 * Date: 22.06.2018
 * Time: 05:28
 */

namespace humhub\modules\content\models;


use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * This interface can be implemented by Models which can be moved to a [[ContentContainerActiveRecord]]
 *
 * @since 1.3
 */
interface Movable
{
    /**
     * Defines if this instance is movable and either returns true or a string indicating why the instance can't be moved.
     *
     * If a [[ContentContainerActiveRecord]] is given this function may adds container specific checks as permission
     * or visibility checks.
     *
     * Thus, instances may be movable but only to certain containers.
     *
     * @param ContentContainerActiveRecord|null $container the target container
     * @return bool|string either true in case the instance can be moved, otherwise a string indicating why the instance
     * can't be moved
     *
     */
    public function canMove(ContentContainerActiveRecord $container = null);

    /**
     * Implements the actual logic for moving the instance to the given [[ContentContainerActiveRecord]].
     *
     * If supported by the implementation a null [[ContentContainerActiveRecord]] can be set to detach the
     * instance from the current [[ContentContainerActiveRecord]].
     *
     * In case a null [[ContentContainerActiveRecord]] is not supported by the implementation but given, this function should
     * return false.
     *
     * By default this function should make use of the [[canMove()]] validation before executing the actual move logic
     * unless the `$force` parameter is set to true.
     *
     * This function may call [[afterMove()]] once the move has been performed successfully.
     *
     * @param ContentContainerActiveRecord|null $container
     * @param bool $force
     * @return bool
     */
    public function move(ContentContainerActiveRecord $container = null, $force = false);

    /**
     * This function is called once the actual move logic has been performed.
     */
    public function afterMove(ContentContainerActiveRecord $container = null);

}
