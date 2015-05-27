<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Ověřování</strong> – LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'V produkčním prostředí je silně doporučeno používat TLS/SSL šifrování, aby hesla nebyla přenášena jako čistý text.',
  'Basic' => 'Základní',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Nastavení filtrů, které se mají aplikovat při pokusu o přihlášení. %uid nahrazuje uživatelské jméno. Např.: &quot;(sAMAccountName=%s)&quot; nebo &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP atribut pro uživatelské jméno. Např. &quotuid&quot; nebo &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Omezit přístup na základě daných kritérií. Např.: &quot(objectClass=posixAccount)&quot; nebo &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Uložit',
  'Status: Error! (Message: {message})' => 'Stav: chyba! (Odpověď: {message})',
  'Status: OK! ({userCount} Users)' => 'Stav: OK! ({userCount} uživatelů)',
  'The default base DN used for searching for accounts.' => '',
  'The default credentials password (used only with username above).' => '',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => '',
);
