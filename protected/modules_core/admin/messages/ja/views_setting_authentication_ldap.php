<?php
return array (
  '<strong>Authentication</strong> - LDAP' => '<strong>Authentication</strong> - LDAP',
  'A TLS/SSL is strongly favored in production environments to prevent passwords from be transmitted in clear text.' => 'パスワードは平文で送信されることから情報漏洩を防止するために、運用環境ではTLS / SSL化する事が強く推奨されます。',
  'Basic' => '基本',
  'Defines the filter to apply, when login is attempted. %uid replaces the username in the login action. Example: &quot;(sAMAccountName=%s)&quot; or &quot;(uid=%s)&quot;' => 'ログインした時に適用するフィルタを定義します。 %uid はログインアクションにユーザ名を置き換えます。例：&quot;(sAMAccountName=%s)&quot; または &quot;(uid=%s)&quot;',
  'LDAP' => 'LDAP',
  'LDAP Attribute for Username. Example: &quotuid&quot; or &quot;sAMAccountName&quot;' => 'ユーザー名のLDAP属性。例：&quotuid&quot; または &quot;sAMAccountName&quot;',
  'Limit access to users meeting this criteria. Example: &quot(objectClass=posixAccount)&quot; or &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;' => 'この基準を満たすユーザーへのアクセスを制限します。例：&quot(objectClass=posixAccount)&quot; または &quot;(&(objectClass=person)(memberOf=CN=Workers,CN=Users,DC=myDomain,DC=com))&quot;',
  'Save' => '保存',
  'Status: Error! (Message: {message})' => 'ステータス：エラー！（メッセージ：{message}）',
  'Status: OK! ({userCount} Users)' => 'ステータス：OK！（{userCount} ユーザー）',
  'The default base DN used for searching for accounts.' => 'デフォルトのベース識別名（DN）を、アカウントの検索に使用。',
  'The default credentials password (used only with username above).' => 'デフォルトの資格のパスワード（上のユーザ名でのみ使用されます）。',
  'The default credentials username. Some servers require that this be in DN form. This must be given in DN form if the LDAP server requires a DN to bind and binding should be possible with simple usernames.' => 'デフォルトの認証情報はユーザー名。サーバによってはこれがDN形式であることを必要とする。 LDAPサーバがバインドする結合は、単純なユーザ名で可能であるべきで、DNを必要とする場合にはDN形式で指定する必要があります。',
);
