<?php
return array (
  'Status: Error! (Message: {message})' => 'Durum: Hata! (Mesaj: {message})',
  '<strong>Authentication</strong> - LDAP' => '<strong>LDAP</strong> - Kimlik doğrulama',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'SSL / TLS açık metin olarak iletilir şifreleri önlemek için üretim ortamlarında tercih edilir.',
  'Basic' => 'Temel',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Giriş denendiğinde, uygulamak için filtreyi tanımlar. % uid giriş eylem adı değiştirir. Örnek: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Kullanıcı adı için LDAP özelliği. Örnek: &quotuid&quot; or &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Bu kriterleri karşılayan kullanıcılara erişim sınırlandırma. Örnek: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => 'Kaydet',
  'Status: OK! ({userCount} Users)' => 'Durum: TAMAM! ( {usercount} Kullanıcılar)',
  'The default base DN used for searching for accounts.' => 'Hesaplar için yapılan aramada varsayılan temel DN kullan.',
  'The default credentials password (used only with username above).' => 'Varsayılan kimlik şifresi (Sadece kullanıcı adı ile kullanılır)',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Varsayılan kimlik ismi. Bazı sunucular bu DN formda olmasını gerektirir.LDAP sunucusu bağlamak ve bağlayıcı basit kullanıcı adları ile mümkün olmalıdır DN gerektiriyorsa, bu DN şeklinde verilmelidir.',
);
