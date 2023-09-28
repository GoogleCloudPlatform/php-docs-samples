This application demonstrates the usage of the Analytics Data API using
OAuth2 credentials.

Please familiarize yourself with the OAuth2 flow guide at
https://developers.google.com/identity/protocols/oauth2

For more information on authenticating as an end user, see
https://cloud.google.com/docs/authentication/end-user

In a nutshell, you need to:

1. Create your OAuth2 client credentials in Google Cloud Console.
Choose "Web application" when asked for an application type.
https://support.google.com/cloud/answer/6158849

2. When configuring the web application credentials, add
"http://localhost:3000/" to "Authorized redirect URIs".

3. Download a credentials file using "Download JSON" button in the credentials
configuration dialog and save it as `oauth2.keys.json` in the same
directory with this sample app.

4. Replace `$property_id` variable with the value of the Google Analytics 4
property id you want to access.

5. Install the PHP bcmath extension (due to https://github.com/protocolbuffers/protobuf/issues/4465):

    ```
    sudo -s apt-get install php-bcmath
    ```

6. Run the following commands from the current directory in order to install
dependencies and run the sample app:

    ```
    composer update
    php -S localhost:3000 -t .
    ```

7. In a browser, open the following url to start the sample:

http://localhost:3000/
