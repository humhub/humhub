#!/bin/bash
set -e

# Criar usuário para o PHP-FPM
useradd -m phpuser 2>/dev/null || true

# Permissões
mkdir -p /run
chmod -R 777 /app/assets /app/uploads /app/protected/runtime /app/protected/config 2>/dev/null || true

# Criar config do Nginx
cat > /tmp/nginx.conf << 'EOF'
daemon off;
worker_processes 1;
error_log /dev/stderr;

events { worker_connections 1024; }

http {
    include /nix/var/nix/profiles/default/etc/nginx/mime.types;
    default_type application/octet-stream;
    access_log /dev/stdout;

    server {
        listen 8080;
        root /app;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$args;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/run/php-fpm.sock;
            fastcgi_index index.php;
            include /nix/var/nix/profiles/default/etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }

        location ~ /\. { deny all; }
    }
}
EOF

# Iniciar PHP-FPM em background
php-fpm -y /app/php-fpm.conf &

# Aguardar o socket ficar disponível
sleep 2

# Iniciar Nginx em foreground
nginx -c /tmp/nginx.conf
