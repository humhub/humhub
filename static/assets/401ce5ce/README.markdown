# timeago: a jQuery plugin

Timeago is a jQuery plugin that makes it easy to support automatically updating
fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago") from ISO 8601
formatted dates and times embedded in your HTML (à la microformats).

---

**How You Can Help**

[![Square Cash](http://img.shields.io/badge/square%20cash-$rmm5t-brightgreen.svg)][square]
[![Gratipay](http://img.shields.io/gratipay/rmm5t.svg)][gratipay]
[![Book a Codementor session](http://img.shields.io/badge/codementor-book%20a%20session-orange.svg)][codementor]

If you like this project, [buy me a coffee][square], [donate via Gratipay][gratipay], or [book a session with me on Codementor][codementor].

Bitcoin: `1rmm5tv6f997JK5bLcGbRCZyVjZUPkQ2m`

[square]: https://cash.me/$rmm5t "Donate to rmm5t for open source!"
[gratipay]: https://gratipay.com/rmm5t/ "Donate to rmm5t for open source!"
[bitcoin]: bitcoin:1rmm5tv6f997JK5bLcGbRCZyVjZUPkQ2m?amount=0.01&label=Coffee%20to%20rmm5t%20for%20Open%20Source "Buy rmm5t a coffee for open source!"
[codementor]: https://www.codementor.io/rmm5t?utm_campaign=profile&utm_source=button-rmm5t&utm_medium=shields "Book a session with rmm5t on Codementor!"

## Usage

First, load jQuery and the plugin:

```html
<script src="jquery.min.js" type="text/javascript"></script>
<script src="jquery.timeago.js" type="text/javascript"></script>
```

Now, let's attach it to your timestamps on DOM ready - put this in the head
section:

```html
<script type="text/javascript">
   jQuery(document).ready(function() {
     $("abbr.timeago").timeago();
   });
</script>
```

This will turn all abbr elements with a class of timeago and an ISO 8601
timestamp in the title (conforming to the
[datetime design pattern microformat](http://microformats.org/wiki/datetime-design-pattern)):

```html
<abbr class="timeago" title="2011-12-17T09:24:17Z">December 17, 2011</abbr>
```

into something like this:

```html
<abbr class="timeago" title="December 17, 2011">about 1 day ago</abbr>
```

HTML5 `<time>` elements are also supported:

```html
<time class="timeago" datetime="2011-12-17T09:24:17Z">December 17, 2011</time>
```

As time passes, the timestamps will automatically update.

**For more usage and examples**: [http://timeago.yarp.com/](http://timeago.yarp.com/)

**For different language configurations**: visit the [`locales`](https://github.com/rmm5t/jquery-timeago/tree/master/locales) directory.

## Changes

| Version | Notes                                                                           |
|---------|---------------------------------------------------------------------------------|
|   1.4.x | ([compare][compare-1.4]) Added allowPast setting                                |
|   1.3.x | ([compare][compare-1.3]) Added updateFromDOM function; bug fixes; bower support |
|   1.2.x | ([compare][compare-1.2]) Added cutoff setting                                   |
|   1.1.x | ([compare][compare-1.1]) Added update function                                  |
|   1.0.x | ([compare][compare-1.0]) locale updates; bug fixes; AMD wrapper                 |
|  0.11.x | ([compare][compare-0.11]) natural rounding; locale updates;                     |
|  0.10.x | ([compare][compare-0.10]) locale updates                                        |
|   0.9.x | ([compare][compare-0.9]) microsecond support; bug fixes                         |
|   0.8.x | ([compare][compare-0.8]) `<time>` element support; bug fixes                    |
|   0.7.x | ([compare][compare-0.7]) locale function overrides; unit tests                  |
|     ... | ...                                                                             |

[compare-1.4]: https://github.com/rmm5t/jquery-timeago/compare/v1.3.2...v1.4.3
[compare-1.3]: https://github.com/rmm5t/jquery-timeago/compare/v1.2.0...v1.3.2
[compare-1.2]: https://github.com/rmm5t/jquery-timeago/compare/v1.1.0...v1.2.0
[compare-1.1]: https://github.com/rmm5t/jquery-timeago/compare/v1.0.2...v1.1.0
[compare-1.0]: https://github.com/rmm5t/jquery-timeago/compare/v0.11.4...v1.0.2
[compare-0.11]: https://github.com/rmm5t/jquery-timeago/compare/v0.10.1...v0.11.4
[compare-0.10]: https://github.com/rmm5t/jquery-timeago/compare/v0.9.3...v0.10.1
[compare-0.9]: https://github.com/rmm5t/jquery-timeago/compare/v0.8.2...v0.9.3
[compare-0.8]: https://github.com/rmm5t/jquery-timeago/compare/v0.7.2...v0.8.2
[compare-0.7]: https://github.com/rmm5t/jquery-timeago/compare/v0.6.2...v0.7.2

## Author

[Ryan McGeary](http://ryan.mcgeary.org) ([@rmm5t](http://twitter.com/rmm5t))

## Other

[MIT License](http://www.opensource.org/licenses/mit-license.php)

Copyright (c) 2008-2015, Ryan McGeary (ryan -[at]- mcgeary [*dot*] org)
