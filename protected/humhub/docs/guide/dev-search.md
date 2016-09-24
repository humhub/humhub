Search
======

TBD


See [[\humhub\modules\search\interfaces\Searchable]] interface for more details.

Example:


```php

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\search\interfaces\Searchable;

class Post extends ContentActiveRecord implements Searchable
{
	// ...

	// This is required to display the search result
    public $wallEntryClass = "humhub\modules\post\widgets\WallEntry";

	// Searchable Attributes / Information
    public function getSearchAttributes()
    {
        return array(
            'message' => $this->message,
            'url' => $this->url,
			'someTextField' => 'Some text'
        );
    }

	// ...

}

```


## Non Content 

> TBD

It's also required to handle/implement the [[\humhub\modules\search\engine\Search::EVENT_ON_REBUILD]] event to rebuild the search index if nessessary.

Example:

```php

public static function onSearchRebuild($event)
{
    foreach (models\NonContent::find()->all() as $obj) {
        \Yii::$app->search->add($obj);
    }
}

```
