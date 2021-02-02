<?php

// Static list provides security against URL injection by default.
$routes = [
    'spanner',
    'monitoring',
    'speech',
];

// Keeping things fast for a small number of routes.
$regex = '/\/(' . join($routes, '|') . ')\.php/';
if (preg_match($regex, $_SERVER['REQUEST_URI'], $matches)) {
    $file_path = __DIR__ . $matches[0];
    if (file_exists($file_path)) {
        require($file_path);
        return;
    }
}

// The homepage is the default behavior.
// By checking for $_SERVER['REQUEST_URI'] == '/' this could be extended
// to include a file not found page.
?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Google Cloud Platform | App Engine Standard gRPC Examples</title>
  </head>

  <body>
    <h1>gRPC Examples</h1>

    <ul>
      <li><a href="/spanner.php">Call Cloud Spanner with gRPC</a></li>
      <li><a href="/monitoring.php">Call Cloud Monitoring with gRPC</a></li>
      <li><a href="/speech.php">Make a gRPC streaming call to the Cloud Speech API</a></li>
    </ul>
  </body>
</html>
