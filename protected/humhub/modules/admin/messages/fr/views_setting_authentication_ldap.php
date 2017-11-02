<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'L\'utilisation du protocole TLS/SSL est fortement recommandé dans les environnements de production pour prévenir de la transmission des mots de passe en clair.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Définit le filtre à appliquer, lors d\'une tentative de connexion. %s remplace le nom d\'utilisateur lors de la connexion. Exemple : &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'Attribut LDAP pour l\'adresse e-mail. Par défaut : &quotmail&quot;',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Attribut LDAP pour Nom d\'utilisateur. Exemple : &quotuid&quot; ou &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Limite l\'accès des utilisateurs qui remplissent ce critère. Par exemple : &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Enregistrer',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Veuillez spécifier l\'utilisateur LDAP utilisé pour récupérer les utilisateurs.',
  'Status: Error! (Message: {message})' => 'Statut : Erreur ! (Message : {message})',
  'Status: OK! ({userCount} Users)' => 'Statut  : OK ! ({userCount} Utilisateurs)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Statut : Attention (aucun utilisateur trouvé en utilisant ce filtre d\'utilisateur LDAP) !',
  'The default base DN used for searching for accounts.' => 'La base par défaut DN utilisé pour la recherche de comptes.',
  'The default credentials password (used only with username above).' => 'Le mot de passe par défaut des informations d\'identification (utilisé uniquement avec identifiant ci-dessus).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Le nom d\'utilisateur par défaut de l\'identification. Certains serveurs exigent cette information dans le formulaire DN. Celle-ci doit être précisée dans le formulaire DN si le serveur LDAP exige un lien vers un DN, et ce lien doit être possible avec de simples noms d\'utilisateur.',
);
