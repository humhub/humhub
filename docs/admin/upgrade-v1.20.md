# Upgrading to HumHub 1.20 — notes for system administrators

1.20 moves everything the web server should serve into a `public/` directory. This page covers what
that means for your server configuration. Nothing here is about the database or the application
update itself.

## What changed

```
<installation directory>/
├── public/          ← the new document root
│   ├── index.php    ← the entry script
│   ├── .htaccess
│   ├── robots.txt
│   └── assets/      ← published assets, must be writable
├── index.php        ← deprecated, still works
├── .htaccess        ← maps everything into public/
├── protected/       ┐
├── uploads/         ├ no longer meant to be reachable over the web
├── themes/          ┘
└── composer.json, .env, docs/, node_modules/ …
```

Before 1.20 the document root was the installation directory, which meant `.env`, `protected/`,
`composer.json` and your uploads were only kept out of reach by `.htaccess` rules. On nginx, where
those rules do not apply, they were reachable outright. Serving from `public/` removes the whole
class of problem: what is not in that directory has no URL.

## Pick the situation that matches your host

### You control the web server configuration

Point the document root at `public/` and you are done.

**Apache**

```apache
<VirtualHost *:443>
    ServerName example.com
    DocumentRoot /var/www/humhub/public

    <Directory /var/www/humhub/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`AllowOverride All` lets `public/.htaccess` do the URL rewriting. If you prefer to keep `.htaccess`
disabled, move its rewrite rules into the `<Directory>` block.

**nginx**

```nginx
server {
    listen 443 ssl;
    server_name example.com;

    root /var/www/humhub/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Only the `root` line changes — it used to point at the installation directory. The `try_files` rule
is unchanged. **Do not leave the old `root` in place**: on nginx nothing else protects `.env` and
`protected/`.

### Your hosting panel lets you choose a document root per domain

Plesk, cPanel and DirectAdmin can all point a domain at a subdirectory. Set it to
`<installation directory>/public`. Same result as above.

### The document root is fixed (typical shared hosting)

Leave everything where it is. The `.htaccess` shipped in the installation directory maps every
request into `public/`:

```apache
RewriteRule ^\.well-known/ - [L]
RewriteRule ^public/ - [L]
RewriteRule ^(.*)$ public/$1 [L]
```

Nothing beside `public/` can be requested any more, so `.env`, `protected/` and `uploads/` are out
of reach even though they sit in the document root. URLs stay clean — visitors never see `/public`
in the address bar — and a subdirectory installation (`example.com/humhub/`) works without further
configuration.

This needs Apache with `mod_rewrite`. Without it nothing is mapped and the site falls back to the
deprecated entry script described below.

### None of the above

The `index.php` in the installation directory still serves the site, without a redirect. It is
deprecated, will be removed in a future version, and while it is in use your installation directory
is served by the web server — which is what you want to get away from. Assets are loaded from
`/public/assets/…` in this mode.

The shipped rules still keep `.env`, `composer.json`, dot directories like `.git` and the whole
`protected/` and `uploads/` trees out of reach without `mod_rewrite` — each of those two directories
carries its own `.htaccess`, which is the only way to protect a directory from a parent `.htaccess`
(`<FilesMatch>` matches file names, not directories, and `<Directory>` is not allowed there). What
stays readable is the rest of the installation directory: `README.md`, `CHANGELOG.md`, `docs/`,
`node_modules/` and `themes/`. That is why HumHub reports this situation as an error rather than a
warning.

## What HumHub reports

*Administration → Information → Prerequisites* gains two entries under **Web server**:

| Entry | Meaning |
|---|---|
| Entry script — OK | The site is served through `public/index.php`. |
| Entry script — warning | The deprecated `index.php` in the installation directory is in use. |
| Document root — OK | The installation directory is not reachable over the web. |
| Document root — error | It is reachable. Move the document root, or let the `.htaccess` map requests. |
| Document root — warning | HumHub could not check, because the installation cannot reach itself over HTTP (split-horizon DNS, container networking). Verify by hand that the installation directory is not served. |

The same two findings appear in the incomplete-setup panel on the admin dashboard.

The document-root check asks the site over HTTP for a file that only exists in the installation
directory. It only does so when the site is reached under a `/public` path segment — for a properly
served installation it makes no request at all. The result is cached for an hour.

## Things to watch after the update

**`.htaccess` is now shipped, not a template.** Both `.htaccess.dist` files are gone; `.htaccess`
and `public/.htaccess` are part of the release. There is no renaming step any more — and forgetting
it can no longer leave your installation directory exposed. The flip side: if you had edited your
`.htaccess`, copying the new release over your installation replaces it. Host-specific
configuration — HTTPS redirects, caching headers, IP restrictions — belongs in the virtual host,
where updates cannot touch it. A subdirectory installation no longer needs `RewriteBase`; the rules
derive it.

**`uploads/` is now refused outright.** It gained an `.htaccess` of its own, like `protected/`
already had. Nothing there was ever served directly — what is meant to be public, profile images and
the site icon, is published into `public/assets/` on demand — so this only closes a hole that the
old layout left open. If you have a rule of your own that serves anything from `uploads/`, it stops
working.

**Dot directories are now blocked for subdirectory installations too.** The previous rule was
anchored to the start of the URL path, so on an installation reached under `example.com/humhub/` a
request for `/humhub/.git/config` was served rather than refused. The rule now matches a dot segment
anywhere in the path, and sits outside the `mod_rewrite` block so it applies without that module.
If you run HumHub in a subdirectory, this is worth verifying after the update.

**`Options +FollowSymLinks`** is in both files, as it was in the `.dist` files before, because
`mod_rewrite` requires it inside `.htaccess`. A host that allows `.htaccess` overrides but not
`Options` answers every request with *500 Internal Server Error*. If that happens after the update,
remove that one line from both files and tell your host.

**The old `assets/` directory** in the installation directory is a leftover publish cache. Published
assets now live in `public/assets/`, which must be writable by the web server. The old directory can
be deleted; HumHub does not remove it for you.

**Asset URLs change** from `/assets/…` to `/public/assets/…` while the deprecated entry script is in
use. If you have a CDN or a caching proxy in front of HumHub, its rules may need the new path — one
more reason to move the document root instead.

**`HUMHUB_PUBLIC_URL`** exists for layouts the automatic derivation cannot cover: the deprecated
entry script in use *and* `public/` not below it. Set it in `.env` to the URL `public/` is reachable
under. You almost certainly do not need it; Prerequisites tells you when you do.

## Modules

Module code that used the `@webroot` alias may need updating — it now resolves to `public/`, and the
new `@root` alias names the installation directory. Marketplace modules compatible with 1.20 have
this handled; see `docs/develop/module-migrate-1.20.md` for custom modules.
