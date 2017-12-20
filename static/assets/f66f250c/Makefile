.PHONY: dev
dev:
	./node_modules/.bin/webpack --watch

.PHONY: prod
prod:
	env PROD=true ./node_modules/.bin/webpack

.PHONY: analyze
analyze:
	env PROD=true BUNDLE_ANALYZER=true ./node_modules/.bin/webpack

.PHONY: setup
setup:
	yarn install

.PHONY: build-for-git
build-for-git: prod
	git stage -f build/*.js build/*d.ts

.PHONY: deploy
deploy:
	rsync -avz ./ garron.net:~/garron.net/code/clipboard-polyfill/ \
		--exclude .git \
		--exclude node_modules

.PHONY: publish
publish: deploy
	git push --tags origin
	git push origin master
	yarn publish
