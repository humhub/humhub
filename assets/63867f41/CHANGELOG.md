yii-pjax Change Log
===================

2.0.6 Mar 4, 2015
-----------------
- Bug #15: Fixed duplication of `_pjax` GET variable (Alex-Code)
- Bug #21: Fixed non-persistence of `cache` option after backward navigation (nkovacs)
- Bug #23: Fixed loading of scripts in pjax containers (nkovacs, silverfire)
- Bug #37: Added `X-Ie-Redirect-Compatibility` header for IE. Fixes error on 302 redirect without `Location` header (silverfire)
- Enh #25: Blur the focused element if it's inside Pjax container (GeorgeGardiner)
- Enh #27: Added `pushRedirect`, `replaceRedirectOptions` options (beowulfenator)
- Chg: JavaScripts load through PJAX will be processed by `jQuery.ajaxPrefiler` when it's configured (silverfire)
- New: Added `skipOuterContainers` option (silverfire)

2.0.3 Mar 7, 2015
-----------------
- Chg: Merged changes from upstream (samdark)

2.0.2 Dec 4, 2014
-----------------
- Chg #12: Merged changes from upstream (samdark)

2.0.1 Oct 10, 2014
------------------
- Bug #9: Fixed missing history option in default settings (tonydspaniard)
- New #11: add new option "cache" (macklay)


2.0.0 Mar 20, 2014
------------------
- Bug: Fixed avoid duplicates of _pjax parameter (tof06)
- Bug: Fixed Pjax/GridView and back button (klevron, tof06, tonydspaniard)
