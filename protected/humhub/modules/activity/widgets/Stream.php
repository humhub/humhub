<?php

namespace humhub\modules\activity\widgets;

/**
 * Class Stream was deprecated in v1.3 this class only holds compatibility logic for old themes
 *
 * @deprecated 1.3 use ActivityStreamViewer instead
 */
class Stream extends ActivityStreamViewer
{
    public function init()
    {
        if ($this->streamAction == '/dashboard/dashboard/stream') {
            $this->streamAction = '/dashboard/dashboard/activity-stream';
        } elseif ($this->streamAction === '/space/space/stream') {
            $this->streamAction = '/activity/stream/stream';
        }

        parent::init();
    }

}
