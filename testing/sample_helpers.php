<?php

namespace Google\Cloud\Samples;

use ReflectionFunction;

function execute_sample(string $file, string $namespace, ?array $argv)
{
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
    $functionName = sprintf('%s\\%s', $namespace, basename($file, '.php'));

    // Verify the user has supplied the correct number of arguments
    $functionReflection = new ReflectionFunction($functionName);
    if (
        count($argv) < $functionReflection->getNumberOfRequiredParameters()
        || count($argv) > $functionReflection->getNumberOfParameters()
    ) {
        print(get_usage(basename($file), $functionReflection));
        return;
    }

    // Require composer autoload for the user
    $autoloadDir = dirname(dirname($functionReflection->getFileName()));
    if (!file_exists($autoloadFile = $autoloadDir . '/vendor/autoload.php')) {
        printf(
            'You must run "composer install" in the sample root (%s/)' . PHP_EOL,
            $autoloadDir
        );
        return;
    }
    require_once $autoloadFile;

    // If any parameters are typehinted as "array", explode user input on ","
    $validArrayTypes = ['array', 'array<string>', 'string[]'];
    $parameterReflections = $functionReflection->getParameters();
    foreach (array_values($argv) as $i => $val) {
        $parameterReflection = $parameterReflections[$i];
        if ($parameterReflection->hasType()) {
            $parameterType = $parameterReflection->getType()->getName();
            if (in_array($parameterType, $validArrayTypes)) {
                $argv[$i] = explode(',', $argv[$i]);
            }
        }
    }

    // Run the function
    return call_user_func_array($functionName, $argv);
}

function get_usage(string $file, ReflectionFunction $functionReflection)
{
    // Print basic usage
    $paramNames = [];
    foreach ($functionReflection->getParameters() as $param) {
        $name = '$' . $param->getName();
        if ($param->isOptional()) {
            $default = var_export($param->getDefaultValue(), true);
            $name = "[$name=$default]";
        }
        $paramNames[] = $name;
    }
    $usage = sprintf('Usage: %s %s' . PHP_EOL, $file, implode(' ', $paramNames));

    // Print @param docs if they exist
    preg_match_all(
        "#(@param+\s*[a-zA-Z0-9, ()_].*)#",
        $functionReflection->getDocComment(),
        $matches
    );
    if (isset($matches[0])) {
        $usage .= PHP_EOL . "\t";
        $usage .= implode(PHP_EOL . "\t", $matches[0]) . PHP_EOL;
        $usage .= PHP_EOL;
    }

    return $usage;
}
