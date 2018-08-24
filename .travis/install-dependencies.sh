#!/usr/bin/env sh

# -e = exit when one command returns != 0, -v print each command before executing
set -ev

# Install chomedriver
curl -s -L -o chromedriver_linux64.zip https://chromedriver.storage.googleapis.com/2.41/chromedriver_linux64.zip \
    && unzip -o -d $HOME chromedriver_linux64.zip \
	&& chmod +x $HOME/chromedriver

# Install node
curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
sudo apt-get install -y nodejs

# Update npm
npm install -g npm@latest