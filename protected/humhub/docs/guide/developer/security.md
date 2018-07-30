Security
=================

This guide assembles some tips and recommendations around the security of your module.

- Read and follow the [Yii Security Best Practises](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)!
- Obviously use [permissions](permissions.md) to secure vulnerable sections of your module.
- Prevent **guest access** of sensitive data.
- [Validate](https://www.yiiframework.com/doc/guide/2.0/en/input-validation) user input.
- Use [[humhub\libs\Html::encode()]] to encode view output provided by user input.
- [Prevent SQL Injections](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices#avoiding-sql-injections)
- Also see the [Administration Security Guide](../admin/security.md)

