# Stackdriver Logging v2 API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=logging

This directory contains samples for calling [Stackdriver Logging][logging]
from PHP.

Execute the snippets in the [src/](src/) directory by running
`php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
are provided:
```sh
$ php src/list_entries.php
Usage: php src/list_entries.php PROJECT_ID LOGGER_NAME

$ php src/list_entries.php your-project-id 'your-logger-name'
[list of entries...]
```

To use logging sinks, you will also need a Google Cloud Storage Bucket.

    gcloud storage buckets create gs://[YOUR_PROJECT_ID]

You must add Cloud Logging as an owner to the bucket. To do so, add
`cloud-logs@google.com` as an owner to the bucket. See the
[exporting logs](https://cloud.google.com/logging/docs/export/configure_export#configuring_log_sinks)
docs for complete details.

# Running locally

Use the [Cloud SDK](https://cloud.google.com/sdk) to provide authentication:

    gcloud beta auth application-default login

[logging]: https://cloud.google.com/logging/docs/reference/libraries
