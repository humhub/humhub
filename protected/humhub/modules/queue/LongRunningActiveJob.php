<?php

namespace humhub\modules\queue;

/**
 * The maximum time to run (Ttr) of jobs of this class is increased.
 * This is done via the event handler of the queue module.
 *
 * @see Module::$longRunningJobTtr
 * @since 1.15
 */
abstract class LongRunningActiveJob extends ActiveJob
{
}
