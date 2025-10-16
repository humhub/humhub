<?php

/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local common (Console and Web) environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */

// https://community.humhub.com/s/installation-and-setup/wiki/80/Configuration+Examples


return [
    'components' => [
        //        'request' => [
        //            'enableCsrfValidation' => false, // Disable CSRF validation
        //        ],
        //        'dbYesWiki' => [
        //            'class' => \yii\db\Connection::class,
        //            'dsn' => 'mysql:host=localhost;dbname=udn_yeswiki',
        //            'username' => 'root',
        //            'password' => 'mdqsokR43534&=XSgFPDS)',
        //            'charset' => 'utf8mb4',
        //        ],
        //        'redis' => [
        //            'class' => 'yii\redis\Connection',
        //            'hostname' => 'localhost',
        //            'port' => 6379,
        //            'database' => 0, // Change if already used on the same server
        //        ],
        //        'queue' => [
        //            'class' => 'humhub\modules\queue\driver\Redis', // Allow instant execution of queued jobs
        //        ],
        //        'cache' => [
        //            'class' => 'yii\redis\Cache',
        //        ],
        //        'live' => [ // https://docs.humhub.org/docs/admin/push-updates/
        //            'driver' => [
        //                'class' => \humhub\modules\live\driver\Push::class,
        //                'pushServiceUrl' => 'http://localhost:3000/',
        //                'jwtKey' => 'humhub_dev_jwt_key',
        //            ],
        //        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            //            'targets' => [
            //                \yii\log\EmailTarget::class => [
            //                    'class' => \yii\log\EmailTarget::class,
            //                    'levels' => ['error'],
            //                    'except' => ['yii\web\HttpException:400', 'yii\web\HttpException:401', 'yii\web\HttpException:403', 'yii\web\HttpException:404', 'yii\web\HttpException:405', 'yii\web\HttpException:416', 'yii\web\HttpException:500'],
            //                    'message' => [
            //                        'from' => ['contact@cuzy.app'],
            //                        'to' => ['marc.farre@cuzy.app'],
            //                        'subject' => 'HumHub Demo CUZY - Log - Event',
            //                    ],
            //                ],
            //            ],
        ],
        // 'assetManager' => [
        //     'baseUrl' => $baseUrl.'/assets',
        // ],
        //        'authClientCollection' => [
        //            'clients' => [
        //                'JWT' => [
        //                    'class' => 'humhub\modules\colibris\authclient\JWT',
        //                    'authUrl' => 'https://monprofil.colibris-universite.org/cas/login?goto=humhub_dev_localhost',
        //                    'publicKey' => 'iVuG6kWC-cZS3sARvx_V0jnzrThh3fpe-bgKo0MQuCsFIgzbwsdkDrz0jUJUD2A9ptuQp_L6mRIKQNTASaD3Q-vuOrONeGK4sNZksmcXC54Y8iYHoXDGCRaimnFm8bE-s3vbWcmYaVRKdKgDkS2aIsZochZw_BmJ5Ln0BGcGvLy8hBxkLIJgSaXwlmcJODCGIdA9ofp5op02JakKGdMI8tdmIOcMfl1JsT7VjCCubEoF90dejaVwJjCOyB73SGgvlKyWppRpE131R0vipLOXODA-qkoem6ke20w3Zd8yj9D90nvHbdlMH1zXiYmZm7ZpUGK-BNgLK-o6YheQzGG3_w',
        //                    // Title of the button (if autoLogin is disabled)
        //                    'title' => 'Mon compte Colibris',
        //                    // Automatic login
        //                    'autoLogin' => true,
        //                    // User ID attribute name
        //                    'idAttribute' => 'jti',
        //                ],
        //            ],
        //        ],
    ],
    'params' => [
        //        'humhub' => [
        //            'apiUrl' => 'https://api.humhub.dev', // Route for the Marketplace API, e.g., for modules uploaded to https://partner.humhub.dev/
        //        ],
        //        'allowedLanguages' => ['fr'],
        //        'availableLanguages' => [
        //            'fr' => 'Français',
        //        ],
        //        'dailyCronExecutionTime' => '17:30',
        //        'hidePoweredBy' => true, // Hide the Powered By HumHub
        'moduleAutoloadPaths' => [
            '@app/modules_paid',
            '@app/modules_pro',
            '@app/modules_no_marketplace',
            '@app/modules_cuzy',
            '@app/modules_cuzy_archived',
            '@app/modules_cuzy_partners',
            '@app/modules_other',
        ],
        'defaultPermissions' => [ // https://docs.humhub.org/docs/admin/permissions/#default-permissions
            \humhub\modules\space\permissions\CreatePrivateSpace::class => [
                '*' => \humhub\libs\BasePermission::STATE_DENY,
            ],
            \humhub\modules\space\permissions\CreatePublicSpace::class => [
                '*' => \humhub\libs\BasePermission::STATE_DENY,
            ],
            \humhub\modules\topic\permissions\AddTopic::class => [
                '*' => \humhub\libs\BasePermission::STATE_DENY,
            ],
            \humhub\modules\topic\permissions\ManageTopics::class => [
                '*' => \humhub\libs\BasePermission::STATE_DENY,
            ],
            //            \humhub\modules\user\permissions\PeopleAccess::class => [
            //                '*' => \humhub\libs\BasePermission::STATE_DENY,
            //            ],
        ],
    ],
    'modules' => [
        //        'notification' => [
        //            'disableNewContentNotificationSpacesToNonMemberFollowing' => true,
        //        ],
        //        'ecommerce' => [
        //            'enableInvoiceStateField' => false, // to disable the State field in the invoice
        //            'enableInvoiceVatField' => false, // to disable the VAT field in the invoice
        //        ],

        //        'saas' => [
        //            'demoMode' => true,
        //        ],
        //                'dashboard' => [
        //                    'hideActivitySidebarWidget' => true,
        //                ],
        'admin' => [
            //            'allowUserImpersonate' => false,
            //            'enableManageAllContentPermission' => true, // Since 1.17
        ],
        //        'stories' => [
        //            'storiesBarRefreshTimespan' => 3,
        //        ],
        //        'transition' => [
        //            'spaceAdminsGroupId' => 59, // The group ID for space admins
        //        ],
        'content' => [
            //            'adminCanViewAllContent' => true, // Deprecated since 1.17, use enableManageAllContentPermission (admin module)
            //            'adminCanEditAllContent' => false, // Deprecated since 1.17, use enableManageAllContentPermission (admin module)
        ],
        'activity' => [
            //            'enableMailSummaries' => false, // Disable mail summaries
            'weeklySummaryDay' => 5, // 0 (for Sunday) through 6 (for Saturday)
        ],
        'translation' => [
            'googleApiKey' => 'AIzaSyANA8eTWt6cvMQRxSHlU3vu2gJrVXpxp3g', // Funkycram
            //            'googleApiKey' => 'AIzaSyBE1PpfnLk5v74JCyzRagE38qhKO4eiqmE', // CUZY
        ],
        'user' => [
            //            'sendInviteMailsInGlobalLanguage' => false,
            //            'profileDisableStream' => true,
            // 'disableFollow' => true, // Disable follow feature
            // 'includeAllUserContentsOnProfile' => false,
            'includeEmailInSearch' => false,
            'validUsernameRegexp' => '/^[\p{L}\d_\-\.]+$/iu', // @ is removed from the default regex
            //            'passwordStrength' => [
            //                '/^.{8,}$/' => 'Password needs to be at least 8 characters long.',
            //                '/^(.*?[A-Z]){1,}.*$/' => 'Password has to contain one uppercase letter.',
            //                '/^(.*?[a-z]){1,}.*$/' => 'Password has to contain one lower case letter.',
            //                '/^(.*?[\W]){1,}.*$/' => 'Password has to contain one special case letter.',
            //                '/^(.*?[0-9]){1,}.*$/' => 'Password has to contain one digit.',
            //            ],
            //            'passwordHint' => 'Minimum 8 characters, at least one uppercase letter, one lowercase letter, on special case letter and one digit.',
            //            'passwordStrength' => [
            //                '/^.{8,}$/' => 'Le mot de passe doit comporter au moins 8 caractères.',
            //                '/^(.*?[A-Z]){1,}.*$/' => 'Le mot de passe doit comporter au moins une lettre en majuscule.',
            //                '/^(.*?[a-z]){1,}.*$/' => 'Le mot de passe doit comporter au moins une lettre en minuscule.',
            //                '/^(.*?[\W]){1,}.*$/' => 'Le mot de passe doit comporter au moins un caractère spécial.',
            //                '/^(.*?[0-9]){1,}.*$/' => 'Le mot de passe doit comporter au moins un chiffre.',
            //            ],
            //            'displayNameCallback' => function (\humhub\modules\user\models\User $user) {
            //                /** @var \humhub\modules\user\models\User $user */
            //                return $user->profile->firstname . ' ' . $user->profile->lastname . (!empty($user->profile->test_registration) ? ' - ' . \humhub\libs\StringHelper::truncate($user->profile->test_registration, 30) : '');
            //            },
            //            'displayNameSubCallback' => function ($user) {
            //                return $user->profile->title ?? '';
            //            },
            //            'displayNameSubCallback' => function ($user) {
            //                /** @var \humhub\modules\user\models\User $user */
            //                $groupNames = array_map(function ($group) {
            //                    return $group->name;
            //                }, $user->getGroups()->where(['show_at_directory' => true, 'is_default_group' => false])->orderBy(['sort_order' => SORT_ASC])->all());
            //                return implode(', ', $groupNames);
            //            },
            // On Humhub 1.16:
            //            'showLoginForm' => false,
            //            'showRegistrationForm' => false,
            //            'allowUserRegistrationFromAuthClientIds' => ['Keycloak'],
        ],
        'file' => [
            'imageMaxResolution' => '1920x1920',
            'imageJpegQuality' => 60,
            'imagePngCompressionLevel' => 8,
            'imageWebpQuality' => 60,
            //            'additionalMimeTypes' => [
            //                'doc' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Added for some .doc files that could not be uploaded
            //                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            //                'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //                'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            //            ],
            //            'defaultFileHandlers' => [], // Remove Upload Audio / Image / Video dropdown menu
            'defaultFileHandlers' => [ // Customize Upload Audio / Image / Video dropdown menu
                \humhub\modules\file\handler\UploadAudioFileHandler::class,
                \humhub\modules\file\handler\UploadImageFileHandler::class,
                \humhub\modules\file\handler\UploadVideoFileHandler::class,
            ],
        ],
        //        'external-websites' => [
        //            'jwtKey' => 'f_se4kxzpuI3tv2ODBpTLddsAm_0TYmdJDZPWgkIxbaLP2HWD64mDNDj5nxvV7vElWPWrMtEYDXOsp5DGwUG1P',
        //            'registerAssetsIfHumhubIsEmbedded' => true,
        //        ],
        //        'group-advanced' => [
        //            'useGroupIconForLinkedSpacesDefaultIcon' => true,
        //            'forceFillRequiredProfileFields' => true,
        //        ],
        //        'comment' => [
        //            'commentsBlockLoadSize' => 100,
        //            'commentsPreviewMax' => 100,
        //        ],
        'space' => [
            //            'hideSpacesPage' => true,
            //            'hideAboutPage' => true,
            //            'disableFollow' => true, // Disable follow feature
        ],
        //        'auth-keycloak' => [
        //            'apiVerifySsl' => false,
        //        ],
        //        'yeswiki' => [
        //            'yesWikiProfileFormId' => 1000,
        //            'profileFieldYesWikiLatLng' => true,
        //            'extraProfileFieldsHumhub2YesWiki' => [
        //                'firstname' => 'bf_nom',
        //                'lastname' => 'bf_prenom',
        //                'zip' => 'bf_code_postal',
        //                'city' => 'bf_ville',
        //                'country' => 'bf_pays',
        //                'mobile' => 'bf_tel',
        //                'url' => 'bf_siteperso',
        //                'tranche_age' => 'listeListeAge',
        //                'intention' => 'bf_intention',
        //                'offrir' => 'bf_offrir',
        //                'type_organisation' => 'listeListeTypeOrga',
        //                'deja_dans_jardin' => 'listeListeOuinonbf_jardinouinon',
        //                'type_jardin_recherche' => 'checkboxListeRejoindreJardin',
        //                'dispo_pour_echanger' => 'checkboxListeListedisponibilitespourechanger',
        //                'niveau_pratique_gouvernance' => 'listeListeNiveauDePratique',
        //                'domaines_interets' => 'bf_tags',
        //                'commentaires_jardinier' => 'bf_commentaires',
        //                'activite_socio_prof' => 'listeListeListeactivitesocioprof',
        //                'competences' => 'checkboxListeCompetences',
        //                'competences_other_selection' => 'bf_detailcompetence',
        //                'commentaires_complements' => 'bf_commseventuels',
        //            ],
        //            'extraProfileValHumhub2YesWiki' => [
        //                'type_jardin_recherche' => [
        //                    'Géographique' => 1,
        //                    'Thématique' => 2,
        //                    'Interne à mon organisation' => 3,
        //                    'Lié à une formation (AdN / Mooc / ...)' => 4,
        //                    'Je ne sais pas / On verra plus tard' => 5,
        //                ],
        //                'competences' => [
        //                    'Communication' => 1,
        //                    'Pédagogie' => 2,
        //                    'Relations publiques / Partenariat' => 3,
        //                    'Animation de communautés' => 4,
        //                    'Outils numériques' => 5,
        //                    'Rédaction / Correction' => 6,
        //                    'Recherche de financements' => 7,
        //                    'Audiovisuel (son/vidéo)' => 8,
        //                    'Infographie / Illustrations' => 9,
        //                    'Juridique' => 10,
        //                    'Organisation d’évènements' => 11,
        //                    'Facilitation / Animation' => 12,
        //                    'other' => 13,
        //                ],
        //                'dispo_pour_echanger' => [
        //                    'en semaine' => 1,
        //                    'en journée' => 2,
        //                    'en soirée' => 3,
        //                    'en week-end' => 4,
        //                ],
        //            ],
        //        ],
        'stream' => [
            //            'streamSuppressLimit' => 1000, // int number of contents from which "Show more" appears in the stream
            //            'streamExcludes' => [
            //                \humhub\modules\cfiles\models\File::class,
            //                \humhub\modules\gallery\models\Media::class,
            //            ],
            //            'streamSuppressQueryIgnore' => [ // Stream Suppressing
            //                'humhub\modules\cfiles\models\File',
            //                'humhub\modules\gallery\models\Media',
            //                'humhub\modules\calendar\models\CalendarEntry',
            //                'humhub\modules\wiki\models\WikiPage',
            //
            //            ]
        ],
        //        'beeswarm' => [
        //            'surveyId' => 11, // 1 for beeswarm.uk
        //            'surveyAnswerPostcodeFieldId' => 22, // ? for beeswarm.uk
        //            'guestUserId' => 69, // 3 for beeswarm.uk
        //            'fallbackSpace' => 15, // 2914 for beeswarm.uk
        //        ],
        'clean-theme' => [
            'collapsibleLeftNavigation' => true,
        ],
        'seve-animateurs' => [
            'animateursSeveGroupId' => 1, // The group ID for SEVE animateurs
        ],
    ],
    'container' => [
        'definitions' => [
            //            'humhub\modules\file\validators\FileValidator' => [
            //                'checkExtensionByMimeType' => true
            //            ],
            //            \humhub\modules\cfiles\models\File::class => [
            //                'silentContentCreation' => true,
            //            ],
            //            \humhub\modules\gallery\models\Media::class => [
            //                'silentContentCreation' => true,
            //            ],
            //            \humhub\modules\space\widgets\Menu::class => [
            //                'on run' => function ($e) {
            //                    /** @var \humhub\modules\space\widgets\Menu $menu */
            //                    $menu = $e->sender;
            //
            //                    $menuList = [
            //                        "/wiki/Infos+zu+Orca" => "500",
            //                        "/home" => "1000",
            //                        "/wiki/page" => "1100",
            //                        "/gallery/list" => "1300",
            //                        "/cfiles/browse" => "2000",
            //                        "/calender/view/index" => "3000",
            //                    ];
            //
            //                    foreach ($menuList as $page => $sortOrder) {
            //                        $menuEntry = $menu->getEntryByUrl($menu->space->createUrl($page));
            //                        if ($menuEntry !== null) {
            //                            $menuEntry->setSortOrder($sortOrder);
            //                        }
            //                    }
            //                }
            //            ]
        ],
    ],
];
