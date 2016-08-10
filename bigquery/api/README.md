# Google BigQuery PHP Sample Application

## Description

This simple command-line application demonstrates how to invoke Google BigQuery from PHP.

## Build and Run
1.  **Enable APIs** - [Enable the BigQuery API](https://console.cloud.google.com/flows/enableapi?apiid=bigquery)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory

    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/bigquery/api
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php bigquery.php`. The following commands are available:

    ```sh
    browse-table  Browse a BigQuery table
    datasets      List BigQuery datasets
    export        Export data from a BigQuery table into a Cloud Storage bucket
    import        Import data into a BigQuery table
    projects      List BigQuery projects
    query         Run a BigQuery query
    schema        Create or delete a table schema in BigQuery
    tables        List BigQuery tables
```

## The commands

### browse-table

Browse a BigQuery table.

```sh
$ php bigquery.php browse-table test_dataset1.test_table1
--- Row 1 ---
name: Brent Shaffer
title: PHP Developer
[Press enter for next page, "n" to exit]
```

### datasets

List the datasets for your BigQuery project.

```sh
$ php bigquery.php datasets
test_dataset1
test_dataset2
test_dataset3
```

### export

Export data from a BigQuery table:

```sh
$ php bigquery.php export test_dataset.test_table gs://your_bucket/your_data.csv --format=csv
$ php bigquery.php export test_dataset.test_table gs://your_bucket/your_data.json --format=json
```

### import

Import data into a BigQuery table. You can import from several sources.

1.  Import from a local JSON or CSV file. Make sure your files are
    [formatted correctly](https://cloud.google.com/bigquery/loading-data#specifying_the_source_format)

    ```sh
    $ php bigquery.php import test_dataset.test_table /path/to/your_data.csv
    $ php bigquery.php import test_dataset.test_table /path/to/your_data.json
```
1.  Import from [a JSON or CSV file in Google Cloud Storage](https://cloud.google.com/bigquery/docs/loading-data-cloud-storage)

    ```sh
    $ php bigquery.php import test_dataset.test_table gs://your-storage-bucket/your_data.csv
    $ php bigquery.php import test_dataset.test_table gs://your-storage-bucket/your_data.json
```
1.  Import from a [Datastore Backup](https://cloud.google.com/bigquery/loading-data-cloud-datastore)

    ```sh
    $ php bigquery.php import test_dataset.test_table gs://your-storage-bucket/your_data.backup_info
```

You can also [stream data into bigquery](https://cloud.google.com/bigquery/streaming-data-into-bigquery)
one record at a time. This approach enables querying data without the delay of running a load job:

```sh
$ php bigquery.php import test_dataset.test_table
Import data for project cloud-samples-tests-php? [y/n]: y
name (required): Brent Shaffer
title (required): PHP Developer
Data streamed into BigQuery successfully
```

### projects

List your BigQuery projects.

```sh
$ php bigquery.php projects
test_project1
test_project2
test_project3
```

### query

Run a BigQuery query

```sh
$ php bigquery.php query "SELECT TOP(corpus, 3) as title, COUNT(*) as unique_words FROM [publicdata:samples.shakespeare]"
--- Row 1 ---
title: hamlet
unique_words: 5318
--- Row 2 ---
title: kinghenryv
unique_words: 5104
--- Row 3 ---
title: cymbeline
unique_words: 4875
```

### schema

Create a table schema in BigQuery. If a schema file is not supplied, you can
create a schema interactively.

```sh
$ php bigquery.php schema my_dataset.my_table --project your-project-id
Using project your-project-id
1st column name: name
1st column type (default: string):
  [0] string
  [1] bytes
  [2] integer
  [3] float
  [4] boolean
  [5] timestamp
  [6] date
  [7] record
 > 0
1st column mode (default: nullable):
  [0] nullable
  [1] required
  [2] repeated
 > 1
add another field? [y/n]: n
[
    {
        "name": "name",
        "type": "string",
        "mode": "required"
    }
]
Does this schema look correct? [y/n]: y
Table created successfully
```

The schema command also allows the deletion of tables
```sh
$ php bigquery.php schema my_dataset.my_table --project your-project-id --delete
Using project your-project-id
Are you sure you want to delete the BigQuery table "my_table"? [y/n]: y
Table deleted successfully
```

### tables

List tables for a BigQuery dataset.

```sh
$ php bigquery.php tables test_dataset1
test_table1
test_table2
test_table3
```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
