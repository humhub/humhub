X-Sendfile
==========

X-Sendfile is a feature that allows us to pass file download requests directly to the webserver.
This improves the application performance.

Installation
------------

Administration -> Settings -> Files -> Enable X-Sendfile Support.

WebServer Config Example
------------------------

        # enable xsendfile
        XSendFile On

        # enable sending files from parent dirs
        XSendFileAllowAbove On


Debian
------

Requires package ``libapache2-mod-xsendfile``

 