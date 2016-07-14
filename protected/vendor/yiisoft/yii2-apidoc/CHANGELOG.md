Yii Framework 2 apidoc extension Change Log
===========================================

2.0.5 March 17, 2016
--------------------

- Bug #25: Fixed encoding of HTML tags in method definition for params passed by reference (cebe)
- Bug #37: Fixed error when extending Interfaces that are not in the current code base (cebe)
- Bug #10470: Fixed TOC links for headlines which include links (cebe)
- Enh #13: Allow templates to be specified by class name (tom--)
- Enh #13: Added a JSON template to output the class structure as a JSON file (tom--)
- Enh: Added callback `afterMarkdownProcess()` to HTML Guide renderer (cebe)
- Enh: Added `getHeadings()` method to ApiMarkdown class (cebe)
- Enh: Added css class to Info, Warning, Note and Tip blocks (cebe)
- Chg #31: Hightlight.php library is now used for code highlighing, the builtin ApiMarkdown::hightligh() function is not used anymore (cebe)


2.0.4 May 10, 2015
------------------

- Bug #3: Interface documentation did not show inheritance (cebe)
- Enh: Added ability to set pageTitle from command line (unclead)


2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- no changes in this release.


2.0.1 December 07, 2014
-----------------------

- Bug #5623: Fixed crash when a class contains a setter that has no arguments e.g. `setXyz()` (cebe)
- Bug #5899: Incorrect class listed as `definedBy` reference for properties (cebe)
- Bug: Guide and API renderer now work with relative paths/URLs (cebe)
- Enh: Guide generator now skips `images` directory if it does not exist instead of throwing an error (cebe)
- Enh: Made `--guidePrefix` option available as a command line option (cebe)


2.0.0 October 12, 2014
----------------------

- Chg: Updated cebe/markdown to 1.0.0 which includes breaking changes in its internal API (cebe)

2.0.0-rc September 27, 2014
---------------------------

- no changes in this release.


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
