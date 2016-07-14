<?php

set_time_limit(0);

require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';
PEAR::setErrorHandling(PEAR_ERROR_PRINT);
$pkg = new PEAR_PackageFileManager2;

$pkg->setOptions(
    array(
        'baseinstalldir' => '/',
        'packagefile' => 'package.xml',
        'packagedirectory' => realpath(dirname(__FILE__) . '/library'),
        'filelistgenerator' => 'file',
        'include' => array('*'),
        'dir_roles' => array('/' => 'php'), // hack to put *.ser files in the right place
        'ignore' => array(
            'HTMLPurifier.standalone.php',
            'HTMLPurifier.path.php',
            '*.tar.gz',
            '*.tgz',
            'standalone/'
        ),
    )
);

$pkg->setPackage('HTMLPurifier');
$pkg->setLicense('LGPL', 'http://www.gnu.org/licenses/lgpl.html');
$pkg->setSummary('Standards-compliant HTML filter');
$pkg->setDescription(
    'HTML Purifier is an HTML filter that will remove all malicious code
    (better known as XSS) with a thoroughly audited, secure yet permissive
    whitelist and will also make sure your documents are standards
    compliant.'
);

$pkg->addMaintainer('lead', 'ezyang', 'Edward Z. Yang', 'admin@htmlpurifier.org', 'yes');

$version = trim(file_get_contents('VERSION'));
$api_version = substr($version, 0, strrpos($version, '.'));

$pkg->setChannel('htmlpurifier.org');
$pkg->setAPIVersion($api_version);
$pkg->setAPIStability('stable');
$pkg->setReleaseVersion($version);
$pkg->setReleaseStability('stable');

$pkg->addRelease();

$pkg->setNotes(file_get_contents('WHATSNEW'));
$pkg->setPackageType('php');

$pkg->setPhpDep('5.0.0');
$pkg->setPearinstallerDep('1.4.3');

$pkg->generateContents();

$pkg->writePackageFile();

// vim: et sw=4 sts=4
