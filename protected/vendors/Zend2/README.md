### Welcome to the *Zend Framework 2.2* Release!

Master: [![Build Status](https://secure.travis-ci.org/zendframework/zf2.png?branch=master)](http://travis-ci.org/zendframework/zf2)
Develop: [![Build Status](https://secure.travis-ci.org/zendframework/zf2.png?branch=develop)](http://travis-ci.org/zendframework/zf2)

## RELEASE INFORMATION

*Zend Framework 2.2.5*

This is the fifth maintenance release for the 2.2 series.

31 Oct 2013

### SECURITY UPDATES IN 2.2.5

An issue with `Zend\Http\PhpEnvironment\RemoteAddress` was reported in
[#5374](https://github.com/zendframework/zf2/pull/5374). Essentially, the class
was not checking if `$_SERVER['REMOTE_ADDR']` was one of the trusted proxies
configured, and as a result, `getIpAddressFromProxy()` could return an untrusted
IP address. 

The class was updated to check if `$_SERVER['REMOTE_ADDR']` is in the list of
trusted proxies, and, if so, will return that value immediately before
consulting the values in the `X-Forwarded-For` header.

If you use the `RemoteAddr` `Zend\Session` validator, and are configuring
trusted proxies, we recommend updating to 2.2.5 or later immediately.

### UPDATES IN 2.2.5

- [#5343](https://github.com/zendframework/zf2/pull/5343) removed the
  DateTimeFormatter filter from DateTime form elements. This was done
  due to the fact that it led to unexpected behavior when non-date inputs were
  provided. However, since the DateTime element already incorporates a
  DateValidator that accepts a date format, validation can still work as
  expected.

Please see [CHANGELOG.md](CHANGELOG.md).

### SYSTEM REQUIREMENTS

Zend Framework 2 requires PHP 5.3.3 or later; we recommend using the
latest PHP version whenever possible.

### INSTALLATION

Please see [INSTALL.md](INSTALL.md).

### CONTRIBUTING

If you wish to contribute to Zend Framework, please read both the
[CONTRIBUTING.md](CONTRIBUTING.md) and [README-GIT.md](README-GIT.md) file.

### QUESTIONS AND FEEDBACK

Online documentation can be found at http://framework.zend.com/manual.
Questions that are not addressed in the manual should be directed to the
appropriate mailing list:

http://framework.zend.com/archives/subscribe/

If you find code in this release behaving in an unexpected manner or
contrary to its documented behavior, please create an issue in our GitHub
issue tracker:

https://github.com/zendframework/zf2/issues

If you would like to be notified of new releases, you can subscribe to
the fw-announce mailing list by sending a blank message to
<fw-announce-subscribe@lists.zend.com>.

### LICENSE

The files in this archive are released under the Zend Framework license.
You can find a copy of this license in [LICENSE.txt](LICENSE.txt).

### ACKNOWLEDGEMENTS

The Zend Framework team would like to thank all the [contributors](https://github.com/zendframework/zf2/contributors) to the Zend
Framework project, our corporate sponsor, and you, the Zend Framework user.
Please visit us sometime soon at http://framework.zend.com.
