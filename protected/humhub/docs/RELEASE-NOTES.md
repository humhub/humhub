Release Notes
=============

This file gives an overview of the most important new features and changes for each major update.

A complete overview of all changes, especially technical enhancements, can be found in the changelog. 
In regards to own themes and individual modules, the corresponding guides at https://docs.humhub.com/ must also be consulted! 

1.5 (April 2020)
----------------

**Realtime Updates (#3924)**

An indicator for new content was added to the timeline.
The activity widget updates in realtime when there is new content e.g. new comments.

This is a big step towards our goal of displaying more and more content and interactions in realtime without the need to reload.

**Permission Management (#3557)**
  
Permissions on space or group level can now be filtered by module. 
This allows you to comfortably manage permissions without loosing track of all available options. 

**User profile posts (#3950)**
 
In previous versions, profile timelines were often empty even though the user itself was very active whitin the community.
This was due to the fact that only direct posts to the own timeline have been displayed on it. 
From now on content posted by the user in spaces will also be displayed on his timeline.
The individual profile privacy settings will be taken into account, of course.

**Dashboard filter**

Filters on the dashboard will only be displayed, if profile posts directly from the timeline are enabled.

**Legacy modules (#3958)**

Of course, with our continuously growing module marketplace, it can happen that certain modules are no longer maintained or are replaced by more powerful alternatives. In order to maintain high quality standards and to provide information about modules that are currently not actively maintained as early as possible, expiring modules are now marked with a "Legacy" tag.   

Legacy modules can still be used and we will try to provide migration steps to alternatives.

**Profile Administration (#3916)**

The admin view to manage the available profile fields has been reworked and improved.

**Other notable changes:**

- The entries in the navigation directory now also have icons (#3844)
- Advanced search and filter administrative log entries have been added (#3909)
- Added possibility to directly send test emails (#3937)
- Replaced space delete & archive buttons

**Administrative notes:**

- SameSite Cookie Support
- Removed caching section from `.htaccess` file. See [Documentation](https://docs.humhub.org/docs/admin/performance#http-caching) for more details. 
- Removed ImageConverter class, switched to Imagine library (#3402)
    - ImageMagick "convert" command not longer exists
    - New optional PHP extensions (ImageMagicks, GraphicsMagick) 
