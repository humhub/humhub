Search
======

TBD


See [[\humhub\modules\search\interfaces\Searchable]] interface for more details.

Example:


```php

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\search\interfaces\Searchable;

class Post extends ContentActiveRecord implements \humhub\modules\search\interfaces\Searchable
{
	// ...

	// Adds/Deletes automatically record from/to search
    public function behaviors()
    {
        return array(
            \humhub\modules\search\behaviors\Searchable::className()
        );
    }

	// Searchable Attributes
    public function getSearchAttributes()
    {
        return array(
            'message' => $this->message,
            'url' => $this->url,
        );
    }

	// Also used as Search Result
	public function getWallOut()
    {
        return \humhub\modules\post\widgets\Wall::widget(['post' => $this]);
    }

}

```


## Non Content objects

If you want to add also non Content objects to the search index, you need to catch and handle the event [[\humhub\modules\search\engine\Search::EVENT_ON_REBUILD]]

Example:

```php

public static function onSearchRebuild($event)
{
    foreach (models\User::find()->all() as $obj) {
        \Yii::$app->search->add($obj);
    }
}

```
