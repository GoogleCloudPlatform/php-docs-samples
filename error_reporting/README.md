# Stackdriver Error Reporting

`quickstart.php` is a simple command-line program to demonstrate logging an
exception to Stackdriver Error Reporting.

To use this sample, you must first [enable the Stackdriver Error Reporting API][0]

# Running locally

Use the [Cloud SDK](https://cloud.google.com/sdk) to provide authentication:

    gcloud beta auth application-default login

Open `quickstart.php` in a text editor and replace the text `YOUR_PROJECT_ID`
with your Project ID.

Run the samples:

```sh
php quickstart.php
Exception logged to Stack Driver Error Reporting
```

View [Stackdriver Error Reporting][0] in the Cloud Console to see the logged
exception.

[0]: https://console.cloud.google.com/flows/enableapi?apiid=clouderrorreporting.googleapis.com
[1]: https://console.cloud.google.com/errors