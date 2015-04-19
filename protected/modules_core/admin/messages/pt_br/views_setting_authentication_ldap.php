<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Autenticação</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'A TLS/SSL é fortemente favorecido em ambientes de produção para evitar senhas de ser transmitida em texto puro.',
  'Basic' => 'Básico',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Define o filtro a ser aplicado, quando o login é feito. %uid substitui o nome de usuário no processo de login. Exemplo: &quot;sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Atributo LDAP para usuário. Exemplo:  &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Limitar o acesso aos utilizadores que cumpram esse critério. Exemplo: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Salvar',
  'Status: Error! (Message: {message})' => 'Status: Erro! (Mensagem: {message})',
  'Status: OK! ({userCount} Users)' => 'Status: OK! ({userCount} Usuários)',
  'The default base DN used for searching for accounts.' => 'A base padrão DN utilizado para a busca de contas.',
  'The default credentials password (used only with username above).' => 'A senha padrão (usada apenas com nome de usuário acima).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'O Username  padrão. Alguns servidores exigem que este seja em forma DN. Isso deve ser dada de forma DN se o servidor LDAP requer uma DN para ligar e deve ser possível com usernames simples.',
);
