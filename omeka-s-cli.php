#!/usr/bin/env php
<?php
/* If you are using xdebug, you can use the following shebang to avoid warnings printing:
#!/usr/local/bin/php -d xdebug.log_level=0
*/
require 'includes.php';


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
