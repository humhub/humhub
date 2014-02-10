User
====

After you defined some userModules in your autostart.php file  the module is available 
under the **Account Settings -> Modules** Section.

User Profiles
-------------

When providing extra functionalities for user profiles be sure that you always add the
ProfileControllerBehavior to your controller and pass the parameter uguid (User GUID).
This way you can access the current user with getUser() Method provided by behavior.


Example of a User Profile Addon Controller:

    class MyModuleControllerController extends Controller {

        // Use standard profile layout (menu, ...)
        public $subLayout = "application.modules_core.user.views.profile._layout";

        /**
         * Add behaviors to this controller
         * 
         * @return type
         */
        public function behaviors() {
            return array(
                /**
                  * This behavior provides the method getUser() inside your controller
                  * which always returns the user of the current profile
                  */
                'ProfileControllerBehavior' => array(
                    'class' => 'application.modules_core.user.ProfileControllerBehavior',
                ),
            );
        }
        ...
    }
