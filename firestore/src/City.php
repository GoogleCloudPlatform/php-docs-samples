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
    public $name;
    public $state;
    public $country;
    public $capital;
    public $population;
    public $regions;

    public function __construct(
        $name,
        $state,
        $country,
        $capital = false,
        $population = 0,
        $regions = []
    ) {
        $this->name = $name;
        $this->state = $state;
        $this->country = $country;
        $this->capital = $capital;
        $this->population = $population;
        $this->regions = $regions;
    }

    public static function from_map($source)
    {
        # [START_EXCLUDE]
        $city = new City($source['name'], $source['state'], $source['country']);
        $city->capital = $source['capital'] ?? null;
        $city->population = $source['population'] ?? null;
        $city->regions = $source['regions'] ?? null;

        return $city;
        # [END_EXCLUDE]
    }

    public function to_map()
    {
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
        return ("Custom Type data(\
        name={$this->name}, \
        country={$this->country}, \
        population={$this->population}, \
        capital={$this->capital}, \
        regions={$this->regions}\
       )");
    }
}

# [END firestore_data_custom_type_definition]
