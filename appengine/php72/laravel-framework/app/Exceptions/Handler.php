<?php

namespace App\Exceptions;

use Exception;
use Google\Cloud\ErrorReporting\Bootstrap;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception to Google Cloud Stackdriver Error Reporting
     *
     * For a full tutorial on deploying Laravel to Google Cloud,
     * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/appengine/php72/laravel-framework/README.md
     *
     * @param  \Exception  $exception
     * @return void
     */
    # [START error_reporting_setup_php_laravel]
    public function report(Exception $exception)
    {
        if (isset($_SERVER['GAE_SERVICE'])) {
            // Ensure Stackdriver is initialized and handle the exception
            Bootstrap::init();
            Bootstrap::exceptionHandler($exception);
        } else {
            parent::report($exception);
        }
    }
    # [END error_reporting_setup_php_laravel]

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
