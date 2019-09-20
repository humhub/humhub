<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'En TLS / SSL er sterkt anbefalt produksjonsmiljøer for å forhindre at passord sendes i klar tekst.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Definerer filteret som skal brukes når pålogging er forsøkt. % s erstatter brukernavnet i påloggingen. Eksempel: "(sAMAccountName =% s)" eller "(uid =% s)"',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'LDAP egenskap for E-postadresse. Standard: &amp;quotmail"',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP egenskaper for brukernavn. Eksempel: &amp;quotuid" or "sAMAccountName"',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Begrens tilgang til brukere som oppfyller disse kriteriene. Eksempel: &amp;quot(objectClass=posixAccount)" or "(&amp;(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))"',
  'Save' => 'Lagre',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Angi LDAP-backend som brukes til å hente brukerkontoer.',
  'Status: Error! (Message: {message})' => 'Status: Feil (melding: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Brukere)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Status: Advarsel! (Ingen brukere funnet å bruke ldap-brukerfilteret!)',
  'The default base DN used for searching for accounts.' => 'Standardbasen DN brukes til å søke etter kontoer.',
  'The default credentials password (used only with username above).' => 'Standard legitimasjonspassord (bare brukt med brukernavn ovenfor).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Standard credentials brukernavn. Noen servere krever at dette er i DN-form. Dette må oppgis i DN-skjema hvis LDAP-serveren krever at en DN skal binde og bindes, bør være mulig med enkle brukernavn.',
);
