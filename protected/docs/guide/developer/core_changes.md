Core Changes / Changelog
=========================

This file shows changes of the core api for migrating your modules between new versions.

# 0.6

- Unified access to current content container (space, user) to allow module controllers act as
  user or space addon at once. (ContentContainerController)
- Moved all Space/User Behaviors in behaviors folder!
- New required "module.json" file with informations about module (see documentation) 
- Cleanup of autostart.php (see documentation) New only: id, class, imports & events 
- New HWebModule Base Class for Modules (Change CWebModule to HWebModule)
- No longer need to check on events whether module is enabled or not (besides spaces, user)
- Spaces/Users Modules need to add new behavior to its module class
 