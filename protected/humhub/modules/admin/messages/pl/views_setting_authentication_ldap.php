<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'TLS/SSL jest silnie faworyzowany w środowiskach produkcyjnych w celu zapobiegania przesyłaniu haseł jako czysty tekst.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Definiuje filtr do zatwierdzenia w czasie próby logowania. %s zastępuje nazwę użytkownika w czasie akcji logowania. Przykład: "(sAMAccountName=%s)" lub "(uid=%s)"',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'Atrybut LDAP dla adresu email. Domyślnie: &amp;quotmail"',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Atrybuty LDAP dla nazwy użytkownika. Przykład: &amp;quotuid" lub "sAMAccountName"',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Ogranicza dostęp do użytkowników spełniających te kryteria. Przykład: &amp;quot(objectClass=posixAccount)" lub "(&amp;(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))"',
  'Save' => 'Zapisz',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Wyznacz sposób w jaki pobierasz konta użytkowników z LDAP.',
  'Status: Error! (Message: {message})' => 'Status: Błąd! (Wiadomość: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} użytkowników)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Stan: Ostrzeżenie! (Nie znaleziono użytkowników używając wybranego filtra ladp!)',
  'The default base DN used for searching for accounts.' => 'Domyślny bazowy DN używany do celów poszukiwania kont.',
  'The default credentials password (used only with username above).' => 'Domyślne hasło listy uwierzytelniającej (używane tylko z powyższą nazwą użytkownika).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Domyślna nazwa użytkownika listy uwierzytelniającej. Niektóre serwery wymagają tego aby było w formularzu DN. Musi być dane w formularzu DN jeżeli serwer LDAP wymaga wiązania i wiązanie powinno być możliwe z prostymi nazwami użytkownika.',
);
