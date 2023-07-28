<?php

function get_local_modules(){
    global $application;
    $serviceLocator = $application->getServiceManager();
    $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
    return $moduleManager->getModules();
}
function get_local_module($module_name, $reload=false){
    global $application;
    $serviceLocator = $application->getServiceManager();

    if($reload){
        $factory = new Omeka\Service\ModuleManagerFactory();
        $moduleManager = $factory->__invoke($serviceLocator, '');
    }else{
        $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
    }
    $modules = $moduleManager->getModules();
    return($modules[$module_name]);
}
function module_is_installed($module_name){
    return (bool)(get_local_module($module_name));
}
function module_download($module, $api_module=null){
    $module_name = $module->getName();
    if($api_module==null)
        $api_module = $api_modules[$module_name];

    $download_url = $api_module['versions'][$api_module['latest_version']]['download_url'];
    if($download_url){
        download_unzip($download_url, OMEKA_PATH.'/modules/');
    }else{
        print_error("Could not download module");
    }
}
function module_install($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        $moduleManager->install($module);
    } catch (Exception $e) {
        print_error("Could not install module: \n" . $e->getMessage());
    }
}
function module_uninstall($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        $moduleManager->uninstall($module);
    } catch (Exception $e) {
        print_error("Could not uninstall module: \n" . $e->getMessage());
    }
}
function module_enable($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        $moduleManager->activate($module);
    } catch (Exception $e) {
        print_error("Could not enable module: \n" . $e->getMessage());
    }
}
function module_disable($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        $moduleManager->deactivate($module);
    } catch (Exception $e) {
        print_error("Could not disable module: \n" . $e->getMessage());
    }
}
function module_update($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        $moduleManager->upgrade($module);
    } catch (Exception $e) {
        throw new Exception("Could not perform database update for the module: \n" . $e->getMessage());
    }
}
function module_delete($module){
    global $application;
    $services = $application->getServiceManager();
    $moduleManager = $services->get('Omeka\ModuleManager');

    try {
        if($module->getState()=='active' || $module->getState()=='not_active'){
            print_error('The module can\'t be removed because it seems to be installed.');
        }
        $path = dirname($module->getModuleFilePath());
        if(empty($path)||$path=='/'||!(str_contains($path, 'modules')))
            print_error('Incorrect or dangerous path detected. Please remove the folder manually.');
        if(!$force){
            $answer = readline('Are you sure that you want to delete the directory "' . $path . '" and its contents? > ');
            if(($answer=='y')||($answer=='y')){
                system("rm -rf ".escapeshellarg($path));
            }

        }
    } catch (Exception $e) {
        print_error("Could not delete module: \n" . $e->getMessage());
    }
}

$subcommands = [
    "list" => function(){
        global $application;
        $mode = 'human';
        $format = 'table';
        $services = $application->getServiceManager();
        $omekaModules = $services->get('Omeka\ModuleManager');
        $modules = $omekaModules->getModules();
        $api_modules = get_module_list_from_api();
        $modules_array = [];
        foreach ($modules as $key => $module) {
            $modules_array[$key] = [
                'id' => $module->getId(),
                'name' => $module->getName(),
                'state' => $module->getState(),
                'ini' => $module->getIni(),
                'db' => $module->getDb(),
                'api' => $api_modules[$key],
                'moduleFilePath' => $module->getModuleFilePath(),
                'isConfigurable' => $module->isConfigurable(),
            ];
        }
        if($mode == 'raw'){
            output($modules_array, $format);
        }else{
            $result_array = [];
            foreach ($modules_array as $key => $module) {
                $result_array[] = [
                    'id' => $module['id'],
                    'name' => $module['name'],
                    'state' => $module['state'],
                    'version' => ($module['db']['version']==$module['ini']['version']||!$module['db']['version'])?$module['ini']['version']:($module['ini']['version'].' ('.$module['db']['version'].' in database)')??'',
                    'upgrade_available' => ($module['ini']?($module['ini']['version']!=$module['api']['latest_version']?$module['api']['latest_version']:'up to date'):'')??'',

                ];
            }
            output($result_array, $format);
        }
    },
    "download" => function(){
        global $argv;
        $module = $argv[3];

        $short_options = "f";
        $long_options = ["force"];
        $options = getopt($short_options, $long_options);
        

        $force = (isset($options["f"]) || isset($options["force"]));
        $force = true;
        $version_to_download = '';

        if(empty($module)){
            print_error('Module not specified. Please specify a module ID or URL');
        }

        if(str_starts_with($module, 'http')){ // URL
            $download_url = $module;
        }else{
            $api_modules = get_module_list_from_api();
            if(!key_exists($module, $api_modules)){
                print_error('Module not found in the official module list. Please specify a valid module ID or the URL for a custom module. Available modules are:', false);
                $module_list = [];
                foreach ($api_modules as $key => $module) {
                    $module_list[] = [
                        'ID' =>  $module['dirname'],
                        'Latest version' => $module['latest_version'],
                        'Owner' => $module['owner'],
                    ];
                }
                print_error(output($module_list, 'table', true));
            }else{
                if(module_is_installed($module) && !$force){
                    print_error('The module seems to be already downloaded. Use the flag --force in order to download it anyway.');
                }
                if(empty($version_to_download))
                    $version_to_download = $api_modules[$module]['latest_version'];
                $download_url = $api_modules[$module]['versions'][$version_to_download]['download_url'];
            }

        }
        
        // Download and unzip
        download_unzip($download_url, OMEKA_PATH.'/modules/');
        
    },
    "install" => function(){
        global $argv;
        $module_name = $argv[3];
        $module = get_local_module($module_name);

        elevate_privileges();
        
        if(!$module){
            echo("Module not found. Trying to download it...");
            $download_url = $api_modules[$module]['versions'][$api_modules[$module]['latest_version']]['download_url'];
            if($download_url){
                download_unzip($download_url, OMEKA_PATH.'/modules/');
            }else{
                print_error("Could not download module");
            }
        }
        elseif(($module->getState()=='active')||($module->getState()=='not_active')){
            print_error('The module seems to be already installed');
        }
        elseif($module->getState()!='not_installed'){
            print_error('The module cannot be installed because its status is: '.$module->getState());
        }
        
        // Downloaded and can be installed. Install
        module_install($module);
    },
    "uninstall" => function(){
        global $argv;
        $module_name = $argv[3];
        $module = get_local_module($module_name);

        elevate_privileges();
        
        if(!$module){
            print_error("Module not found");
        }
        elseif(($module->getState()=='not_installed')||($module->getState()=='invalid_ini')){
            print_error('The module does not seem to be installed');
        }else{
            // Uninstall the module
            module_uninstall($module);
        }
    },
    "update-db" => function(){
        global $argv;
        $module_name = $argv[3];
        $module = get_local_module($module_name);

        elevate_privileges();
        
        if(!$module){
            print_error("Module not found");
        }
        elseif($module->getState()!='needs_upgrade'){
            print_error('The module does not seem to need a database update');
        }else{
            // Update the module database (upgrade)
            module_update($module);
        }

    },
    "delete" => function(){
        global $argv;

        $force = false;

        $module_name = $argv[3];
        $module = get_local_module($module_name);
        

        elevate_privileges();
        
        if(!$module){
            print_error("Module not found");
        }else{
            if($module->getState()=='active' || $module->getState()=='not_active'){
                if(!$force){
                    $answer = readline('The module will be uninstalled in order to delete it. That will erase any settings or information from the module "' . $module_name . '". Are you sure about this? > ');
                    if(($answer=='y')||($answer=='y')){
                        // Uninstall the module
                        module_uninstall($module);
                    }
                }
            }
            // Delete
            module_delete($module);
        }
    },
    "update" => function(){
        global $argv;

        if(count($argv)<4){
            print_error("No module specified");
        }

        $module_names = array_slice($argv, 3);
        $several = false;
        $modules = [];
        $local_modules = get_local_modules();
        elevate_privileges();

        if($module_names[0]=='--all'){
            $several = true;
            $modules = $local_modules;
        }else{
            $several = (count($module_names)>1);
            foreach ($module_names as $module_name) {
                $modules[] = $local_modules[$module_name];
            }
        }
        
        $api_modules = get_module_list_from_api();

        foreach ($modules as $module) {
            
            $api_module = $api_modules[$module->getId()];

            if(!$module){
                if(!$several)
                    print_error("Module not found");
                else
                    continue;
            }

            if($api_module){
                if($module->getState()=='needs_upgrade'){
                    module_update($module);
                }
                elseif($module->getIni()['version']==$api_module['latest_version']){
                    if(!$several)
                        print_error('The module does not seem to have available updates');
                else
                    continue;
                }else{
                    // Download latest version and upgrade the module
                    module_download($module, $api_module);
                    echo("Newest version of the module '" . $module->getId() . "' downloaded and extracted.\n");
                    $module2 = get_local_module($module->getId(), true);
                    // $ini = $module->getIni();
                    // $ini['version'] = 
                    // $module->setState('needs_upgrade');
                    // var_dump($module2->getIni());
                    // die;
                    // $module->setIni($module2->getIni());
                    try {
                        if($module2->getState()=='invalid_omeka_version'){
                            print_error("The latest version ({$module2->getIni()['version']}) of the module is not compatible with the current version of Omeka S. Supported version contraint is: {$module2->getIni()['omeka_version_constraint']}", false);
                        }elseif($module2->getState()=='needs_upgrade'){
                            module_update($module2);
                            echo("Module {$module->getId()} upgraded.\n");
                        }
                    } catch (\Throwable $e) {
                        print_error($e->getMessage(), false);
                    }
                }
            }
        }
    },
    "enable" => function(){
        global $argv;
        $module_name = $argv[3];
        $module = get_local_module($module_name);

        elevate_privileges();
        
        if(!$module){
            echo("Module not found. Trying to download it...");
            $download_url = $api_modules[$module]['versions'][$api_modules[$module]['latest_version']]['download_url'];
            if($download_url){
                download_unzip($download_url, OMEKA_PATH.'/modules/');
            }else{
                print_error("Could not download module");
            }
            // It didn't exist locally. Install
            module_install($module);
        }
        elseif(($module->getState()=='active')){
            print_error('The module seems to be already active');
        }
        elseif(($module->getState()!='not_installed')&&($module->getState()!='not_active')){
            print_error('The module cannot be installed because its status is: '.$module->getState());
        }else{
            // Enable the module
            module_enable($module);
        }
    },
    "disable" => function(){
        global $argv;
        $module_name = $argv[3];
        $module = get_local_module($module_name);

        elevate_privileges();
        
        if(!$module){
            print_error("Module not found");
        }
        elseif($module->getState()!='active'){
            print_error('The module does not seem to be active');
        }else{
            // Disable the module
            module_disable($module);
        }
    },
    "status" => function(){
        global $application;
        global $argv;
        $format = 'table';
        $services = $application->getServiceManager();
        $omekaModules = $services->get('Omeka\ModuleManager');
        $modules = $omekaModules->getModules();

        if(key_exists($argv[3], $modules)){
            $module = $modules[$argv[3]];
            $api_modules = get_module_list_from_api();
            
            output([[
                'id' => $module->getId(),
                'name' => $module->getName(),
                'state' => $module->getState(),
                'version' => ($module->getDb()['version']==$module->getIni()['version']||!$module->getDb()['version'])?$module->getIni()['version']:($module->getIni()['version'].' ('.$module->getDb()['version'].' in database)')??'',
                'upgrade_available' => ($module->getIni()?($module->getIni()['version']!=$api_modules[$key]['latest_version']?$api_modules[$key]['latest_version']:'up to date'):'')??'',
                'moduleFilePath' => $module->getModuleFilePath(),
                'isConfigurable' => $module->isConfigurable(),
            ]], $format);
        }else{
            echo("Module {$argv[3]} not found\n");
            exit(1);
        }
        
    },
];
