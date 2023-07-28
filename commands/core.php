<?php
$subcommands = [
    "latest" => function(){
        output(get_omeka_latest_version());
    },
    "version" => function(){
        global $application;
        $serviceLocator = $application->getServiceManager();
        $settings = $serviceLocator->get('Omeka\Settings');
        output($settings->get('version'));
        
    },
    "upgrade" => function(){
        global $application;
        global $argv;
        $force = ($argv[3]=='-f'||$argv[3]=='--force');
        $serviceLocator = $application->getServiceManager();
        $settings = $serviceLocator->get('Omeka\Settings');
        $currentVersion = $settings->get('version');
        $latestVersion = trim(get_omeka_latest_version());

        echo("Current version: $currentVersion\n");
        echo("Latest version: $latestVersion\n");

        if(($latestVersion==$currentVersion)&&(!$force)){
            print_error("Omeka S core seems up to date");
        }

        if(!$force){
            $answer = readline("Do you want to download and install version $latestVersion? Please make a full backup before proceeding and only do this if you know what you're doing. > ");
            if(!(($answer=='y')||($answer=='y'))){
                print_error("Cancelled");
            }
        }
        
        $DOWNLOAD_URL = "https://github.com/omeka/omeka-s/releases/download/v$latestVersion/omeka-s-$latestVersion.zip";
        $tempDir = sys_get_temp_dir()."/omeka-s-upgrade/$latestVersion/";
        echo("Downloading and extracting to $tempDir...");
        download_unzip($DOWNLOAD_URL, $tempDir);
        
        echo("New version of Omeka S downloaded. Replacing files...");

        $escapedFilename = escapeshellarg($tempDir.'omeka-s');
        
        $cmd = ("bash -c 'set -o pipefail -o errexit; rm -Rf $escapedFilename/config && rm -Rf $escapedFilename/themes && rm -Rf $escapedFilename/modules && rm -Rf $escapedFilename/logs && cp -Rf $escapedFilename/* ".OMEKA_PATH."/'");
        system($cmd);
        $output = [];
        $exitCode = -1;
        $result = exec($cmd, $output, $exitCode);
        
        if($exitCode==0){
            echo("Omeka installed in ".OMEKA_PATH."\n");
            echo("Please check permissions manually. You might want to update modules and themes too. Try:\n\tomeka-s-cli module update --all\tomeka-s-cli theme update --all\n");
        }else{
            print_error("Error installing");
        }

    }
];