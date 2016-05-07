<?php

namespace humhub\modules\space\models;


use humhub\modules\space\widgets\Menu;

/**
 * Helper class for retrieving all stand alone module pages of the current space
 */
class SpacePages
{
    /**
     * Searches for urls of modules which are activated for the current space
     * and offer an own site over the space menu.
     * The urls are associated with a module label.
     * 
     * Returns an array of urls with associated module labes for modules 
     * @param type $space
     */
    public static function getAvailablePages() 
    {
        //Initialize the space Menu to check which active modules have an own page
        $moduleItems = (new Menu())->getItems('modules');
        $result = [];
        foreach($moduleItems as $moduleItem) {
            $result[$moduleItem['url']] = $moduleItem['label'];
        }
        return $result;
    }
}
