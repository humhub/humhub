Zend Framework
==============

The Zend Framework is bundeld with Zamm.

To allow our autoloads we need to stripping als "require_once" from ZF Files.

GNU
---
     % cd path/to/ZendFramework/library
     % find . -name '*.php' -not -wholename '*/Loader/Autoloader.php' \
       -not -wholename '*/Application.php' -print0 | \
     xargs -0 sed --regexp-extended --in-place 's/(require_once)/\/\/ \1/g'


MacOSX
------
     % cd path/to/ZendFramework/library
     % find . -name '*.php' | grep -v './Loader/Autoloader.php' | \
     xargs sed -E -i~ 's/(require_once)/\/\/ \1/g'
     % find . -name '*.php~' | xargs rm -f