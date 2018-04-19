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
.PHONY: all php56 php70 php71 php72
php56:
	mkdir -p php56
	sed -e 's/PHP_VERSION/php56/g' Dockerfile.template > php56/Dockerfile
php70:
	mkdir -p php70
	sed -e 's/PHP_VERSION/php70/g' Dockerfile.template > php70/Dockerfile
php71:
	mkdir -p php71
	sed -e 's/PHP_VERSION/php71/g' Dockerfile.template > php71/Dockerfile
php72:
	mkdir -p php72
	sed -e 's/PHP_VERSION/php72/g' Dockerfile.template > php72/Dockerfile
all:
	make php56
	make php70
	make php71
	make php72
