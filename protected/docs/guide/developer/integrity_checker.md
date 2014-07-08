Integrity Checker
=================

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




