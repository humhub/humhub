<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecordInterface;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * See [[\yii\db\ActiveRecord]] for a concrete implementation.
 *
 * @property-read array $dirtyAttributes The changed attribute values (name-value pairs).
 * @property bool       $isNewRecord     Whether the record is new and should be inserted when calling [[save()]].
 * @property array      $oldAttributes   The old attribute values (name-value pairs). Note that the type of this
 * property differs in getter and setter. See [[getOldAttributes()]] and [[setOldAttributes()]] for details.
 * @property-read mixed $oldPrimaryKey   The old primary key value. An array (column name => column value) is
 * returned if the primary key is composite or `$asArray` is `true`. A string is returned otherwise (null will be
 * returned if the key value is null).
 * @property-read mixed $primaryKey      The primary key value. An array (column name => column value) is returned
 * if the primary key is composite or `$asArray` is `true`. A string is returned otherwise (null will be returned
 * if the key value is null).
 * @property-read array $relatedRecords  An array of related records indexed by relation names.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since  2.0
 */
interface BaseActiveRecord extends ModelInterface, ActiveRecordInterface
{
    /**
     * Returns the name of the column that stores the lock version for implementing optimistic locking.
     *
     * Optimistic locking allows multiple users to access the same record for edits and avoids
     * potential conflicts. In case when a user attempts to save the record upon some staled data
     * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
     * and the update or deletion is skipped.
     *
     * Optimistic locking is only supported by [[update()]] and [[delete()]].
     *
     * To use Optimistic locking:
     *
     * 1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
     *    Override this method to return the name of this column.
     * 2. Ensure the version value is submitted and loaded to your model before any update or delete.
     *    Or add [[\yii\behaviors\OptimisticLockBehavior|OptimisticLockBehavior]] to your model
     *    class in order to automate the process.
     * 3. In the Web form that collects the user input, add a hidden field that stores
     *    the lock version of the record being updated.
     * 4. In the controller action that does the data updating, try to catch the [[StaleObjectException]]
     *    and implement necessary business logic (e.g. merging the changes, prompting stated data)
     *    to resolve the conflict.
     *
     * @return string|null the column name that stores the lock version of a table row.
     * If `null` is returned (default implemented), optimistic locking will not be supported.
     */
    public function optimisticLock();

    /**
     * {@inheritdoc}
     */
    public function canGetProperty(
        $name,
        $checkVars = true,
        $checkBehaviors = true
    );

    /**
     * {@inheritdoc}
     */
    public function canSetProperty(
        $name,
        $checkVars = true,
        $checkBehaviors = true
    );

    /**
     * Declares a `has-one` relation.
     * The declaration is returned in terms of a relational [[ActiveQuery]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-one` relation means that there is at most one related record matching
     * the criteria set by this relation, e.g., a customer has one country.
     *
     * For example, to declare the `country` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ```php
     * public function getCountry()
     * {
     *     return $this->hasOne(Country::class, ['id' => 'country_id']);
     * }
     * ```
     *
     * Note that in the above, the 'id' key in the `$link` parameter refers to an attribute name
     * in the related class `Country`, while the 'country_id' value refers to an attribute name
     * in the current AR class.
     *
     * Call methods declared in [[ActiveQuery]] to further customize the relation.
     *
     * @param string $class the class name of the related record
     * @param array  $link  the primary-foreign key constraint. The keys of the array refer to
     *                      the attributes of the record associated with the `$class` model, while the values of the
     *                      array refer to the corresponding attributes in **this** AR class.
     *
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasOne(
        $class,
        $link
    );

    /**
     * Declares a `has-many` relation.
     * The declaration is returned in terms of a relational [[ActiveQuery]] instance
     * through which the related record can be queried and retrieved back.
     *
     * A `has-many` relation means that there are multiple related records matching
     * the criteria set by this relation, e.g., a customer has many orders.
     *
     * For example, to declare the `orders` relation for `Customer` class, we can write
     * the following code in the `Customer` class:
     *
     * ```php
     * public function getOrders()
     * {
     *     return $this->hasMany(Order::class, ['customer_id' => 'id']);
     * }
     * ```
     *
     * Note that in the above, the 'customer_id' key in the `$link` parameter refers to
     * an attribute name in the related class `Order`, while the 'id' value refers to
     * an attribute name in the current AR class.
     *
     * Call methods declared in [[ActiveQuery]] to further customize the relation.
     *
     * @param string $class the class name of the related record
     * @param array  $link  the primary-foreign key constraint. The keys of the array refer to
     *                      the attributes of the record associated with the `$class` model, while the values of the
     *                      array refer to the corresponding attributes in **this** AR class.
     *
     * @return ActiveQueryInterface the relational query object.
     */
    public function hasMany(
        $class,
        $link
    );

    /**
     * Populates the named relation with the related records.
     * Note that this method does not check if the relation exists or not.
     *
     * @param string                           $name    the relation name, e.g. `orders` for a relation defined via
     *                                                  `getOrders()` method (case-sensitive).
     * @param ActiveRecordInterface|array|null $records the related records to be populated into the relation.
     *
     * @see getRelation()
     */
    public function populateRelation(
        $name,
        $records
    );

    /**
     * Check whether the named relation has been populated with records.
     *
     * @param string $name the relation name, e.g. `orders` for a relation defined via `getOrders()` method
     *                     (case-sensitive).
     *
     * @return bool whether relation has been populated with records.
     * @see getRelation()
     */
    public function isRelationPopulated($name);

    /**
     * Returns all populated related records.
     *
     * @return array an array of related records indexed by relation names.
     * @see getRelation()
     */
    public function getRelatedRecords();

    /**
     * Returns a value indicating whether the model has an attribute with the specified name.
     *
     * @param string $name the name of the attribute
     *
     * @return bool whether the model has an attribute with the specified name.
     */
    public function hasAttribute($name);

    /**
     * Returns the named attribute value.
     * If this record is the result of a query and the attribute is not loaded,
     * `null` will be returned.
     *
     * @param string $name the attribute name
     *
     * @return mixed the attribute value. `null` if the attribute is not set or does not exist.
     * @see hasAttribute()
     */
    public function getAttribute($name);

    /**
     * Sets the named attribute value.
     *
     * @param string $name  the attribute name
     * @param mixed  $value the attribute value.
     *
     * @throws InvalidArgumentException if the named attribute does not exist.
     * @see hasAttribute()
     */
    public function setAttribute(
        $name,
        $value
    );

    /**
     * Returns the old attribute values.
     *
     * @return array the old attribute values (name-value pairs)
     */
    public function getOldAttributes();

    /**
     * Sets the old attribute values.
     * All existing old attribute values will be discarded.
     *
     * @param array|null $values old attribute values to be set.
     *                           If set to `null` this record is considered to be [[isNewRecord|new]].
     */
    public function setOldAttributes($values);

    /**
     * Returns the old value of the named attribute.
     * If this record is the result of a query and the attribute is not loaded,
     * `null` will be returned.
     *
     * @param string $name the attribute name
     *
     * @return mixed the old attribute value. `null` if the attribute is not loaded before
     * or does not exist.
     * @see hasAttribute()
     */
    public function getOldAttribute($name);

    /**
     * Sets the old value of the named attribute.
     *
     * @param string $name  the attribute name
     * @param mixed  $value the old attribute value.
     *
     * @throws InvalidArgumentException if the named attribute does not exist.
     * @see hasAttribute()
     */
    public function setOldAttribute(
        $name,
        $value
    );

    /**
     * Marks an attribute dirty.
     * This method may be called to force updating a record when calling [[update()]],
     * even if there is no change being made to the record.
     *
     * @param string $name the attribute name
     */
    public function markAttributeDirty($name);

    /**
     * Returns a value indicating whether the named attribute has been changed.
     *
     * @param string $name      the name of the attribute.
     * @param bool   $identical whether the comparison of new and old value is made for
     *                          identical values using `===`, defaults to `true`. Otherwise `==` is used for comparison.
     *                          This parameter is available since version 2.0.4.
     *
     * @return bool whether the attribute has been changed
     */
    public function isAttributeChanged(
        $name,
        $identical = true
    );

    /**
     * Returns the attribute values that have been modified since they are loaded or saved most recently.
     *
     * The comparison of new and old values is made for identical values using `===`.
     *
     * @param string[]|null $names the names of the attributes whose values may be returned if they are
     *                             changed recently. If null, [[attributes()]] will be used.
     *
     * @return array the changed attribute values (name-value pairs)
     */
    public function getDirtyAttributes($names = null);

    /**
     * Saves the current record.
     *
     * This method will call [[insert()]] when [[isNewRecord]] is `true`, or [[update()]]
     * when [[isNewRecord]] is `false`.
     *
     * For example, to save a customer record:
     *
     * ```php
     * $customer = new Customer; // or $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->save();
     * ```
     *
     * @param bool       $runValidation  whether to perform validation (calling [[validate()]])
     *                                   before saving the record. Defaults to `true`. If the validation fails, the
     *                                   record will not be saved to the database and this method will return `false`.
     * @param array|null $attributeNames list of attribute names that need to be saved. Defaults to null,
     *                                   meaning all attributes that are loaded from DB will be saved.
     *
     * @return bool whether the saving succeeded (i.e. no validation errors occurred).
     */
    public function save(
        $runValidation = true,
        $attributeNames = null
    );

    /**
     * Saves the changes to this active record into the associated database table.
     *
     * This method performs the following steps in order:
     *
     * 1. call [[beforeValidate()]] when `$runValidation` is `true`. If [[beforeValidate()]]
     *    returns `false`, the rest of the steps will be skipped;
     * 2. call [[afterValidate()]] when `$runValidation` is `true`. If validation
     *    failed, the rest of the steps will be skipped;
     * 3. call [[beforeSave()]]. If [[beforeSave()]] returns `false`,
     *    the rest of the steps will be skipped;
     * 4. save the record into database. If this fails, it will skip the rest of the steps;
     * 5. call [[afterSave()]];
     *
     * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
     * [[EVENT_AFTER_VALIDATE]], [[EVENT_BEFORE_UPDATE]], and [[EVENT_AFTER_UPDATE]]
     * will be raised by the corresponding methods.
     *
     * Only the [[dirtyAttributes|changed attribute values]] will be saved into database.
     *
     * For example, to update a customer record:
     *
     * ```php
     * $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->update();
     * ```
     *
     * Note that it is possible the update does not affect any row in the table.
     * In this case, this method will return 0. For this reason, you should use the following
     * code to check if update() is successful or not:
     *
     * ```php
     * if ($customer->update() !== false) {
     *     // update successful
     * } else {
     *     // update failed
     * }
     * ```
     *
     * @param bool       $runValidation  whether to perform validation (calling [[validate()]])
     *                                   before saving the record. Defaults to `true`. If the validation fails, the
     *                                   record will not be saved to the database and this method will return `false`.
     * @param array|null $attributeNames list of attribute names that need to be saved. Defaults to null,
     *                                   meaning all attributes that are loaded from DB will be saved.
     *
     * @return int|false the number of rows affected, or `false` if validation fails
     * or [[beforeSave()]] stops the updating process.
     * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
     * being updated is outdated.
     * @throws Exception in case update failed.
     */
    public function update(
        $runValidation = true,
        $attributeNames = null
    );

    /**
     * Updates the specified attributes.
     *
     * This method is a shortcut to [[update()]] when data validation is not needed
     * and only a small set attributes need to be updated.
     *
     * You may specify the attributes to be updated as name list or name-value pairs.
     * If the latter, the corresponding attribute values will be modified accordingly.
     * The method will then save the specified attributes into database.
     *
     * Note that this method will **not** perform data validation and will **not** trigger events.
     *
     * @param array $attributes the attributes (names or name-value pairs) to be updated
     *
     * @return int the number of rows affected.
     */
    public function updateAttributes($attributes);

    /**
     * Updates one or several counter columns for the current AR object.
     * Note that this method differs from [[updateAllCounters()]] in that it only
     * saves counters for the current AR object.
     *
     * An example usage is as follows:
     *
     * ```php
     * $post = Post::findOne($id);
     * $post->updateCounters(['view_count' => 1]);
     * ```
     *
     * @param array $counters the counters to be updated (attribute name => increment value)
     *                        Use negative values if you want to decrement the counters.
     *
     * @return bool whether the saving is successful
     * @see updateAllCounters()
     */
    public function updateCounters($counters);

    /**
     * Deletes the table row corresponding to this active record.
     *
     * This method performs the following steps in order:
     *
     * 1. call [[beforeDelete()]]. If the method returns `false`, it will skip the
     *    rest of the steps;
     * 2. delete the record from the database;
     * 3. call [[afterDelete()]].
     *
     * In the above step 1 and 3, events named [[EVENT_BEFORE_DELETE]] and [[EVENT_AFTER_DELETE]]
     * will be raised by the corresponding methods.
     *
     * @return int|false the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
     * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
     * being deleted is outdated.
     * @throws Exception in case delete failed.
     */
    public function delete();

    /**
     * Returns a value indicating whether the current record is new.
     *
     * @return bool whether the record is new and should be inserted when calling [[save()]].
     */
    public function getIsNewRecord();

    /**
     * Sets the value indicating whether the record is new.
     *
     * @param bool $value whether the record is new and should be inserted when calling [[save()]].
     *
     * @see getIsNewRecord()
     */
    public function setIsNewRecord($value);

    /**
     * Initializes the object.
     * This method is called at the end of the constructor.
     * The default implementation will trigger an [[EVENT_INIT]] event.
     */
    public function init();

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind();

    /**
     * This method is called at the beginning of inserting or updating a record.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is `false`.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (!parent::beforeSave($insert)) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @param bool $insert whether this method called while inserting a record.
     *                     If `false`, it means the method is called while updating a record.
     *
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert);

    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is `true`,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is `false`. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     *
     * @param bool  $insert            whether this method called while inserting a record.
     *                                 If `false`, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     *                                 You can use this parameter to take action based on the changes made for example
     *                                 send an email when the password had changed or implement audit trail that tracks
     *                                 all the changes.
     *                                 `$changedAttributes` gives you the old attribute values while the active record
     *                                 (`$this`) has already the new, updated values.
     *
     * Note that no automatic type conversion performed by default. You may use
     * [[\yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute typecasting.
     * See https://www.yiiframework.com/doc-2.0/guide-db-active-record.html#attributes-typecasting.
     */
    public function afterSave(
        $insert,
        $changedAttributes
    );

    /**
     * This method is invoked before deleting a record.
     *
     * The default implementation raises the [[EVENT_BEFORE_DELETE]] event.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeDelete()
     * {
     *     if (!parent::beforeDelete()) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @return bool whether the record should be deleted. Defaults to `true`.
     */
    public function beforeDelete();

    /**
     * This method is invoked after deleting a record.
     * The default implementation raises the [[EVENT_AFTER_DELETE]] event.
     * You may override this method to do postprocessing after the record is deleted.
     * Make sure you call the parent implementation so that the event is raised properly.
     */
    public function afterDelete();

    /**
     * Repopulates this active record with the latest data.
     *
     * If the refresh is successful, an [[EVENT_AFTER_REFRESH]] event will be triggered.
     * This event is available since version 2.0.8.
     *
     * @return bool whether the row still exists in the database. If `true`, the latest data
     * will be populated to this active record. Otherwise, this record will remain unchanged.
     */
    public function refresh();

    /**
     * This method is called when the AR object is refreshed.
     * The default implementation will trigger an [[EVENT_AFTER_REFRESH]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     *
     * @since 2.0.8
     */
    public function afterRefresh();

    /**
     * Returns a value indicating whether the given active record is the same as the current one.
     * The comparison is made by comparing the table names and the primary key values of the two active records.
     * If one of the records [[isNewRecord|is new]] they are also considered not equal.
     *
     * @param ActiveRecordInterface $record record to compare to
     *
     * @return bool whether the two active records refer to the same row in the same database table.
     */
    public function equals($record);

    /**
     * Returns the primary key value(s).
     *
     * @param bool $asArray whether to return the primary key value as an array. If `true`,
     *                      the return value will be an array with column names as keys and column values as values.
     *                      Note that for composite primary keys, an array will always be returned regardless of this
     *                      parameter value.
     *
     * @return mixed the primary key value. An array (column name => column value) is returned if the primary key
     * is composite or `$asArray` is `true`. A string is returned otherwise (null will be returned if
     * the key value is null).
     */
    public function getPrimaryKey($asArray = false);

    /**
     * Returns the old primary key value(s).
     * This refers to the primary key value that is populated into the record
     * after executing a find method (e.g. find(), findOne()).
     * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
     *
     * @param bool $asArray whether to return the primary key value as an array. If `true`,
     *                      the return value will be an array with column name as key and column value as value.
     *                      If this is `false` (default), a scalar value will be returned for non-composite primary key.
     *
     * @return mixed the old primary key value. An array (column name => column value) is returned if the primary key
     * is composite or `$asArray` is `true`. A string is returned otherwise (null will be returned if
     * the key value is null).
     * @throws Exception if the AR model does not have a primary key
     */
    public function getOldPrimaryKey($asArray = false);

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the interface [[\ArrayAccess]].
     *
     * @param mixed $offset the offset to check on
     *
     * @return bool whether there is an element at the specified offset.
     */
    public function offsetExists($offset);

    /**
     * Returns the relation object with the specified name.
     * A relation is defined by a getter method which returns an [[ActiveQueryInterface]] object.
     * It can be declared in either the Active Record class itself or one of its behaviors.
     *
     * @param string $name           the relation name, e.g. `orders` for a relation defined via `getOrders()` method
     *                               (case-sensitive).
     * @param bool   $throwException whether to throw exception if the relation does not exist.
     *
     * @return ActiveQueryInterface|ActiveQuery|null the relational query object. If the relation does not exist
     * and `$throwException` is `false`, `null` will be returned.
     * @throws InvalidArgumentException if the named relation does not exist.
     */
    public function getRelation(
        $name,
        $throwException = true
    );

    /**
     * Establishes the relationship between two models.
     *
     * The relationship is established by setting the foreign key value(s) in one model
     * to be the corresponding primary key value(s) in the other model.
     * The model with the foreign key will be saved into database **without** performing validation
     * and **without** events/behaviors.
     *
     * If the relationship involves a junction table, a new row will be inserted into the
     * junction table which contains the primary key values from both models.
     *
     * Note that this method requires that the primary key value is not null.
     *
     * @param string                $name         the case sensitive name of the relationship, e.g. `orders` for a
     *                                            relation defined via `getOrders()` method.
     * @param ActiveRecordInterface $model        the model to be linked with the current one.
     * @param array                 $extraColumns additional column values to be saved into the junction table.
     *                                            This parameter is only meaningful for a relationship involving a
     *                                            junction table
     *                                            (i.e., a relation set with [[ActiveRelationTrait::via()]] or
     *                                            [[ActiveQuery::viaTable()]].)
     *
     * @throws InvalidCallException if the method is unable to link two models.
     */
    public function link(
        $name,
        $model,
        $extraColumns = []
    );

    /**
     * Destroys the relationship between two models.
     *
     * The model with the foreign key of the relationship will be deleted if `$delete` is `true`.
     * Otherwise, the foreign key will be set `null` and the model will be saved without validation.
     *
     * @param string                $name   the case sensitive name of the relationship, e.g. `orders` for a relation
     *                                      defined via `getOrders()` method.
     * @param ActiveRecordInterface $model  the model to be unlinked from the current one.
     *                                      You have to make sure that the model is really related with the current
     *                                      model as this method does not check this.
     * @param bool                  $delete whether to delete the model that contains the foreign key.
     *                                      If `false`, the model's foreign key will be set `null` and saved.
     *                                      If `true`, the model containing the foreign key will be deleted.
     *
     * @throws InvalidCallException if the models cannot be unlinked
     * @throws Exception
     * @throws StaleObjectException
     */
    public function unlink(
        $name,
        $model,
        $delete = false
    );

    /**
     * Destroys the relationship in current model.
     *
     * The model with the foreign key of the relationship will be deleted if `$delete` is `true`.
     * Otherwise, the foreign key will be set `null` and the model will be saved without validation.
     *
     * Note that to destroy the relationship without removing records make sure your keys can be set to null
     *
     * @param string $name   the case sensitive name of the relationship, e.g. `orders` for a relation defined via
     *                       `getOrders()` method.
     * @param bool   $delete whether to delete the model that contains the foreign key.
     *
     * Note that the deletion will be performed using [[deleteAll()]], which will not trigger any events on the related
     * models. If you need [[EVENT_BEFORE_DELETE]] or [[EVENT_AFTER_DELETE]] to be triggered, you need to
     * [[find()|find]] the models first and then call [[delete()]] on each of them.
     */
    public function unlinkAll(
        $name,
        $delete = false
    );

    /**
     * Returns the text label for the specified attribute.
     * If the attribute looks like `relatedModel.attribute`, then the attribute will be received from the related model.
     *
     * @param string $attribute the attribute name
     *
     * @return string the attribute label
     * @see generateAttributeLabel()
     * @see attributeLabels()
     */
    public function getAttributeLabel($attribute);

    /**
     * Returns the text hint for the specified attribute.
     * If the attribute looks like `relatedModel.attribute`, then the attribute will be received from the related model.
     *
     * @param string $attribute the attribute name
     *
     * @return string the attribute hint
     * @see   attributeHints()
     * @since 2.0.4
     */
    public function getAttributeHint($attribute);

    /**
     * {@inheritdoc}
     *
     * The default implementation returns the names of the columns whose values have been populated into this record.
     */
    public function fields();

    /**
     * {@inheritdoc}
     *
     * The default implementation returns the names of the relations that have been populated into this record.
     */
    public function extraFields();

    /**
     * Sets the element value at the specified offset to null.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `unset($model[$offset])`.
     *
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset);

}
