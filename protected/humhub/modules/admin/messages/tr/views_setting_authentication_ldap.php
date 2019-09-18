<?php
return array (
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'SSL / TLS açık metin olarak iletilir şifreleri önlemek için üretim ortamlarında tercih edilir.',
  'Defines the filter to apply, when login is attempted. %s replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'Giriş denendiğinde, uygulamak için filtreyi tanımlar. % uid giriş eylem adı değiştirir. Örnek: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;',
  'LDAP Attribute for E-Mail Address. Default: &quotmail&quot;' => 'E-Posta Adresi için LDAP Özniteliği. Varsayılan: &amp;quotmail"',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'Kullanıcı adı için LDAP özelliği. Örnek: &amp;quotuid" or "sAMAccountName"',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'Bu kriterleri karşılayan kullanıcılara erişim sınırlandırma. Örnek: &amp;quot(objectClass=posixAccount)" or "(&amp;(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))"',
  'Save' => 'Kaydet',
  'Specify your LDAP-backend used to fetch user accounts.' => 'Kullanıcı hesaplarını almak için kullanılan LDAP belirtin.',
  'Status: Error! (Message: {message})' => 'Durum: Hata! (Mesaj: {message})',
  'Status: OK! ({userCount} Users)' => 'Durum: TAMAM! ( {usercount} Kullanıcılar)',
  'Status: Warning! (No users found using the ldap user filter!)' => 'Durum: Uyarı! (Ldap kullanıcı filtresini kullanan hiçbir kullanıcı bulunamadı!)',
  'The default base DN used for searching for accounts.' => 'Hesaplar için yapılan aramada varsayılan temel DN kullan.',
  'The default credentials password (used only with username above).' => 'Varsayılan kimlik şifresi (Sadece kullanıcı adı ile kullanılır)',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'Varsayılan kimlik ismi. Bazı sunucular bu DN formda olmasını gerektirir.LDAP sunucusu bağlamak ve bağlayıcı basit kullanıcı adları ile mümkün olmalıdır DN gerektiriyorsa, bu DN şeklinde verilmelidir.',
);
