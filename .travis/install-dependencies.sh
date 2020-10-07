#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# Install chomedriver
CHROME_MAIN_VERSION=`google-chrome-stable --version | sed -E 's/(^Google Chrome |\.[0-9]+ )//g'`
CHROMEDRIVER_VERSION=`curl -s "https://chromedriver.storage.googleapis.com/LATEST_RELEASE_$CHROME_MAIN_VERSION"`

curl "https://chromedriver.storage.googleapis.com/${CHROMEDRIVER_VERSION}/chromedriver_linux64.zip" -O \
    && unzip -o -d $HOME chromedriver_linux64.zip \
	&& chmod +x $HOME/chromedriver
