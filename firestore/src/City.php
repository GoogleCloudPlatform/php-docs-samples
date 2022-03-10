<?php
/**
 * Copyright 2022 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

# [START firestore_data_custom_type_definition]
class City
{
    public string $name;
    public string $state;
    public string $country;
    public bool $capital;
    public int $population;
    public array $regions;

    public function __construct(
        string $name,
        string $state,
        string $country,
        bool $capital = false,
        int $population = 0,
        array $regions = []
    ) {
        $this->name = $name;
        $this->state = $state;
        $this->country = $country;
        $this->capital = $capital;
        $this->population = $population;
        $this->regions = $regions;
    }

    public static function fromArray(array $source): City
    {
        // implementation of fromArray is excluded for brevity
        # [START_EXCLUDE]
        $city = new City(
            $source['name'],
            $source['state'],
            $source['country'],
            $source['capital'] ?? false,
            $source['population'] ?? 0,
            $source['regions'] ?? []
        );

        return $city;
        # [END_EXCLUDE]
    }

    public function toArray(): array
    {
        // implementation of toArray is excluded for brevity
        # [START_EXCLUDE]
        $dest = [
            'name' => $this->name,
            'state' => $this->state,
            'country' => $this->country,
            'capital' => $this->capital,
            'population' => $this->population,
            'regions' => $this->regions,
        ];

        return $dest;
        # [END_EXCLUDE]
    }

    public function __toString()
    {
        return sprintf(
            <<<EOF
        Custom Type data(
            name=%s,
            state=%s,
            country=%s,
            capital=%s,
            population=%s,
            regions=%s
        )
        EOF,
            $this->name,
            $this->state,
            $this->country,
            $this->population,
            $this->capital ? 'true' : 'false',
            implode(', ', $this->regions)
        );
    }
}

# [END firestore_data_custom_type_definition]
