# Google DLP PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=dlp

## Description

This simple command-line application demonstrates how to invoke
[Google DLP API][dlp-api] from PHP.

[dlp-api]: https://cloud.google.com/dlp/docs/libraries

## Build and Run
1.  **Enable APIs** - [Enable the DLP API](
    https://console.cloud.google.com/flows/enableapi?apiid=dlp.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/dlp
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Execute the snippets in the [src/](src/) directory by running
    `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/inspect_string.php
    Usage: php src/inspect_string.php PROJECT_ID STRING

    $ php src/inspect_string.php your-project-id 'bob@example.com'
    Findings:
      Quote: bob@example.com
      Info type: EMAIL_ADDRESS
      Likelihood: LIKELY
    ```

See the [DLP Documentation](https://cloud.google.com/dlp/docs/inspecting-text) for more information.

## Testing

### Setup
- Ensure that `GOOGLE_APPLICATION_CREDENTIALS` points to authorized service account credentials file.
- [Create a Google Cloud Project](https://console.cloud.google.com/projectcreate) and set the `GOOGLE_PROJECT_ID` environment variable.
    ```
    export GOOGLE_PROJECT_ID=YOUR_PROJECT_ID
    ```
- [Create a Google Cloud Storage bucket](https://console.cloud.google.com/storage) and upload [test.txt](src/test/data/test.txt).
    - Set the `GOOGLE_STORAGE_BUCKET` environment variable. 
    - Set the `GCS_PATH` environment variable to point to the path for the bucket file.
    ```
    export GOOGLE_STORAGE_BUCKET=YOUR_BUCKET
    export GCS_PATH=gs://GOOGLE_STORAGE_BUCKET/test.txt
    ```
- Set the `DLP_DEID_WRAPPED_KEY` environment variable to an AES-256 key encrypted ('wrapped') [with a Cloud Key Management Service (KMS) key](https://cloud.google.com/kms/docs/encrypt-decrypt).
- Set the `DLP_DEID_KEY_NAME` environment variable to the path-name of the Cloud KMS key you wrapped `DLP_DEID_WRAPPED_KEY` with.
    ```
    export DLP_DEID_WRAPPED_KEY=YOUR_ENCRYPTED_AES_256_KEY
    export DLP_DEID_KEY_NAME=projects/GOOGLE_PROJECT_ID/locations/YOUR_LOCATION/keyRings/YOUR_KEYRING_NAME/cryptoKeys/YOUR_KEY_NAME
    ```
- [Create a De-identify templates](https://console.cloud.google.com/security/dlp/create/template;template=deidentifyTemplate)
    - Create default de-identify template for unstructured file.
    - Create a de-identify template for  structured files.
    - Create image redaction template for images.
    ```
    export DLP_DEIDENTIFY_TEMPLATE=YOUR_DEFAULT_DEIDENTIFY_TEMPLATE
    export DLP_STRUCTURED_DEIDENTIFY_TEMPLATE=YOUR_STRUCTURED_DEIDENTIFY_TEMPLATE
    export DLP_IMAGE_REDACT_DEIDENTIFY_TEMPLATE=YOUR_IMAGE_REDACT_TEMPLATE
    ```
- Copy and paste the data below into a CSV file and [create a BigQuery table](https://cloud.google.com/bigquery/docs/loading-data-local) from the file:
    ```$xslt
    Name,TelephoneNumber,Mystery,Age,Gender
    James,(567) 890-1234,8291 3627 8250 1234,19,Male
    Gandalf,(223) 456-7890,4231 5555 6781 9876,27,Male
    Dumbledore,(313) 337-1337,6291 8765 1095 7629,27,Male
    Joe,(452) 223-1234,3782 2288 1166 3030,35,Male
    Marie,(452) 223-1234,8291 3627 8250 1234,35,Female
    Carrie,(567) 890-1234,2253 5218 4251 4526,35,Female
    ```
  Set the `DLP_DATASET_ID` and `DLP_TABLE_ID` environment values.
  ```
  export DLP_DATASET_ID=YOUR_BIGQUERY_DATASET_ID
  export DLP_TABLE_ID=YOUR_TABLE_ID
  ```
- [Create a Google Cloud Datastore](https://console.cloud.google.com/datastore) kind and add an entity with properties:
    ```
    Email : john@doe.com
    Person Name : John
    Phone Number : 343-343-3435

    Email : gary@doe.com
    Person Name : Gary
    Phone Number : 343-443-3136
    ```
    Provide namespace and kind values.
    -   Set the environment variables `DLP_NAMESPACE_ID` and `DLP_DATASTORE_KIND` with the values provided in above step.
    ```
    export DLP_NAMESPACE_ID=YOUR_NAMESPACE_ID
    export DLP_DATASTORE_KIND=YOUR_DATASTORE_KIND
    ```

## Troubleshooting

### bcmath extension missing

If you see an error like this:

```
PHP Fatal error:  Uncaught Error: Call to undefined function Google\Protobuf\Internal\bccomp() in /usr/local/google/home/crwilson/github/GoogleCloudPlatform/php-docs-samples/dlp/vendor/google/protobuf/src/Google/Protobuf/Internal/Message.php:986
```

You may need to install the bcmath PHP extension.
e.g. (may depend on your php version)
```
$ sudo apt-get install php8.0-bcmath
```


## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
