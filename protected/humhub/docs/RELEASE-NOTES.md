Release Notes
=============

This file gives an overview of the most important new features and changes for each major version.

A complete overview of the changes, especially with technical improvements, can be found in the changelog. 
For own themes or individual modules, the corresponding guides at https://docs.humhub.com/ must also be consulted! 

1.5 (April 2020)
----------------

**Realtime Updates (#3924)**

There is now a direct indication on the timeline as soon as new content is available.
The activity widget also now automatically updates itself when there are news such as a new comment.

This is a big step towards our goal of displaying more and more actions directly to the user without the need to reload.

**Permission management (#3557)**
  
Permissions management on space or group level can now be easily filtered by module. 
This allows you to comfortably manage permissions although the finely granular and modular system gives a variety of options.

**User profile posts (#3950)**
 
in previous versions, user timelines were often empty even though the user itself were very active on the network.
This is due to the fact that only direct posts have been displayed on profile timelines.
From now on also contents of the user from Spaces are displayed in his profile timeline. 
Of course these contents are displayed based on the current profile visitor.

**Dashboard filter**

If the dashboard has the option enabled to write posts directly to the profile, the filter selection for the dashboard now also appears.

**Legacy modules (#3958)**

Of course, with our continuously growing module marketplace, it can happen that certain modules are no longer maintained or are replaced by more powerful alternatives.
In order to maintain high quality standard and to provide information about modules that are currently not actively maintained as early as possible, expiring modules are now marked with a "legacy"  badge.   

Of course such modules can still be used and we also try to provide migration steps to alternatives.

**Profile Administration (#3916)**

The view for administrators to manage the available profile fields has been reworked and improved.

**Other notable changes:**

- The entries in the Navigation directory now also have icons (#3844)
- Advanced search and filter administrative log entries (#3909)
- Add possibility to directly send a test email in the mail settings (#3937)
- Replaced Space delete & archive buttons

**Administrative notes:**

- SameSite Cookie Support
- Removed caching section from `.htaccess` file. See [Documentation](https://docs.humhub.org/docs/admin/performance#http-caching) for more details. 
- Removed ImageConverter class, switched to Imagine library (#3402)
    - ImageMagick "convert" command not longer exists
    - New optional PHP extensions (ImageMagicks, GraphicsMagick) 
