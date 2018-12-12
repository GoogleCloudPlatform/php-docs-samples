#	Copyright 2018, Google, Inc.
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
.PHONY: all build push php56 php70 php71 php72

all:
	make build
	make push

php56:
	mkdir -p php56
	sed -e 's/PHP_VERSION/php56/g' Dockerfile.template > php56/Dockerfile
	echo "# Add steps specific to PHP 5.6" >> php56/Dockerfile
	echo "RUN rm /opt/php56/lib/ext.enabled/ext-memcached.ini" >> php56/Dockerfile
	echo "ENV RUN_CS_FIXER=true\n" >> php56/Dockerfile

php70:
	mkdir -p php70
	sed -e 's/PHP_VERSION/php70/g' Dockerfile.template > php70/Dockerfile

php71:
	mkdir -p php71
	sed -e 's/PHP_VERSION/php71/g' Dockerfile.template > php71/Dockerfile

php72:
	mkdir -p php72
	sed -e 's/PHP_VERSION/php72/g' Dockerfile.template > php72/Dockerfile

build:
	make php56 && docker build php56 -t gcr.io/cloud-devrel-kokoro-resources/php56
	make php70 && docker build php70 -t gcr.io/cloud-devrel-kokoro-resources/php70
	make php71 && docker build php71 -t gcr.io/cloud-devrel-kokoro-resources/php71
	make php72 && docker build php72 -t gcr.io/cloud-devrel-kokoro-resources/php72
	docker build php73 -t gcr.io/cloud-devrel-kokoro-resources/php73

push:
	docker push gcr.io/cloud-devrel-kokoro-resources/php56
	docker push gcr.io/cloud-devrel-kokoro-resources/php70
	docker push gcr.io/cloud-devrel-kokoro-resources/php71
	docker push gcr.io/cloud-devrel-kokoro-resources/php72
	docker push gcr.io/cloud-devrel-kokoro-resources/php73

push55:
	docker build php55 -t gcr.io/cloud-devrel-kokoro-resources/php55
	docker push gcr.io/cloud-devrel-kokoro-resources/php55
