Content
=======

In ZAMM we differ content objects in two areas.

---

Content
-------

``Content`` are the major content objects. These have distinct access privileges which are linked to a space or user. 

### Examples
* Post
* Task
* Question

Each Content Class *must* extend the ``SIActiveRecordContent`` class. 

If the content object should also be displayed in a Wall/Stream there must be a public method ``getWallOut`` which returns the wall entry.
See [Wall Entries](wall_entries.md) for more details.

Each content object gets a content active record instance by  ``SIActiveRecordContent`` parent class automatically.
This content record is responsible for generic stuff like:

* Wall Bindings & Functions
* Archiving
* Security (canRead, canWrite, ...)
* Visibilty
* Deletion
* ...

The underlying content object of a Content can be accessed by the ``contentMeta`` attribute.

Example:
    $canRead = $post->contentMeta->canRead();


You also need to register new content objects in the autostart.php of your module.

    Yii::app()->moduleManager->registerContentModel('MyContent');

---


ContentAddon
------------

``ContentAddons`` are always linked to a ``Content`` or ``ContentAddon`` object and provides additional information for it.

### Examples
* Comment
* Like
* Files

Each ContentAddon Class *must* extends the ``SIActiveRecordContentAddon`` class and implements the ``SIContentAddonBehavior`` behavior.

Also important: 
     Post (Content) -> Comment (ContentAddon) -> Like(ContentAddon)


