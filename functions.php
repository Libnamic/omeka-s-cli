<?php
const COMMANDS_FOLDER = 'commands';
function printHelp(){
    echo("Available commands:\n");
    $files = glob(__DIR__.'/'.COMMANDS_FOLDER.'/*.php');
    
    foreach($files as $file) {
        $file = pathinfo($file);
        echo ('- '.$file['filename']."\n");
    }
    echo("\n");
    exit(1);
}

function runSubcommand(){
    global $application;
    global $argv;
    global $argc;
    global $subcommands;
    $subcommand = $argv[2];
    
    if($argc<3){
        echo("No subcommand specified. Available subcommands:\n");
        foreach ($subcommands as $key => $function) {
            echo '- '.$key."\n";
        }
        echo("\n");
        exit(1);
    }else{
        if($subcommands[$subcommand]){
            $subcommands[$subcommand]();
        }else{
            echo("Subcommand not found. Available subcommands:\n");
            foreach ($subcommands as $key => $function) {
                echo '- '.$key."\n";
            }
            echo("\n");
            exit(1);
        }
    }
}
function getIndexedArray($array) {
    $arrayTemp = array();
    for ($i=0; $i < count($array); $i++) { 
        $keys = array_keys($array[$i]);
        $innerArrayTemp = array();
        for ($j=0; $j < count($keys); $j++) { 

            $innerArrayTemp[$j] = $array[$i][$keys[$j]];                
        }
        array_push($arrayTemp, $innerArrayTemp);
    }
    return $arrayTemp;
}
function table_output($object){
    $column_width = 36;

    $column_widths = [];
    
    
    if(is_object($object)){
        $object = json_decode(json_encode($object), 1);
    }
    
    if(is_array(array_values($object)) && count(array_values($object))>1 && count(array_values($object)[0])<=1) // 1-dimensional
        $object = [$object];
    // print_r($object);
    // if(!array_is_list($object)){
    //     $object = array_values($object);
    //     $columns = array_keys($object);
    //     print_r($columns);
    // }else{
        if(count($object)>0 && array_key_exists(0, $object))
            $columns = array_keys($object[0]);
        else
            $columns = array_keys($object);
    // }

    // Calculate column widths
    foreach ($columns as $key => $column) {
        $column_widths[$column] = strlen($column) + 2;
    }
    foreach ($object as $row) {
        
        foreach ($columns as $key => $column) {
            if(is_array($row)&&count($row)>1)
                $value=$row[$column];
            else
                $value=$row;
            
            if(!is_string($value)){
                $value = json_encode($value);
            }
            if($column_widths[$column] < (strlen($value)+2))
                $column_widths[$column] = strlen($value) + 2;
        }
    }
    $total_width = array_sum($column_widths);


    echo(' ' . str_repeat('_', $total_width));
    echo("\n");
    echo('/ ');
    foreach ($columns as $key => $column) {;
        echo($column.str_repeat(" ", max($column_widths[$column]-strlen($column), 0)));
    }
    echo("\\\n");
    echo('|' . str_repeat('_', $total_width). " |\n");
    
    
    foreach ($object as $row) {
        echo('| ');
        foreach ($columns as $key => $column) {
            if(is_array($row)&&count($row)>1){
                $value=$row[$column];
            }else{
                $value=$row;
            }
            if(!is_string($value))
                $value = json_encode($value);
            echo($value.str_repeat(" ",max($column_widths[$column]-strlen($value), 0)));
        }
        echo("|\n");
        if($end)
            break;
    }
    echo('\\' . str_repeat('_', $total_width+1). "/\n");
}

function list_output($object){
    $n = 1;

    if(array_is_list($object)){
        foreach ($object as $o) {
            echo("$n. ");
            if(is_array($o)&&count($o)>1){
                foreach ($o as $value) {
                    if(is_array($value)||is_object($value))
                        echo(json_encode($value)."\n");
                    else
                        echo("$value\n");
                }
            }else{
                echo($o);
            }
            echo("-------------\n");
            $n++;
        }
    }else{
        foreach ($object as $key => $value) {
            echo("$n. $key\n");
            
            if(is_array($value)||is_object($value))
                echo(json_encode($value)."\n");
            else
                echo("$value\n");    
            echo("-------------\n");
            $n++;
        }
    }
}

function output($object, $format='json', $return_value = false){
    if(!$object)
        return;
    if($return_value)
        ob_start();
    if($format=='json'){
        if(is_object($object))
            $object = (array)$object;
        echo json_encode($object, true);
    }
    elseif($format=='print_r')
        print_r($object);
    elseif($format=='var_dump')
        var_dump($object);
    elseif($format=='table')
        table_output($object);
    elseif($format=='list')
        list_output($object);
    else
        print_r($object);

    if($return_value)
        return ob_get_clean();
}

function get_module_list_from_api(){
    $MODULE_LIST_API_URL = 'https://omeka.org/add-ons/json/s_module.json';
    return json_decode(file_get_contents($MODULE_LIST_API_URL), true);
}

function get_theme_list_from_api(){
    $THEME_LIST_API_URL = 'https://omeka.org/add-ons/json/s_theme.json';
    return json_decode(file_get_contents($THEME_LIST_API_URL), true);
}

function print_error($message, $die = true){
    fwrite(STDERR, $message."\n");
    if($die){
        exit(1);
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

function download_unzip($url, $destination){
    $path = tempnam(sys_get_temp_dir(), 'prefix');
    $file = fopen($path, "w+");
    if (flock($file, LOCK_EX)) {
        fwrite($file, file_get_contents($url));
        $zip = new ZipArchive;
        $res = $zip->open($path);
        if ($res === TRUE) {
            if(!$zip->extractTo($destination))
                print_error('Could not unzip the file');
            $zip->close();
        } else {
            print_error('Could not read to unzip the file');
        }
        flock($file, LOCK_UN);
    } else {
        print_error('Could not download the file');
    }
    fclose($file);
}

function elevate_privileges(){
    global $application;
    $serviceLocator = $application->getServiceManager();
    $auth = $serviceLocator->get('Omeka\AuthenticationService');
    
    $entityManager = $serviceLocator->get('Omeka\EntityManager');
    $userRepository = $entityManager->getRepository('Omeka\Entity\User');
    $identity = $userRepository->findOneBy(['id' => 1, 'isActive' => true]);
    $auth->getStorage()->write($identity);
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $arr)
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}

function get_sites(){
    global $application;
    $serviceLocator = $application->getServiceManager();
    $api = $serviceLocator->get('Omeka\ApiManager');

    return $api->search('sites', [], ['responseContent' => 'resource'])->getContent();
}
function get_site($slug){
    global $application;
    $serviceLocator = $application->getServiceManager();
    $api = $serviceLocator->get('Omeka\ApiManager');

    $sites = $api->search('sites', ['slug' => $slug], ['responseContent' => 'resource'])->getContent();
    if(count($sites)>0)
        return $sites[0];
}
function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor]. 'B';
  }
  
function get_omeka_latest_version(){
    $OMEKA_VERSION_API_URL = 'https://api.omeka.org/latest-version-s';
    return file_get_contents($OMEKA_VERSION_API_URL);
}