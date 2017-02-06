Backup and Restore
==================

Whether you are upgrading your installation of HumHub using the updater module or the manual, you must make sure you have your own set of backup data, one you can rely on and to which you can get back on your own terms.



Create a Backup of HumHub Data
------------------------------

* Create a full backup of the HumHub database
* Backup installation files
- /protected/modules
- /protected/config
- /uploads
- /themes/yourtheme (if you're running an own theme) 


Restore HumHub from Backup Data
--------------------------------

* Restore and import backup database
* Restore installation files
- /protected/modules
- /protected/config
- /uploads
- /themes/yourtheme (if you're running an own theme) 
* Rebuild the search index, see [Search chapter](search.md)