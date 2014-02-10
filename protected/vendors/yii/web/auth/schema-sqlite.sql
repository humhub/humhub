/**
 * Database schema required by CDbAuthManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 1.0
 */

drop table if exists 'AuthAssignment';
drop table if exists 'AuthItemChild';
drop table if exists 'AuthItem';

create table 'AuthItem'
(
   "name"                 varchar(64) not null,
   "type"                 integer not null,
   "description"          text,
   "bizrule"              text,
   "data"                 text,
   primary key ("name")
);

create table 'AuthItemChild'
(
   "parent"               varchar(64) not null,
   "child"                varchar(64) not null,
   primary key ("parent","child"),
   foreign key ("parent") references 'AuthItem' ("name") on delete cascade on update cascade,
   foreign key ("child") references 'AuthItem' ("name") on delete cascade on update cascade
);

create table 'AuthAssignment'
(
   "itemname"             varchar(64) not null,
   "userid"               varchar(64) not null,
   "bizrule"              text,
   "data"                 text,
   primary key ("itemname","userid"),
   foreign key ("itemname") references 'AuthItem' ("name") on delete cascade on update cascade
);
