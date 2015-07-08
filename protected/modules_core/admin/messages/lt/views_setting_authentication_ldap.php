<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Atpažinimas</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'TLS/SSL ',
  'Basic' => 'Pagrindinis',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Nurodo filtrą, kai bandoma prisijungti. %uid pakeičia vartotojo vardą į prisijungimo veiksmą. Pavyzdys &quot;(sAMAccountName=%s)&quot; ar &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP savybė vartotojui. Pavyzdys: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Apriboti prieigą vartotojams, atitinkantiems šį kriterijų. Pavyzdys: &quot(objectClass=posixAccount)&quot; ar &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Išsaugoti',
  'Status: Error! (Message: {message})' => 'Statusas: Klaida! (žinutė: {message})',
  'Status: OK! ({userCount} Users)' => 'Statusas: Gerai! ({userCount} Vartotojai)',
  'The default base DN used for searching for accounts.' => 'Numatytoji bazė DN, naudojama paskyrų paieškai.',
  'The default credentials password (used only with username above).' => 'Numatytasis įgaliojimų slaptažodis (naudojamas tik su vartotojo vardu)',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Numatytasis įgaliojimų vardas. Kai kurie serveriai reikalauja, kad tai būtų DN formoje. Tai turi būti DN formoje, jei LDAP serveris reikalauja DN įpareigoti ir įpareigojimas turi būti galimas su paprastais vartotojų vardais.',
);
