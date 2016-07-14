Advanced Configuration
======================

You can overwrite the default HumHub / Yii configuration in folder `/protected/config`.  

## File Overview

- **common.php**  - Configuration used in Console & Web Application
- **web.php** - Configuration used in Web Application only
- **console.log** - Configuration used in Console Application only
- **dynamic.php** - Dynamic generated configuration - do not edit manually!

## Loading Order

### Web Application

1. humhub/config/common.php
2. humhub/config/web.php
3. config/dynamic.php
4. **config/common.php**
5. **config/web.php**


### Console Application

1. humhub/config/common.php
2. humhub/config/console.php
3. config/dynamic.php
4. **config/common.php**
5. **config/console.php**

