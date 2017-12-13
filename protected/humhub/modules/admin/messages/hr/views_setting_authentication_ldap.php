<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'TLS / SSL je snažno favoriziran u produkcijskom okruženju kako bi se spriječio prijenos lozinki u jasnom tekstu.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Određuje filtar koji se primjenjuje kod pokušajaj prijave. %s zamjenjuje korisničko ime u akciji za prijavu. Primjer: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'LDAP atribut za e-mail adresu. Zadano: &quotmail&quot;',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'DAP atribut za korisničko ime. Primjer: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Ograničite pristup korisnicima koji ispunjavaju ove kriterije. Primjer: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Spremi',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Navedite svoj LDAP-backend koji se koristi za dohvaćanje korisničkih računa.',
  'Status: Error! (Message: {message})' => 'Status: Pogreška! (Poruka: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Korisnici)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Status: Upozorenje! (Nisu pronađeni korisnici koji koriste filtar korisnika ldap!)',
  'The default base DN used for searching for accounts.' => 'Zadana baza DN koja se koristi za traženje računa.',
  'The default credentials password (used only with username above).' => 'Zadana lozinka vjerodajnica (upotrijebljena samo iznad gore navedenog korisničkog imena).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Zadano korisničko ime vjerodajnica. Neki poslužitelji zahtijevaju da to bude u DN formi. To se mora dati u DN obliku ako LDAP poslužitelj zahtijeva DN vezanje i vezivanje bi trebalo biti moguće s jednostavnim korisničkim imenom.',
);
