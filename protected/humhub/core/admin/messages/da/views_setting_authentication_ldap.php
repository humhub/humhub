<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Godkendelse</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'En TLS/SSL er kraftig foretrukket i produktionsmiljøer for at modvirke adgangskoder fra at blive sendt i ren tekst.',
  'Basic' => 'Basis',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Definerer filteret for at anvende, når der er forsøgt på at logge ind. %uid udskrifter brugernavnet ved login. Eksempel: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'LDAP Attribut for Brugernavn. Eksempel: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Begræns adgangen for brugere, der opfylder disse kriterier. Eksempel: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Gem',
  'Status: Error! (Message: {message})' => 'Status: Fejl! (Besked: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Brugere)',
  'The default base DN used for searching for accounts.' => 'Standard basen som DN bruger for at søge efter kontier',
  'The default credentials password (used only with username above).' => 'Standard legitimationsoplysning i adgangskode (bruges kun med brugernavn ovenfor).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Standardindstillingerne legitimationsoplysninger brugernavn . Nogle servere kræver , at dette skal være i DN form. Dette skal angives i DN formularen, hvis LDAP-serveren kræver en DN at forbinde til og forbindendelse bør være muligt ved simple brugernavne .',
);
