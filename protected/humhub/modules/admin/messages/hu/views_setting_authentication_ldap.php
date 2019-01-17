<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'A TLS/SSL-t előnyben részesítik a termelési környezetekben, hogy megakadályozzák a jelszavak egyértelmű szövegként való átvitelét.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Meghatározza az alkalmazandó szűrőt, amikor a bejelentkezés megtörténik. A %-ok  helyettesítik a felhasználónevet a bejelentkezési művelet során. Példa: "(sAMAccountName=%s)" or "(uid=%s)"',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'LDAP attribútum az email címhez. Alapértelmezés:  &amp;quotmail"',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP attribútum a felhasználónévhez. Példa:  &amp;quotuid" vagy "sAMAccountName"',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Korlátozza a hozzáférést az e kritériumoknak megfelelő felhasználókhoz. Példa: &amp;quot(objectClass=posixAccount)" or "(&amp;(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))"',
  'Save' => 'Mentés',
  'Specify your LDAP-backend used to fetch user accounts.' => 'A felhasználói fiókok lekéréséhez használt LDAP-backend meghatározása.',
  'Status: Error! (Message: {message})' => 'Állapot: Hiba! (Üzenet: {message})',
  'Status: OK! ({userCount} Users)' => 'Állapot: OK! ({userCount} felhasználó)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Állapot: Figyelmeztetés! (Az ldap felhasználói szűrő használatával nem találhatók felhasználók!)',
  'The default base DN used for searching for accounts.' => 'A fiókok kereséséhez használt alapértelmezett DN alap.',
  'The default credentials password (used only with username above).' => 'Az alapértelmezett hitelesítő jelszó (csak a fenti felhasználónévvel használható).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Az alapértelmezett hitelesítő felhasználónév. Néhány kiszolgáló megköveteli, hogy ez DN formában legyen. Ezt DN formában kell megadni, ha az LDAP kiszolgáló megköveteli a DN-t a kötéshez, és a kötés egyszerű felhasználónevekkel lehetséges.',
);
