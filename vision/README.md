# Google Vision PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=vision

## Description

This simple command-line application demonstrates how to invoke
[Google Vision API][vision-api] from PHP.

[vision-api]: https://cloud.google.com/vision/docs/quickstart-client-libraries

## Build and Run
1.  **Enable APIs** - [Enable the Vision API](https://console.cloud.google.com/flows/enableapi?apiid=vision.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/vision
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  For a basic demonstration of the Cloud Vision API, run `php quickstart.php`.
6.  Run `php vision.php` or `php product_search.php`. For `vision.php`, the following commands are available:
```
  face            Detect faces in an image using Google Cloud Vision API
  help            Displays help for a command
  label           Detect labels in an image using Google Cloud Vision API
  landmark        Detect landmarks in an image using Google Cloud Vision API
  list            Lists commands
  localize-object Detect objects in an image using Google Cloud Vision API
  logo            Detect logos in an image using Google Cloud Vision API
  property        Detect image proerties in an image using Google Cloud Vision API
  safe-search     Detect adult content in an image using Google Cloud Vision API
  text            Detect text in an image using Google Cloud Vision API
  crop-hints      Detect crop hints in an image using Google Cloud Vision API
  document-text   Detect document text in an image using Google Cloud Vision API
  pdf             Detect text in a PDF/TIFF using Google Cloud Vision API
  web             Detect web entities in an image using Google Cloud Vision API
  web-geo         Detect web entities in an image with geo metadata using
                  Google Cloud Vision API
```
   For `product_search.php`, the following commands are available:
```
  product-create                        Create a product
  product-delete                        Delete a product
  product-get                           Get information of a product
  product-list                          List information for all products
  product-update                        Update information for a product
  product-image-create                  Create reference image
  product-image-delete                  Delete reference image
  product-image-get                     Get reference image information for a product
  product-image-list                    List all reference image information for a product
  product-search-similar                Search for similar products to local image
  product-search-similar-gcs            Search for similar products to GCS image
  product-set-create                    Create a product set
  product-set-delete                    Delete a product set
  product-set-get                       Get information for a product set
  product-set-import                    Import a product set
  product-set-list                      List information for all product sets
  product-set-add-product               Add product to a product set
  product-set-list-products             List products in a product set
  product-set-remove-product            Remove product from a product set
  product-purge-orphan                  Delete all products not in any product sets
  product-purge-products-in-product-set Delete all products not in any product set
```

7. Run `php vision.php COMMAND --help` or `php product_search.php COMMAND --help` to print information about the usage of each command.

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and [report issues][google-cloud-php-issues].

## Troubleshooting

If you get the following error, set the environment variable `GCLOUD_PROJECT` to your project ID:

```
[Google\Cloud\Core\Exception\GoogleException]
No project ID was provided, and we were unable to detect a default project ID.
```

If you have not set a timezone you may get an error from php. This can be resolved by:

  1. Finding where the php.ini is stored by running `php -i | grep 'Configuration File'`
  1. Finding out your timezone from the list on this page: http://php.net/manual/en/timezones.php
  1. Editing the php.ini file (or creating one if it doesn't exist)
  1. Adding the timezone to the php.ini file e.g., adding the following line: `date.timezone = "America/Los_Angeles"`

[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
