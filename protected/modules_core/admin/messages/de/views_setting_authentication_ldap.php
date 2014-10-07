<?php
return array (
  'Status: Error! (Message: {message})' => 'Status: Fehler! (Meldung: {message})',
  '<strong>Authentication</strong> - LDAP' => '<strong>Authentifizierung</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'TLS/SSL wird in Produktivumgebungen favorisiert, da dies die Übertragung von Passwörtern in Klartext verhindert.',
  'Basic' => 'Grundeinstellung',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Filter der angewendet wird, sobald sich ein Benutzer anmeldet. %uid ersetzt den Benutzername während der Anmeldung. Beispiel: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP Attribute für Benutzernamen. Beispiel: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Zugriff auf Benutzer beschränken die diese Kriterien erfüllen. Beispiel: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Speichern',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Benutzer)',
  'The default base DN used for searching for accounts.' => 'Die Standard Basis DN zum Suchen der Benutzeraccounts.',
  'The default credentials password (used only with username above).' => 'Das Standard Passwort.',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Der Standard Benutzername. Einige Server benötigen den Benutzername in DN Form.',
);
