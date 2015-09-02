X-Sendfile
==========

X-Sendfile is a feature that allows us to pass file download requests directly by the webserver.
This improves the application performance.

Installation
------------

Administration -> Settings -> Files -> Enable X-Sendfile Support.

Apache Config Example
------------------------

```        
        XSendFile On
        XSendFilePath /path/to/humhub/uploads
```        


More Informations
-----------------

- Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
- Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)



Debian
------

Requires package ``libapache2-mod-xsendfile``

 
