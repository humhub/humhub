<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Autenticación</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'Una conexión TLS/SSL es altamente recomendada en entornos de producción para evitar que las contraseñas se transmitan en texto claro.',
  'Basic' => 'Básica',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Define el filtro que aplicar cuando se intenta iniciar sesión. %uid reemplaza el nombre de usuario en la acción de iniciar sesión. Ejemplo: quot;(sAMAccountName=%s)&quot; o &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Atributo LDAP para el nombre de usuario. Ejemplo: &quotuid&quot; o &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Limita el acceso a usuarios que cumplan estos criterios. Ejemplo: &quot(objectClass=posixAccount)&quot; o &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Guardar',
  'Status: Error! (Message: {message})' => 'Estado: ¡Error! (Mensaje: {message})',
  'Status: OK! ({userCount} Users)' => 'Estado: ¡Correcto! ({userCount} usuarios)',
  'The default base DN used for searching for accounts.' => 'El DN base por defecto usado para buscar cuentas.',
  'The default credentials password (used only with username above).' => 'La contraseña de las credenciales por defecto (usada sólo con el nombre de usuario de arriba).',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'El nombre de usuario de las credenciales por defecto. Algunos servidores requieren que este esté en formato DN. Este tiene que estar en formato DN si el servidor LDAP requiere un DN para unirse y la unión debería ser posible con nombres de usuario simples.',
);
