Special Topics
==============

## Integrity Checker

The integrity checker is a command which validates and if necessary repairs the application database.

If you want to add own checking methods for your module to it, you can intercept the ``onRun`` event of ``IntegrityChecker`` class.

Example in MyModule Base Class:

    /**
     * On run of integrity check command, validate all module data
     * 
     * @param type $event
     */
    public static function onIntegrityCheck($event) {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating MyModule (" . MyModuleContent::model()->count() . " entries)");

        // Do Checking

    }


## Javascript

### Ajax Loaded Event

Each page that is loading with Ajax via the renderPartial method, fire a JavaScript event named "ajaxLoaded" to signalize that the view is successfully loaded.
To catch this event you have to set a trigger at the body tag:

    $( "body" ).on( "ajaxLoaded", function( event, controllerID, moduleID, actionID, view ) {
        alert( controllerID + "\n" + moduleID  + "\n" + actionID + "\n" + view );
    });

``Note:`` To deliver JavaScript with the view loaded via ajax, you have to set the renderPartial param "$processOutput" to true!



