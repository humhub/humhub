<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'A TLS/SSL é fortemente preferível em ambientes de produção para evitar que senhas sejam transmitidas em texto puro.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Define o filtro a ser aplicado, quando o login é feito. %s substitui o nome de usuário no processo de login. Exemplo: &quot;(sAMAccountName=%s)&quot; ou &quot;(uid=%s)&quot;',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'Atributo LDAP para e-mail. Padrão: &quotmail&quot;',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Atributo LDAP para usuário. Exemplo:  &quotuid&quot; ou &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Limitar o acesso aos utilizadores que cumpram esse critério. Exemplo: &quot(objectClass=posixAccount)&quot; ou &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Salvar',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Especifique seu usuário LDAP para buscar as contas de usuários.',
  'Status: Error! (Message: {message})' => 'Status: Erro! (Mensagem: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Usuários)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Status: Atenção! (Nenhum usuário encontrado usando o filtro de usuário LDAP!)',
  'The default base DN used for searching for accounts.' => 'A base padrão DN utilizado para a busca de contas.',
  'The default credentials password (used only with username above).' => 'A senha padrão (usada apenas com nome de usuário acima).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'O usuário padrão. Alguns servidores exigem que este seja em forma DN. Isso deve ser dada de forma DN se o servidor LDAP requer uma DN para vincular e deve ser possível com nomes de usuários simples.',
);
