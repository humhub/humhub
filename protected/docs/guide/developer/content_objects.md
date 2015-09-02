Content Objects
===============

There are three basic base classes which are used to store content.


Container (HActiveRecordContentContainer)
-----------------------------------------

Base Class: HActiveRecordContentContainer

### Examples
* User
* Space

Content (HActiveRecordContent)
------------------------------

Each content is linked to a content container.

All subclasses of HActiveRecordContent will automatically bound to a Content 
Model. This Model is reponsible for all generic content features like (ACL,
Wall, ...). You can access this underlying Content model via the ``content`` attribute.

Base Class: HActiveRecordContent

### Examples
* Post
* Poll
* Task

### Stream / Wall

TDB




Addon (HActiveRecordContentAddon)
---------------------------------

Each content addon is linked to a content object which can be accessed via
``content`` attribute.

Base Class: HActiveRecordContentAddon

### Examples
* Like
* Comment
* File
