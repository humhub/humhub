# Search

HumHub indexes content via the `content` module's search subsystem. A `ContentActiveRecord` becomes searchable simply by overriding `getSearchAttributes()` — the rest is handled by the indexer.

## Making content searchable

Return an associative array of fields you want indexed and surfaced as snippets. Keys are arbitrary names; values are the text that should be matched.

```php
use humhub\modules\content\components\ContentActiveRecord;

class Post extends ContentActiveRecord
{
    // Required: how this content appears in the search result UI.
    public $wallEntryClass = \humhub\modules\post\widgets\WallEntry::class;

    public function getSearchAttributes(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
```

`$wallEntryClass` points to the wall-entry widget the search result list will use to render hits, so it must be set even if you don't otherwise display the content in a stream.

## Rebuilding the index

After bulk imports, schema changes, or when the index drifts out of sync with reality:

```sh
grunt build-search
```

Internally this runs `php yii content-search/rebuild` from the `protected` directory.

## Backends

The default driver writes into the database. Alternative drivers (e.g. ZendLucene) live under `humhub\modules\content\search\driver\`. The active driver is configured in the `content` module settings.
