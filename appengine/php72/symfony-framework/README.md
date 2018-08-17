## Symfony

> [Symfony][symfony] is a set of PHP Components, a Web Application framework, a
> Philosophy, and a Community — all working together in harmony.
>
> – symfony.com

You can check out [PHP on Google Cloud Platform][php-gcp] to get an
overview of PHP itself and learn ways to run PHP apps on Google Cloud
Platform.

## Prerequisites

1. [Create a project][create-project] in the Google Cloud Platform Console
   and make note of your project ID.
1. [Enable billing][enable-billing] for your project.
1. Install the [Google Cloud SDK](https://cloud.google.com/sdk/).

## Install

This tutorial uses the [Symfony Demo][symfony-demo] application. Run the
following command to install it:

```sh
composer create-project symfony/symfony-demo:^1.2
```

## Run

1. Run the app with the following command:

        php bin/console server:run

1. Visit [http://localhost:8000](http://localhost:8000) to see the Symfony
Welcome page.

## Deploy

1.  Copy the [`app.yaml`](app.yaml) file from this repository into the root of
    your project and replace `YOUR_APP_SECRET` with a new secret or the generated
    secret in `.env`:

    ```yaml
    runtime: php72

    env_variables:
        APP_ENV: prod
        APP_SECRET: YOUR_APP_SECRET

    # URL handlers
    # ...
    ```

1. Set the cache and log directories to `/tmp` in production. This is because
   App Engine's file system is **read-only**. Modify the functions `getCacheDir`
   and `getLogDir` to the following in `src/Kernel.php`:


    ```php
    class Kernel extends BaseKernel
    {
        //...

        public function getCacheDir()
        {
            if ($this->environment === 'prod') {
                return sys_get_temp_dir();
            }
            return $this->getProjectDir() . '/var/cache/' . $this->environment;
        }
        public function getLogDir()
        {
            if ($this->environment === 'prod') {
                return sys_get_temp_dir();
            }
            return $this->getProjectDir() . '/var/log';
        }

        // ...
    }
    ```

1. Deploy your application to App Engine:

        gcloud app deploy

1. Visit `http://YOUR_PROJECT_ID.appspot.com` to see the Symfony demo landing
   page.

## Connect to Cloud SQL with Doctrine

Next, connect your symfony demo application with a [Cloud SQL][cloudsql]
database. This tutorial uses the database name `symfonydb` and the username
`root`, but you can use whatever you like.

### Setup

1. Follow the instructions to set up a
   [Google Cloud SQL Second Generation instance for MySQL][cloudsql-create].

1. Create a database for your Symfony application. Replace `INSTANCE_NAME`
   with the name of your instance:

       gcloud sql databases create symfonydb --instance=INSTANCE_NAME

1. Enable the [CloudSQL APIs][cloudsql-apis] in your project.

1. Follow the instructions to
   [install and run the Cloud SQL proxy client on your local machine][cloudsql-install].
   The Cloud SQL proxy is used to connect to your Cloud SQL instance when
   running locally.

    * Use the [Cloud SDK][cloud_sdk] from the command line to run the following
      command. Copy the `connectionName` value for the next step. Replace
      `INSTANCE_NAME` with the name of your instance:

          gcloud sql instances describe INSTANCE_NAME

    * Start the Cloud SQL proxy and replace `INSTANCE_CONNECTION_NAME` with
      the connection name you retrieved in the previous step:

          cloud_sql_proxy -instances=INSTANCE_CONNECTION_NAME=tcp:3306 &

    **Note:** Include the `-credential_file` option when using the proxy, or
    authenticate with `gcloud`, to ensure proper authentication.

### Configure

1.  Modify your Doctrine configuration in `config/packages/doctrine.yml` and
    change the parameters under `doctrine.dbal` to be the following:

    ```yaml
    # Doctrine Configuration
    doctrine:
        dbal:
            driver: pdo_mysql
            url: '%env(resolve:DATABASE_URL)%'

        # ORM configuration
        # ...
    ```

1.  Use the symfony CLI to connect to your instance and create a database for
    the application. Be sure to replace `DB_PASSWORD` with the root password you
    configured:

        # create the database using doctrine
        DATABASE_URL="mysql://root:DB_PASSWORD@127.0.0.1:3306/symfonydb" \
            bin/console doctrine:schema:create

1.  Modify your `app.yaml` file with the following contents. Be sure to replace
    `DB_PASSWORD` and `INSTANCE_CONNECTION_NAME` with the values you created for
    your Cloud SQL instance:

    ```yaml
    runtime: php72

    env_variables:
      APP_ENV: prod
      APP_SECRET: YOUR_APP_SECRET

      # Add the DATABASE_URL environment variable
      DATABASE_URL: mysql://root:DB_PASSWORD@localhost?unix_socket=/cloudsql/INSTANCE_CONNECTION_NAME;dbname=symfonydb

    # URL handlers
    # ...
    ```

### Run

1.  Now you can run locally and verify the connection works as expected.

        DB_HOST="127.0.0.1" DB_DATABASE=symfony DB_USERNAME=root DB_PASSWORD=YOUR_DB_PASSWORD \
            php bin/console server:run

1.  Reward all your hard work by running the following command and deploying
    your application to App Engine:

        gcloud app deploy

### What's Next

1. Check out the [Databases and the Doctrine ORM][symfony-doctrine] documentation for Symfony.
1. View a [Symfony Demo Application][symfony-sample-app] for App Engine Flex.

[php-gcp]: https://cloud.google.com/php
[cloud_sdk]: https://cloud.google.com/sdk/
[cloudsql]: https://cloud.google.com/sql/docs/
[cloudsql-create]: https://cloud.google.com/sql/docs/mysql/create-instance
[cloudsql-install]: https://cloud.google.com/sql/docs/mysql/connect-external-app#install
[cloudsql-apis]:https://pantheon.corp.google.com/apis/library/sqladmin.googleapis.com/?pro
[create-project]: https://cloud.google.com/resource-manager/docs/creating-managing-projects
[enable-billing]: https://support.google.com/cloud/answer/6293499?hl=en
[php-gcp]: https://cloud.google.com/php
[symfony]: http://symfony.com
[symfony-install]: http://symfony.com/doc/current/setup.html
[symfony-welcome]: https://symfony.com/doc/current/_images/welcome.png
[composer-json]: https://storage.googleapis.com/gcp-community/tutorials/run-symfony-on-appengine-flexible/composer-json.png
[symfony-doctrine]: https://symfony.com/doc/current/doctrine.html
[symfony-sample-app]: https://github.com/bshaffer/symfony-on-app-engine-flex
[symfony-demo]: https://github.com/symfony/demo
