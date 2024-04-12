<?php

namespace tests\codeception\_support;

use humhub\libs\UUID;
use humhub\modules\content\models\Content;
use yii\test\ActiveFixture;

/**
 * Base fixture for ActiveFixtures that represents a `ContentActiveRecord`.
 *
 * Automatically an corresponding record in the `content` table is created for fixture rows.
 * The fixture data can also contain an additional column named `content` with individual content attributes.
 * e.g.
 *
 *```
 * return [
 *     ['id' => '1', 'message' => 'Post 1', 'content' => ['contentcontainer_id' => 1]],
 *     ['id' => '2', 'message' => 'Post 2', 'content' => ['contentcontainer_id' => 2]]
 * ];
 * ```
 *
 * @since 1.12.1
 */
abstract class ContentActiveFixture extends ActiveFixture
{
    /**
     * @inheritDoc
     */
    public function load()
    {
        $this->data = [];
        $table = $this->getTableSchema();
        foreach ($this->getData() as $alias => $row) {
            $contentRow = (isset($row['content']) && is_array($row['content'])) ? $row['content'] : [];
            unset($row['content']);

            $primaryKeys = $this->db->schema->insert($table->fullName, $row);
            $this->data[$alias] = array_merge($row, $primaryKeys);

            // Add Content
            $contentRowDefaults = [
                'guid' => UUID::v4(),
                'object_model' => $this->modelClass,
                'object_id' => $primaryKeys['id'],
                'visibility' => Content::VISIBILITY_PRIVATE,
                'created_by' => 1,
                'updated_by' => 1,
                'contentcontainer_id' => 1,
                'stream_channel' => Content::STREAM_CHANNEL_DEFAULT,
            ];
            $this->db->schema->insert('content', array_merge($contentRowDefaults, $contentRow));
        }
    }

    /**
     * @inheritDoc
     */
    public function unload()
    {
        $this->db->createCommand()->delete('content', ['object_model' => $this->modelClass])
            ->execute();

        parent::unload();
    }
}
