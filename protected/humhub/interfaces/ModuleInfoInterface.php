<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

/**
 * @since 1.16
 */
interface ModuleInfoInterface
{
    /**
     * Returns the ID that uniquely identifies this module among other modules which have the same [[module|parent]].
     *
     * @return string ID (Return type SHOULD be enforced)
     */
    public function getId();

    /**
     * Returns the module's name provided by module.json file
     *
     * @return string Name (Return type SHOULD be enforced)
     */
    public function getName();

    /**
     * Returns the module's description provided by module.json file
     *
     * @return string Description (Return type SHOULD be enforced)
     */
    public function getDescription();

    /**
     * Returns the module's version number provided by module.json file
     *
     * @return string Version Number (Return type SHOULD be enforced)
     */
    public function getVersion();

    /**
     * Returns the module's keywords provided by module.json file
     *
     * @return array List of keywords
     */
    public function getKeywords(): array;
}
