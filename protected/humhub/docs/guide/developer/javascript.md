JavaScript
==========

Summary of general JavaScript functions and events used in HumHub.

## Ajax Loaded Event

Each page that is loading with Ajax via the renderPartial method, fire a JavaScript event named "ajaxLoaded" to signalize that the view is successfully loaded.
To catch this event you have to set a trigger at the body tag:

    $( "body" ).on( "ajaxLoaded", function( event, controllerID, moduleID, actionID, view ) {
        alert( controllerID + "\n" + moduleID  + "\n" + actionID + "\n" + view );
    });

``Note:`` To deliver JavaScript with the view loaded via ajax, you have to set the renderPartial param "$processOutput" to true!
