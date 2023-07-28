<?php

$subcommands = [
    "create" => function(){
        global $application;
        global $argv;
        global $workingDir;

        $force = false;

        $serviceLocator = $application->getServiceManager();
        $settings = $serviceLocator->get('ApplicationConfig')['connection'];
        
        $filename = ($argv[3] ?? ("{$settings['dbname']}_dump_".(new DateTime())->format('Y-m-d_H.i.s').".sql.gz"));

        if(substr($filename, 0, 1 ) !== "/"){ // Relative path
            $filename = $workingDir . '/' . $filename;
        }

        
        echo("Creating database dump to location: $filename\n");
        $conn = '';
        if(key_exists('unix_socket', $settings)){
            $conn = '-S'.escapeshellarg($settings['unix_socket']);
        }elseif(key_exists('host', $settings)){
            $conn = '-h'.$settings['host'];
        }

        if(file_exists($filename)&&(!$force)){
            $answer = readline('The file "' . $filename . '" already exists. It will be overwritten even if the dump fails. Are you sure? > ');
            if(!(($answer=='y')||($answer=='y'))){
                print_error("Cancelled");
            }
        }

        $escapedFilename = escapeshellarg($filename);
        
        $cmd = ("bash -c \"set -o pipefail -o errexit; mysqldump $conn -u{$settings['user']} -p{$settings['password']} {$settings['dbname']} | gzip > ".$escapedFilename) . '"';
        $output = [];
        $exitCode = -1;
        $result = exec($cmd, $output, $exitCode);
        
        if($exitCode==0){
            echo("Dump created: $filename (".human_filesize(filesize($filename)).")\n");
            echo "Plese create a copy of the files manually! Only database dumps are supported at the time\n";
        }else{
            print_error("Error creating dump");
        }

    }
];