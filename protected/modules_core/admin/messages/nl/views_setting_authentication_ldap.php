<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Authenticatie</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'TLS/SSL is aan te raden in productie omgevingen om te vermijden dat wachtwoorden in leesbare tekst verzonden worden.',
  'Basic' => 'Basis',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Definieert de filter om toe te passen wanneer er ingelogd wordt. %uid vervangt de gebruikersnaam in de login actie. Voorbeeld: &quot;(sAMAccountName=%s)&quot; of &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP Attribuut voor gebruikersnaam. Voorbeeld: &quotuid&quot; of &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Beperk toegang voor gebruikers die voldoen aan deze criteria. Voorbeeld: &quot(objectClass=posixAccount)&quot; of &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Bewaar',
  'Status: Error! (Message: {message})' => 'Status: Error! (Melding: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} gebruikers)',
  'The default base DN used for searching for accounts.' => 'De standaard Base DN die gebruikt wordt voor het zoeken naar accounts.',
  'The default credentials password (used only with username above).' => 'Het standaard wachtwoord (wordt enkel gebruikt in combinatie met bovenstaande gebruikersnaam).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'De standaards gebruikersnaam. Sommige servers eisen dat dit in DN formaat staat. Dit moet in DN formaat staan als de LDAP server een DN vereist voor de bind actie en de bind actie zou mogelijk moeten zijn met simpele gebruikersnamen.',
);
