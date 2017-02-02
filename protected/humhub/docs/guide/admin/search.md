Search 
======


### Search Index Rebuilding

If you need to rebuild the search index (e.g. after updating) you need to run following command:

```
cd /path/to/humhub/protected
php yii search/rebuild
```

### Zend Lucence

By default HumHub is using a *Lucence* Index (Zend Lucence) to store search data.
Folder:  */protected/runtime/searchdb/*
