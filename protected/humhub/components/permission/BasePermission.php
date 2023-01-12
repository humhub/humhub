<?php

namespace humhub\components\permission;

use yii\base\BaseObject;
use yii\base\Event;

class BasePermission extends BaseObject
{
    /**
     * @event Event an event that is triggered when the permission is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';

    /**
     * @var string id of the permission (default is classname)
     */
    protected $id;

    /**
     * @var string title of the permission
     */
    protected $title = '';

    /**
     * @var string description of the permission
     */
    protected $description = '';

    /**
     * @var string module id which belongs to the permission
     */
    protected $moduleId = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Event::trigger($this, self::EVENT_INIT);
    }


    /**
     * Returns the ID
     *
     * @return string the id of the permission
     */
    public function getId()
    {
        if ($this->id != '') {
            return $this->id;
        }

        return $this->className();
    }

    /**
     * Returns the title
     *
     * @return string the title of the permission
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the description
     *
     * @return string the description of the permission
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the module id
     *
     * @return string the moduleid of the permission
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }


    /**
     * Checks the given id belongs to this permission
     *
     * @return boolean
     */
    public function hasId($id)
    {
        return ($this->getId() == $id);
    }
}
