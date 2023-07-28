#!/usr/local/bin/php -d xdebug.log_level=0
<?php
require 'includes.php';

#!/usr/bin/env php

if($argc<2){
    echo("No command specified. ");
    printHelp();
}
// $commandInclude = __DIR__.'/'.COMMANDS_FOLDER.'/'.$argv[1]'.php';
if(file_exists(__DIR__.'/'.COMMANDS_FOLDER.'/'.$argv[1].'.php')){
    require(__DIR__.'/'.COMMANDS_FOLDER.'/'.$argv[1].'.php');
    runSubcommand();
}else{
    echo("Command not found. ");
    printHelp();
}
