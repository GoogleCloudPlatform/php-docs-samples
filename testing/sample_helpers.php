<?php

function execute_sample(string $file, string $namespace)
{
    global $argv;

    // Return if sample file is not being executed via CLI
    if (is_null($argv)) {
        return;
    }

    // Return if sample file is being included via PHPUnit
    $argvFile = array_shift($argv);
    if ('.php' != substr($argvFile, -4)) {
        return;
    }

    // Determine the name of the function to execute
    $functionName = ($namespace ?: '') . '\\' . basename($file, '.php');

    // Verify the user has supplied the correct number of arguments
    $functionReflection = new ReflectionFunction($functionName);
    if (
        count($argv) < $functionReflection->getNumberOfRequiredParameters()
        || count($argv) > $functionReflection->getNumberOfParameters()
    ) {
        print_usage(basename($file), $functionReflection);
        return;
    }

    // Require composer autoload for the user
    $autoloadDir = dirname(dirname($file));
    if (!file_exists($autoloadFile = $autoloadDir . '/vendor/autoload.php')) {
        printf(
            'You must run "composer install" in the sample root (%s/)' . PHP_EOL,
            $autoloadDir
        );
        return;
    }
    require_once $autoloadFile;

    // Run the function
    call_user_func_array($functionName, $argv);
}

function print_usage(string $file, ReflectionFunction $functionReflection)
{
    // Print basic usage
    $paramNames = [];
    foreach ($functionReflection->getParameters() as $param) {
        $name = '$' . $param->getName();
        if ($param->isOptional()) {
            $name = "[$name]";
        }
        $paramNames[] = $name;
    }
    printf('Usage: %s %s' . PHP_EOL, $file, implode(' ', $paramNames));

    // Print @param docs if they exist
    preg_match_all(
        "#(@param+\s*[a-zA-Z0-9, ()_].*)#",
        $functionReflection->getDocComment(),
        $matches
    );
    if (isset($matches[0])) {
        print(PHP_EOL . "\t");
        print(implode(PHP_EOL . "\t", $matches[0]) . PHP_EOL);
        print(PHP_EOL);
    }
}
