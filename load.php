<?php
ini_set('error_reporting', 1);

$workingDir = exec("pwd");

if(file_exists('bootstrap.php')){
    include 'bootstrap.php';
}elseif(file_exists('/var/www/html/bootstrap.php')){
    include '/var/www/html/bootstrap.php';
}else{
    die("Could not find Omeka S install. Try running the tool from the Omeka base path.\n");
}

if( php_sapi_name() !== 'cli' ) { 
    print_error("THis program must be invoked using the CLI");
}

try {
    $application = Omeka\Mvc\Application::init(require 'application/config/application.config.php');
    try {
        $application->run();
    } catch (\Exception $e) {
        var_dump($e);
        error_log($e);
        exit(-1);
    }
} catch (\Exception $e) {
    var_dump($e);
    error_log($e);
}