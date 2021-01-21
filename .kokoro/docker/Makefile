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
.PHONY: all build push

all:
	make build
	make push

build:
	docker build php72 -t gcr.io/cloud-devrel-kokoro-resources/php72
	docker build php73 -t gcr.io/cloud-devrel-kokoro-resources/php73
	docker build php74 -t gcr.io/cloud-devrel-kokoro-resources/php74

push:
	docker push gcr.io/cloud-devrel-kokoro-resources/php72
	docker push gcr.io/cloud-devrel-kokoro-resources/php73
	docker push gcr.io/cloud-devrel-kokoro-resources/php74
