<?php

namespace humhub\modules\user\authclient\interfaces;

/**
 * Auth clients that hold non-serializable state (e.g. open connections, closures)
 * implement this interface to clean up before being stored in the session.
 *
 * @since 1.19
 */
interface SerializableAuthClient
{
    public function beforeSerialize(): void;
}
