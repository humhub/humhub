#!/bin/sh

set -e

WAIT_FOR_DB=${HUMHUB_WAIT_FOR_DB:-1}
SET_PJAX=${HUMHUB_SET_PJAX:-1}
AUTOINSTALL=${HUMHUB_AUTO_INSTALL:-"false"}
ENTRYPOINT_QUIET_LOGS=${ENTRYPOINT_QUIET_LOGS:-}

HUMHUB_DB_NAME=${HUMHUB_DB_NAME:-"humhub"}
HUMHUB_DB_HOST=${HUMHUB_DB_HOST:-"db"}
HUMHUB_DB_PORT=${HUMHUB_DB_PORT:-3306}
HUMHUB_NAME=${HUMHUB_NAME:-"HumHub"}
HUMHUB_EMAIL=${HUMHUB_EMAIL:-"humhub@example.com"}
HUMHUB_LANG=${HUMHUB_LANG:-"en-US"}
HUMHUB_DEBUG=${HUMHUB_DEBUG:-"false"}

HUMHUB_ADMIN_LOGIN=${HUMHUB_ADMIN_LOGIN:-"admin"}
HUMHUB_ADMIN_EMAIL=${HUMHUB_ADMIN_EMAIL:-${HUMHUB_EMAIL}}
HUMHUB_ADMIN_PASSWORD=${HUMHUB_ADMIN_PASSWORD:-"test"}

HUMHUB_CACHE_CLASS=${HUMHUB_CACHE_CLASS:-"yii\caching\FileCache"}
HUMHUB_CACHE_EXPIRE_TIME=${HUMHUB_CACHE_EXPIRE_TIME:-3600}

HUMHUB_ANONYMOUS_REGISTRATION=${HUMHUB_ANONYMOUS_REGISTRATION:-1}
HUMHUB_ALLOW_GUEST_ACCESS=${HUMHUB_ALLOW_GUEST_ACCESS:-0}
HUMHUB_NEED_APPROVAL=${HUMHUB_NEED_APPROVAL:-0}

# LDAP Config
HUMHUB_LDAP_ENABLED=${HUMHUB_LDAP_ENABLED:-0}
HUMHUB_LDAP_HOSTNAME=${HUMHUB_LDAP_HOSTNAME:-localhost}
HUMHUB_LDAP_PORT=${HUMHUB_LDAP_PORT:-389}
HUMHUB_LDAP_ENCRYPTION=${HUMHUB_LDAP_ENCRYPTION:-"false"}
HUMHUB_LDAP_USERNAME=${HUMHUB_LDAP_USERNAME:-"humhub"}
HUMHUB_LDAP_PASSWORD=${HUMHUB_LDAP_PASSWORD:-"humhub"}
HUMHUB_LDAP_BASE_DN=${HUMHUB_LDAP_BASE_DN:-"dc=example,dc=com"}
HUMHUB_LDAP_LOGIN_FILTER=${HUMHUB_LDAP_LOGIN_FILTER:-""}
HUMHUB_LDAP_USER_FILTER=${HUMHUB_LDAP_USER_FILTER:-""}
HUMHUB_LDAP_USERNAME_ATTRIBUTE=${HUMHUB_LDAP_USERNAME_ATTRIBUTE:-"cn"}
HUMHUB_LDAP_EMAIL_ATTRIBUTE=${HUMHUB_LDAP_EMAIL_ATTRIBUTE:-"mail"}
HUMHUB_LDAP_ID_ATTRIBUTE=${HUMHUB_LDAP_ID_ATTRIBUTE:-"uid"}
HUMHUB_LDAP_REFRESH_USERS=${HUMHUB_LDAP_REFRESH_USERS:-1}
HUMHUB_LDAP_CACERT=${HUMHUB_LDAP_CACERT:-""}
HUMHUB_LDAP_SKIP_VERIFY=${HUMHUB_LDAP_SKIP_VERIFY:-0}

# Mailer Config
HUMHUB_MAILER_SYSTEM_EMAIL_ADDRESS=${HUMHUB_MAILER_SYSTEM_EMAIL_ADDRESS:-"noreply@example.com"}
HUMHUB_MAILER_SYSTEM_EMAIL_NAME=${HUMHUB_MAILER_SYSTEM_EMAIL_NAME:-"HumHub"}
HUMHUB_MAILER_TRANSPORT_TYPE=${HUMHUB_MAILER_TRANSPORT_TYPE:-"php"}
HUMHUB_MAILER_HOSTNAME=${HUMHUB_MAILER_HOSTNAME:-"localhost"}
HUMHUB_MAILER_PORT=${HUMHUB_MAILER_PORT:-"25"}
HUMHUB_MAILER_USERNAME=${HUMHUB_MAILER_USERNAME:-""}
HUMHUB_MAILER_PASSWORD=${HUMHUB_MAILER_PASSWORD:-""}
HUMHUB_MAILER_ENCRYPTION=${HUMHUB_MAILER_ENCRYPTION:-""}
HUMHUB_MAILER_ALLOW_SELF_SIGNED_CERTS=${HUMHUB_MAILER_ALLOW_SELF_SIGNED_CERTS:-0}

# Redis Config
HUMHUB_REDIS_HOSTNAME=${HUMHUB_REDIS_HOSTNAME:-""}
HUMHUB_REDIS_PORT=${HUMHUB_REDIS_PORT:-6379}
HUMHUB_REDIS_PASSWORD=${HUMHUB_REDIS_PASSWORD:-""}

wait_for_db() {
	if [ "$WAIT_FOR_DB" = "false" ]; then
		return 0
	fi

	if [ -n "$HUMHUB_REDIS_HOSTNAME" ]; then
		until nc -z -v -w60 "$HUMHUB_REDIS_HOSTNAME" "$HUMHUB_REDIS_PORT"; do
			echo >&3 "$0: Waiting for redis connection..."
			# wait for 5 seconds before check again
			sleep 5
		done
	fi

	until nc -z -v -w60 "$HUMHUB_DB_HOST" "$HUMHUB_DB_PORT"; do
		echo >&3 "$0: Waiting for database connection..."
		# wait for 5 seconds before check again
		sleep 5
	done
}

if [ -z "$ENTRYPOINT_QUIET_LOGS" ]; then
    exec 3>&1
else
    exec 3>/dev/null
fi

echo >&3 "$0: Starting pre-launch ..."

if [ -f "/var/www/localhost/htdocs/protected/config/dynamic.php" ]; then
	echo >&3 "$0: Existing installation found!"

	wait_for_db

	INSTALL_VERSION=$(cat /var/www/localhost/htdocs/protected/config/.version)
	SOURCE_VERSION=$(cat /usr/src/humhub/.version)
	cd /var/www/localhost/htdocs/protected/ || exit 1
	if [ "$INSTALL_VERSION" != "$SOURCE_VERSION" ]; then
		echo >&3 "$0: Updating from version $INSTALL_VERSION to $SOURCE_VERSION"
		php yii migrate/up --includeModuleMigrations=1 --interactive=0
		php yii search/rebuild
		cp -v /usr/src/humhub/.version /var/www/localhost/htdocs/protected/config/.version
	fi
else
	echo >&3 "$0: No existing installation found!"
	echo >&3 "$0: Installing source files..."
	cp -rv /usr/src/humhub/protected/config/* /var/www/localhost/htdocs/protected/config/
	cp -v /usr/src/humhub/.version /var/www/localhost/htdocs/protected/config/.version

	if [ ! -f "/var/www/localhost/htdocs/protected/config/common.php" ]; then
		echo >&3 "$0: Generate config using common factory..."

		echo '<?php return ' \
			>/var/www/localhost/htdocs/protected/config/common.php

		sh -c "php /var/www/localhost/htdocs/protected/config/common-factory.php" \
			>>/var/www/localhost/htdocs/protected/config/common.php

		echo ';' \
			>>/var/www/localhost/htdocs/protected/config/common.php
	fi

	if ! php -l /var/www/localhost/htdocs/protected/config/common.php; then
		echo >&3 "$0: Humhub common config is not valid! Fix errors before restarting."
		exit 1
	fi

	mkdir -p /var/www/localhost/htdocs/protected/runtime/logs/
	touch /var/www/localhost/htdocs/protected/runtime/logs/app.log

	echo >&3 "$0: Setting permissions..."
	chown -R nginx:nginx /var/www/localhost/htdocs/uploads
	chown -R nginx:nginx /var/www/localhost/htdocs/protected/modules
	chown -R nginx:nginx /var/www/localhost/htdocs/protected/config
	chown -R nginx:nginx /var/www/localhost/htdocs/protected/runtime

	wait_for_db

	echo >&3 "$0: Creating database..."
	cd /var/www/localhost/htdocs/protected/ || exit 1
	if [ -z "$HUMHUB_DB_USER" ]; then
		AUTOINSTALL="false"
	fi

	if [ "$AUTOINSTALL" != "false" ]; then
		echo >&3 "$0: Installing..."
		php yii installer/write-db-config "$HUMHUB_DB_HOST" "$HUMHUB_DB_NAME" "$HUMHUB_DB_USER" "$HUMHUB_DB_PASSWORD"
		php yii installer/install-db
		php yii installer/write-site-config "$HUMHUB_NAME" "$HUMHUB_EMAIL"
		# Set baseUrl if provided
		if [ -n "$HUMHUB_PROTO" ] && [ -n "$HUMHUB_HOST" ]; then
			HUMHUB_BASE_URL="${HUMHUB_PROTO}://${HUMHUB_HOST}${HUMHUB_SUB_DIR}/"
			echo >&3 "$0: Setting base url to: $HUMHUB_BASE_URL"
			php yii installer/set-base-url "${HUMHUB_BASE_URL}"
		fi
		php yii installer/create-admin-account "${HUMHUB_ADMIN_LOGIN}" "${HUMHUB_ADMIN_EMAIL}" "${HUMHUB_ADMIN_PASSWORD}"

		php yii 'settings/set' 'base' 'cache.class' "${HUMHUB_CACHE_CLASS}"
		php yii 'settings/set' 'base' 'cache.expireTime' "${HUMHUB_CACHE_EXPIRE_TIME}"

		php yii 'settings/set' 'user' 'auth.anonymousRegistration' "${HUMHUB_ANONYMOUS_REGISTRATION}"
		php yii 'settings/set' 'user' 'auth.allowGuestAccess' "${HUMHUB_ALLOW_GUEST_ACCESS}"
		php yii 'settings/set' 'user' 'auth.needApproval' "${HUMHUB_NEED_APPROVAL}"

		if [ "$HUMHUB_LDAP_ENABLED" != "0" ]; then
			echo >&3 "$0: Setting LDAP configuration..."
			php yii 'settings/set' 'ldap' 'enabled' "${HUMHUB_LDAP_ENABLED}"
			php yii 'settings/set' 'ldap' 'hostname' "${HUMHUB_LDAP_HOSTNAME}"
			php yii 'settings/set' 'ldap' 'port' "${HUMHUB_LDAP_PORT}"
			php yii 'settings/set' 'ldap' 'encryption' "${HUMHUB_LDAP_ENCRYPTION}"
			php yii 'settings/set' 'ldap' 'username' "${HUMHUB_LDAP_USERNAME}"
			php yii 'settings/set' 'ldap' 'password' "${HUMHUB_LDAP_PASSWORD}"
			php yii 'settings/set' 'ldap' 'baseDn' "${HUMHUB_LDAP_BASE_DN}"
			php yii 'settings/set' 'ldap' 'loginFilter' "${HUMHUB_LDAP_LOGIN_FILTER}"
			php yii 'settings/set' 'ldap' 'userFilter' "${HUMHUB_LDAP_USER_FILTER}"
			php yii 'settings/set' 'ldap' 'usernameAttribute' "${HUMHUB_LDAP_USERNAME_ATTRIBUTE}"
			php yii 'settings/set' 'ldap' 'emailAttribute' "${HUMHUB_LDAP_EMAIL_ATTRIBUTE}"
			php yii 'settings/set' 'ldap' 'idAttribute' "${HUMHUB_LDAP_ID_ATTRIBUTE}"
			php yii 'settings/set' 'ldap' 'refreshUsers' "${HUMHUB_LDAP_REFRESH_USERS}"
		fi

		php yii 'settings/set' 'base' 'mailer.systemEmailAddress' "${HUMHUB_MAILER_SYSTEM_EMAIL_ADDRESS}"
		php yii 'settings/set' 'base' 'mailer.systemEmailName' "${HUMHUB_MAILER_SYSTEM_EMAIL_NAME}"
		if [ "$HUMHUB_MAILER_TRANSPORT_TYPE" != "php" ]; then
			echo >&3 "$0: Setting Mailer configuration..."
			php yii 'settings/set' 'base' 'mailer.transportType' "${HUMHUB_MAILER_TRANSPORT_TYPE}"
			php yii 'settings/set' 'base' 'mailer.hostname' "${HUMHUB_MAILER_HOSTNAME}"
			php yii 'settings/set' 'base' 'mailer.port' "${HUMHUB_MAILER_PORT}"
			php yii 'settings/set' 'base' 'mailer.username' "${HUMHUB_MAILER_USERNAME}"
			php yii 'settings/set' 'base' 'mailer.password' "${HUMHUB_MAILER_PASSWORD}"
			php yii 'settings/set' 'base' 'mailer.encryption' "${HUMHUB_MAILER_ENCRYPTION}"
			php yii 'settings/set' 'base' 'mailer.allowSelfSignedCerts' "${HUMHUB_MAILER_ALLOW_SELF_SIGNED_CERTS}"
		fi

		chown -R nginx:nginx /var/www/localhost/htdocs/protected/runtime
		chown nginx:nginx /var/www/localhost/htdocs/protected/config/dynamic.php
	fi
fi

echo >&3 "$0: Config preprocessing ..."

if test -e /var/www/localhost/htdocs/protected/config/dynamic.php &&
	grep "'installed' => true" /var/www/localhost/htdocs/protected/config/dynamic.php -q; then
	echo >&3 "$0: installation active"

	if [ "$SET_PJAX" != "false" ]; then
		sed -i \
			-e "s/'enablePjax' => false/'enablePjax' => true/g" \
			/var/www/localhost/htdocs/protected/config/common.php
	fi

	if [ -n "$HUMHUB_TRUSTED_HOSTS" ]; then
		sed -i \
			-e "s|'trustedHosts' => \['.*'\]|'trustedHosts' => ['$HUMHUB_TRUSTED_HOSTS']|g" \
			/var/www/localhost/htdocs/protected/config/web.php
	fi
else
	echo >&3 "$0: no installation config found or not installed"
	export HUMHUB_INTEGRITY_CHECK="false"
fi

if [ "$HUMHUB_DEBUG" = "false" ]; then
	sed -i '/YII_DEBUG/s/^\/*/\/\//' /var/www/localhost/htdocs/index.php
	sed -i '/YII_ENV/s/^\/*/\/\//' /var/www/localhost/htdocs/index.php
	echo >&3 "$0: debug disabled"
else
	sed -i '/YII_DEBUG/s/^\/*//' /var/www/localhost/htdocs/index.php
	sed -i '/YII_ENV/s/^\/*//' /var/www/localhost/htdocs/index.php
	echo >&3 "$0: debug enabled"
fi

if [ "$HUMHUB_LDAP_SKIP_VERIFY" != "0" ]; then
	echo "Setting LDAP TLS SKIP VERIFY"
	echo "TLS_REQCERT ALLOW" >> /etc/openldap/ldap.conf
fi

if [ "$HUMHUB_LDAP_CACERT" != "" ]; then
	echo "Setting LDAP CACERT"
	echo "$HUMHUB_LDAP_CACERT" > /etc/ssl/certs/cacert.crt
	echo "TLS_CACERT  /etc/ssl/certs/cacert.crt" >> /etc/openldap/ldap.conf
fi

if /usr/bin/find "/docker-entrypoint.d/" -mindepth 1 -maxdepth 1 -type f -print -quit 2>/dev/null; then
	echo >&3 "$0: /docker-entrypoint.d/ is not empty, will attempt to perform configuration"
	echo >&3 "$0: Looking for shell scripts in /docker-entrypoint.d/"

	find "/docker-entrypoint.d/" -follow -type f -print | sort -n | while read -r f; do
		case "$f" in
			*.sh)
				if [ -x "$f" ]; then
					echo >&3 "$0: Launching $f";
					"$f"
				else
					# warn on shell scripts without exec bit
					echo >&3 "$0: Ignoring $f, not executable";
				fi
				;;
			*) echo >&3 "$0: Ignoring $f";;
		esac
	done

	echo >&3 "$0: Configuration complete; ready for start up"
else
	echo >&3 "$0: No files found in /docker-entrypoint.d/, skipping configuration"
fi

echo >&3 "$0: Entrypoint finished! Launching ..."

exec "$@"
