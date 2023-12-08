<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace tests\codeception\_support;

use yii\log\Target;

class ArrayTarget extends Target
{
    protected array $messageStore = [];

    /**
     * @inheritDoc
     */
    public function export()
    {
        $this->messageStore = array_merge($this->messageStore, $this->messages);
        $this->messages = [];
    }

    public function flush()
    {
        $this->messageStore = [];
    }


    /**
     * @param int|string[]|null $levels
     * @param array|null $categories
     * @param array|null $except
     *
     * @return array
     */
    public function &getMessages($levels = 0, ?array $categories = null, ?array $except = null): array
    {
        $messages = static::filterMessages($this->messageStore, $levels ?? 0, $categories ?? [], $except ?? []);

        return $messages;
    }
}
