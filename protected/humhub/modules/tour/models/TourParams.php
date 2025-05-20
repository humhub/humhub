<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\models;

use humhub\modules\admin\controllers\ModuleController;
use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\tour\Module;
use humhub\modules\user\controllers\ProfileController;
use humhub\widgets\bootstrap\Button;
use Yii;
use yii\helpers\Url;

/**
 * Parameters for the introduction tour
 *
 * @since 1.18
 */
class TourParams
{
    public const PAGE_DASHBOARD = 'interface';
    public const PAGE_SPACES = 'spaces';
    public const PAGE_PROFILE = 'profile';
    public const PAGE_ADMINISTRATION = 'administration';
    public const KEY_PAGE = 'page';
    public const KEY_NAME = 'name';
    public const KEY_CONTROLLER_CLASS = 'controller_class';
    public const KEY_URL = 'url';
    public const KEY_NEXT_PAGE = 'next_page';
    /**
     * Available values: https://driverjs.com/docs/
     */
    public const KEY_DRIVER = 'driver';

    public static function get(): array
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');

        if (is_array($module->customTourParams)) {
            $validCustomParams = [];
            foreach ($module->customTourParams as $params) {
                if (static::isValidParams($params)) {
                    $validCustomParams[] = $params;
                }
            }
            return $validCustomParams;
        }

        $params = [static::getDashboard()];

        if (static::getTourSpace()) {
            $params[] = static::getSpaces();
        }

        $params[] = static::getProfile();

        if (Yii::$app->user->isAdmin()) {
            $params[] = static::getAdministration();
        }

        return $params;
    }

    public static function isValidParams($params): bool
    {
        $isValid =
            is_array($params)
            && !empty($params[self::KEY_PAGE])
            && !empty($params[self::KEY_NAME])
            && !empty($params[self::KEY_CONTROLLER_CLASS])
            && !empty($params[self::KEY_URL])
            && array_key_exists(self::KEY_NEXT_PAGE, $params)
            && !empty($params[self::KEY_DRIVER]);

        if (!$isValid) {
            Yii::error("Invalid Tour params: " . print_r($params, true), 'tour');
        }

        return $isValid;
    }

    public static function getCurrent(): ?array
    {
        foreach (self::get() as $params) {
            if (Yii::$app->controller instanceof $params[self::KEY_CONTROLLER_CLASS]) {
                return $params;
            }
        }

        return null;
    }

    public static function isPageAcceptable(string $page): bool
    {
        foreach (self::get() as $params) {
            if ($page === $params[self::KEY_PAGE]) {
                return true;
            }
        }

        return false;
    }

    private static function getDashboard(): array
    {
        return [
            self::KEY_PAGE => self::PAGE_DASHBOARD,
            self::KEY_CONTROLLER_CLASS => DashboardController::class,
            self::KEY_NAME => Yii::t('TourModule.base', '<strong>Guide:</strong> Overview'),
            self::KEY_URL => Url::to(['/dashboard/dashboard', 'tour' => true]),
            self::KEY_NEXT_PAGE => self::PAGE_SPACES,
            self::KEY_DRIVER => [
                'steps' => [
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.interface', '<strong>Dashboard</strong>'),
                            'description' => Yii::t('TourModule.interface', "This is your dashboard.<br><br>Any new activities or posts that might interest you will be displayed here."),
                        ],
                    ],
                    [
                        'element' => "#icon-notifications",
                        'popover' => [
                            'title' => Yii::t('TourModule.base', '<strong>Notifications</strong>'),
                            'description' => Yii::t('TourModule.base', 'Don\'t lose track of things!<br /><br />This icon will keep you informed of activities and posts that concern you directly.'),
                        ],
                    ],
                    [
                        'element' => ".dropdown.account",
                        'popover' => [
                            'title' => Yii::t('TourModule.base', '<strong>Account</strong> Menu'),
                            'description' => Yii::t('TourModule.base', 'The account menu gives you access to your private settings and allows you to manage your public profile.'),
                        ],
                    ],
                    [
                        'element' => "#space-menu",
                        'popover' => [
                            'title' => Yii::t('TourModule.base', '<strong>Space</strong> Menu'),
                            'description' =>
                                Yii::t('TourModule.base', 'This is the most important menu and will probably be the one you use most often!<br><br>Access all the spaces you have joined and create new spaces here.<br><br>The next guide will show you how:') .
                                '<br><br>' .
                                Button::asLink(Yii::t("TourModule.base", "<strong>Start</strong> space guide"))->action('tour.next') .
                                '<br><br>',
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function getSpaces(): array
    {
        return [
            self::KEY_PAGE => self::PAGE_SPACES,
            self::KEY_CONTROLLER_CLASS => SpaceController::class,
            self::KEY_NAME => Yii::t('TourModule.base', '<strong>Guide:</strong> Spaces'),
            self::KEY_URL => static::getTourSpace()?->createUrl('/space/space', ['tour' => true]),
            self::KEY_NEXT_PAGE => self::PAGE_PROFILE,
            self::KEY_DRIVER => [
                'steps' => [
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Space</strong>'),
                            'description' => Yii::t('TourModule.spaces', "Once you have joined or created a new space you can work on projects, discuss topics or just share information with other users.<br><br>There are various tools to personalize a space, thereby making the work process more productive."),
                        ],
                    ],
                    [
                        'element' => ".layout-nav-container .panel",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> navigation menu'),
                            'description' => Yii::t('TourModule.spaces', 'This is where you can navigate the space – where you find which modules are active or available for the particular space you are currently in. These could be polls, tasks or notes for example.<br><br>Only the space admin can manage the space\'s modules.'),
                        ],
                    ],
                    [
                        'element' => ".dropdown",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> preferences'),
                            'description' => Yii::t('TourModule.spaces', 'This menu is only visible for space admins. Here you can manage your space settings, add/block members and activate/deactivate tools for this space.'),
                        ],
                    ],
                    [
                        'element' => "#contentFormBody",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Writing</strong> posts'),
                            'description' => Yii::t('TourModule.spaces', 'New posts can be written and posted here.'),
                        ],
                    ],
                    [
                        'element' => ".wall-entry:eq(0)",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Posts</strong>'),
                            'description' => Yii::t('TourModule.spaces', 'Yours, and other users\' posts will appear here.<br><br>These can then be liked or commented on.'),
                        ],
                    ],
                    [
                        'element' => ".panel-activities",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Most recent</strong> activities'),
                            'description' => Yii::t('TourModule.spaces', 'To keep you up to date, other users\' most recent activities in this space will be displayed here.'),
                        ],
                    ],
                    [
                        'element' => "#space-members-panel",
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Space</strong> members'),
                            'description' => Yii::t('TourModule.spaces', 'All users who are a member of this space will be displayed here.<br /><br />New members can be added by anyone who has been given access rights by the admin.'),
                        ],
                    ],
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.spaces', '<strong>Yay! You\'re done.</strong>'),
                            'description' =>
                                Yii::t('TourModule.spaces', "That's it for the space guide.<br><br>To carry on with the user profile guide, click here: ") .
                                Button::asLink(Yii::t("TourModule.spaces", "<strong>Profile Guide</strong>"))->action('tour.next') .
                                '<br><br>',
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function getProfile(): array
    {
        return [
            self::KEY_PAGE => self::PAGE_PROFILE,
            self::KEY_CONTROLLER_CLASS => ProfileController::class,
            self::KEY_NAME => Yii::t('TourModule.base', '<strong>Guide:</strong> User profile'),
            self::KEY_URL => Yii::$app->user->identity->createUrl('/user/profile/home', ['tour' => true]),
            self::KEY_NEXT_PAGE => Yii::$app->user?->isAdmin() ? self::PAGE_ADMINISTRATION : null,
            self::KEY_DRIVER => [
                'steps' => [
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.profile', '<strong>User profile</strong>'),
                            'description' => Yii::t('TourModule.profile', "This is your public user profile, which can be seen by any registered user."),
                        ],
                    ],
                    [
                        'element' => ".profile-user-photo-container",
                        'popover' => [
                            'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> photo'),
                            'description' => Yii::t('TourModule.profile', 'Upload a new profile photo by simply clicking here or by drag&drop. Do just the same for updating your cover photo.'),
                        ],
                    ],
                    [
                        'element' => ".edit-account",
                        'popover' => [
                            'title' => Yii::t('TourModule.profile', '<strong>Edit</strong> account'),
                            'description' => Yii::t('TourModule.profile', 'Click on this button to update your profile and account settings. You can also add more information to your profile.'),
                        ],
                    ],
                    [
                        'element' => ".layout-nav-container .panel",
                        'popover' => [
                            'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> menu'),
                            'description' => Yii::t('TourModule.profile', 'Just like in the space, the user profile can be personalized with various modules.<br><br>You can see which modules are available for your profile by looking them in "Modules" in the account settings menu.'),
                        ],
                    ],
                    [
                        'element' => "#contentFormBody",
                        'popover' => [
                            'title' => Yii::t('TourModule.profile', '<strong>Profile</strong> stream'),
                            'description' => Yii::t('TourModule.profile', 'Each profile has its own pin board. Your posts will also appear on the dashboards of those users who are following you.'),
                        ],
                    ],
                    [
                        'popover' => Yii::$app->user->isAdmin() ?
                            [
                                'title' => Yii::t('TourModule.profile', '<strong>Hurray!</strong> You\'re done!'),
                                'description' =>
                                    Yii::t('TourModule.profile', 'You\'ve completed the user profile guide!<br><br>To carry on with the administration guide, click here:<br /><br />') .
                                    Button::asLink(Yii::t("TourModule.profile", "<strong>Administration (Modules)</strong>"))->action('tour.next') .
                                    '<br><br>',
                            ] :
                            [
                                'title' => Yii::t('TourModule.profile', '<strong>Hurray!</strong> The End.'),
                                'description' => Yii::t('TourModule.profile', "You've completed the user profile guide!"),
                            ],
                    ],
                ],
            ],
        ];
    }

    private static function getAdministration(): array
    {
        return [
            self::KEY_PAGE => self::PAGE_ADMINISTRATION,
            self::KEY_CONTROLLER_CLASS => ModuleController::class,
            self::KEY_NAME => Yii::t('TourModule.base', '<strong>Guide:</strong> Administration (Modules)'),
            self::KEY_URL => Url::to(['/admin/module/list', 'tour' => true]),
            self::KEY_NEXT_PAGE => null,
            self::KEY_DRIVER => [
                'steps' => [
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.administration', '<strong>Administration</strong>'),
                            'description' => Yii::t('TourModule.administration', "As an admin, you can manage the whole platform from here.<br><br>Apart from the modules, we are not going to go into each point in detail here, as each has its own short description elsewhere."),
                        ],
                    ],
                    [
                        'element' => ".list-group-item.modules",
                        'popover' => [
                            'title' => Yii::t('TourModule.administration', '<strong>Modules</strong>'),
                            'description' => Yii::t('TourModule.administration', 'You are currently in the tools menu. From here you can access the HumHub online marketplace, where you can install an ever increasing number of tools on-the-fly.<br><br>As already mentioned, the tools increase the features available for your space.'),
                        ],
                    ],
                    [
                        'popover' => [
                            'title' => Yii::t('TourModule.administration', "<strong>Hurray!</strong> That's all for now."),
                            'description' => Yii::t('TourModule.administration', 'You have now learned about all the most important features and settings and are all set to start using the platform.<br><br>We hope you and all future users will enjoy using this site. We are looking forward to any suggestions or support you wish to offer for our project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :-)'),
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function getTourSpace(): ?Space
    {
        $space = null;

        // Loop over all spaces where the user is member
        foreach (Membership::getUserSpaces() as $space) {
            if ($space->isAdmin() && !$space->isArchived()) {
                // If user is admin on this space, it´s the perfect match
                break;
            }
        }

        if ($space === null) {
            // If user is not member of any space, try to find a public space
            // to run tour in
            $space = Space::findOne(['and', ['!=', 'visibility' => Space::VISIBILITY_NONE], ['status' => Space::STATUS_ENABLED]]);
        }

        return $space;
    }

    public static function getNextUrl(array $params): ?string
    {
        $nextPage = $params[self::KEY_NEXT_PAGE];
        if (!$nextPage) {
            return null;
        }

        foreach (static::get() as $searchedParams) {
            if ($searchedParams[self::KEY_PAGE] === $nextPage) {
                return $searchedParams[self::KEY_URL];
            }
        }

        return null;
    }
}
