<?php

namespace humhub\modules\content;

/**
 * Content Module
 * 
 * @author Luke
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\content\controllers';

    /**
     * @since 1.1
     * @var boolean admin can see all content
     */
    public $adminCanViewAllContent = false;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer !== null) {
            return [
                new permissions\ManageContent(),
            ];
        }

        return [];
    }

}
