<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong> احراز هویت </strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'SSL/TSL به شدت در تولید محیط‌ها مورد توجه است تا از جابجایی گذرواژه‌ها در متن واضح جلوگیری شود.',
  'Basic' => 'پایه‌ای',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => ' در زمان ورود فیلتر را برای اجرا تعریف می‌کند. %uid نام کاربری را در عمل ورود جایگزین می‌کند. مثال: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'صفت LDAP برای نام کاربری. مثال: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'دسترسی کاربرانی را که دارای این شرط هستند محدود کن. مثال: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'ذخیره',
  'Status: Error! (Message: {message})' => 'وضعیت: خطا! (پیغام: {message} )',
  'Status: OK! ({userCount} Users)' => 'وضعیت: بدون مشکل! ({userCount} کاربر)',
  'The default base DN used for searching for accounts.' => 'DN پیش‌فرض استفاده‌شده برای جستجوی حساب‌های کاربری',
  'The default credentials password (used only with username above).' => 'گذرواژه‌ی پیش‌فرض اسناد(استفاده‌شده تنها با نام کاربری بالا)  ',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'نام کاربری پیش‌فرض اسناد. برخی سرور ها این را با فرم DN نیاز دارند. در صورتی که سرور LDAP نیاز به bind کردن داشته‌باشد باید با فرم DN داده‌شود تا bind کردن برای نام‌های کاربری ساده امکان‌پذیر باشد.',
);
