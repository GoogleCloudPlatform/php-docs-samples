# Google Cloud Firestore API Samples

These samples show how to use the [Google Cloud Firestore API][cloud-firestore-api] to store and query data.

[cloud-firestore-api]: https://cloud.google.com/firestore/docs/quickstart-servers

## Setup

### Prerequisites

1. Open the [Firebase Console][firebase-console] and create a new project. (You can't use both Cloud Firestore and Cloud Datastore in the same project, which might affect apps using App Engine. Try using Cloud Firestore with a different project if this is the case).

1. In the Database section, click Try Firestore Beta.

1. Click Enable.

[firebase-console]: https://console.firebase.google.com


### Authentication

Authentication is typically done through [Application Default Credentials][adc]
which means you do not have to change the code to authenticate as long as
your environment has credentials. You have a few options for setting up
authentication:

1. When running locally, use the [Google Cloud SDK][google-cloud-sdk]

        gcloud auth application-default login

1. When running on App Engine or Compute Engine, credentials are already
   set-up. However, you may need to configure your Compute Engine instance
   with [additional scopes][additional_scopes].

1. You can create a [Service Account key file][service_account_key_file]. This file can be used to
   authenticate to Google Cloud Platform services from any environment. To use
   the file, set the ``GOOGLE_APPLICATION_CREDENTIALS`` environment variable to
   the path to the key file, for example:

        export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account.json

[adc]: https://cloud.google.com/docs/authentication#getting_credentials_for_server-centric_flow
[additional_scopes]: https://cloud.google.com/compute/docs/authentication#using
[service_account_key_file]: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#creatinganaccount

## Install Dependencies

1. [Enable the Cloud Firestore API](https://console.cloud.google.com/flows/enableapi?apiid=firestore.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

1. Create a service account at the
[Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)

1. Download the json key file of the service account.

1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

## Samples

To run the Cloud Firestore Samples:

    $ php firestore.php
    Cloud Firestore

    Usage:
      command [options] [arguments]

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Available commands:
      add-data                             Add data to a document.
      add-doc-data-after-auto-id           Auto-generate an ID for a document, then add document data.
      add-doc-data-types                   Set document data with different data types.
      add-doc-data-with-auto-id            Add document data with an auto-generated ID.
      add-subcollection                    Add a subcollection by creating a new document.
      array-membership                     Create queries using an an array-contains where clause.
      batch-write                          Batch write.
      chained-query                        Create a query with chained clauses.
      collection-ref                       Get a collection reference.
      composite-index-chained-query        Create a composite index chained query, which combines an equality operator with a range comparison.
      create-query-capital                 Create a query that gets documents where capital=True.
      create-query-state                   Create a query that gets documents where state=CA.
      delete-collection                    Delete a collection.
      delete-document                      Delete a document.
      delete-field                         Delete a field from a document.
      delete-test-collections              Delete test collections used in these code samples.
      document-path-ref                    Get a document path reference.
      document-ref                         Get a document reference.
      end-at-field-query-cursor            Define field end point for a query.
      get-all-docs                         Get all documents in a collection.
      get-document                         Get a document.
      get-multiple-docs                    Get multiple documents from a collection.
      help                                 Displays help for a command
      initialize                           Initialize Cloud Firestore with default project ID.
      initialize-project-id                Initialize Cloud Firestore with given project ID.
      invalid-range-order-by-query         An invalid range with order by query.
      invalid-range-query                  An example of an invalid range query.
      list                                 Lists commands
      list-subcollections                  List subcollections of a document.
      multiple-cursor-conditions           Set multiple cursor conditions.
      order-by-name-desc-limit-query       Create an order by name descending with limit query.
      order-by-name-limit-query            Create an order by name with limit query.
      order-by-state-and-population-query  Create an order by state and descending population query.
      paginated-query-cursor               Paginate using cursor queries.
      query-create-examples                Create an example collection of documents.
      range-order-by-query                 Create a range with order by query.
      range-query                          Create a query with range clauses.
      retrieve-all-documents               Retrieve all documents from a collection.
      retrieve-create-examples             Create an example collection of documents.
      return-info-transaction              Return information from your transaction.
      run-simple-transaction               Run a simple transaction.
      set-document                         Set document data.
      set-document-merge                   Set document data by merging it into the existing document.
      set-requires-id                      Set document data with a given document ID.
      simple-queries                       Create queries using single where clauses.
      start-at-field-query-cursor          Define field start point for a query.
      start-at-snapshot-query-cursor       Define snapshot start point for a query.
      subcollection-ref                    Get a reference to a subcollection document.
      update-doc                           Update a document.
      update-doc-array                     Update a document array field.
      update-doc-increment                 Update a document number field using Increment.
      update-nested-fields                 Update fields in nested data.
      update-server-timestamp              Update field with server timestamp.
      where-order-by-limit-query           Combine where with order by and limit in a query.

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
[google-cloud-sdk]: https://cloud.google.com/sdk/
