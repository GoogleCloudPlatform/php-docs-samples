# Custom Extensions for App Engine Standard

This sample shows how to compile custom extensions for PHP that aren't already included in
the [activated extensions](https://cloud.google.com/appengine/docs/standard/php-gen2/runtime#enabled_extensions) 
or [dynamically loadable extensions](https://cloud.google.com/appengine/docs/standard/php-gen2/runtime#dynamically_loadable_extensions).

This can be useful for activating extensions such as [sqlsrv](https://pecl.php.net/package/sqlsrv) which are not (yet) supported
by this runtime.

## Steps to compiling and activating custom extensions

1. Put the custom extension code in a directory in your project, so it gets uploaded with
the rest of your application. In this example we use the directory named `ext`.

2. Put the commands to compile the extension and move it into the `vendor` directory
in your `composer.json`. 

```json
{
    "scripts": {
        "post-autoload-dump": [
            "cd ext && phpize --clean && phpize && ./configure && make",
            "cp ext/modules/sqlsrv.so vendor/"
        ]
    }
}
```
**NOTE**: Moving the extension into the `vendor` directory ensures the file is cached. This
means if you modify the ext directory, you'll need to run gcloud app deploy with the
`--no-cache argument` to rebuild it.

3. Activate the extension in your `php.ini`:
```ini
# php.ini
extension=/workspace/vendor/my_custom_extension.so
```

4. Deploy your application as usual with `gcloud app deploy`. In this example, we use `index.php`
to print `phpinfo()` so we can see that the extension has been activated.
