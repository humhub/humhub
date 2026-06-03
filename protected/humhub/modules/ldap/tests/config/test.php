<?php

/**
 * Base test configuration for the LDAP module.
 *
 * 'fixtures' lists the fixture groups to load before each test run.
 * The 'default' group loads the standard HumHub fixture set (users, spaces, etc.)
 * which is required for sync tests that create or look up HumHub users.
 */
return [
    'fixtures' => [
        'default',
    ],
];
