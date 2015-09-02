<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Authentication</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'L\'utilisation du protocole TLS/SSL est fortement recommandé dans les environnements de production pour prévenir de la transmission des mots de passe en clair.',
  'Basic' => 'Basique',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => '',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Attribut LDAP pour Nom d\'utilisateur. Exemple: &quotuid&quot; ou &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => '',
  'Save' => 'Enregistrer',
  'Status: Error! (Message: {message})' => 'Status : Erreur! (Message: {message})',
  'Status: OK! ({userCount} Users)' => 'Status : OK! ({userCount} Utilisateurs)',
  'The default base DN used for searching for accounts.' => 'La base par défaut DN utilisé pour la recherche de comptes.',
  'The default credentials password (used only with username above).' => 'Le mot de passe des informations d\'identification par défaut (utilisé uniquement avec identifiant ci-dessus).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => '',
);
