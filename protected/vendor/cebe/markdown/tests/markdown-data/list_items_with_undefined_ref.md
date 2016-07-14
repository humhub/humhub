* [[\yii\caching\ApcCache]]: uses PHP [APC](http://php.net/manual/en/book.apc.php) extension. This option can be
  considered as the fastest one when dealing with cache for a centralized thick application (e.g. one
  server, no dedicated load balancers, etc.).

* [[\yii\caching\DbCache]]: uses a database table to store cached data. By default, it will create and use a
  [SQLite3](http://sqlite.org/) database under the runtime directory. You can explicitly specify a database for
  it to use by setting its `db` property.
