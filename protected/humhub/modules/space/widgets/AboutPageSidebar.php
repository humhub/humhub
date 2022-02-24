<?php

namespace humhub\modules\space\widgets;

class AboutPageSidebar extends Sidebar
{
    /**
     * @var \humhub\modules\space\models\Space the space this sidebar is in
     */
    public $space;

    public function init()
    {
        parent::init();
        $this->widgets = [];

        if ($this->space->isMember())
            $this->widgets[] = [MyMembership::class, ['space' => $this->space], ['sortOrder' => 10]];

        $this->widgets[] = [Members::class, ['space' => $this->space, 'orderByNewest' => true], ['sortOrder' => 20]];
        $this->widgets[] = [SpaceFollowers::class, ['space' => $this->space], ['sortOrder' => 25]];
        $this->widgets[] = [SpaceTags::class, ['space' => $this->space], ['sortOrder' => 30]];
    }
}
