Activities
==========

Activities are a "sub" stream which shows information about recent actions.

## Example activity

    $activity = Activity::CreateForContent($this);
    $activity->type = "PostCreated";
    $activity->module = "post";
    $activity->save();
    $activity->fire();

The ActivityWidget class automatically searches under ``protected/modules/post/views/activities/`` for a view of given type.

So in this case:

* protected/modules/post/views/activities/PostCreated.php - will used for standard activity output
* protected/modules/post/views/activities/PostCreated_mail.php - will used for mail activity output


See ActivityWidget API or Example Module for more details.
