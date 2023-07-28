<?php
$subcommands = [
    "list" => function(){
        global $application;
        global $argv;
        $format = 'list';
        $mode = 'human';
        $serviceLocator = $application->getServiceManager();

        $setingsType = $argv[3] ?? 'global';
        if($setingsType == 'global'){
            $settings = $serviceLocator->get('Omeka\Settings');
        }elseif($setingsType == 'site'){
            $slug = $argv[4];
            $site = get_site($slug);
            if(!$site){
                print_error("Site with slug '$slug' not found");
            }
            $settings = $serviceLocator->get('Omeka\Settings\Site');
            $settings->setTargetId($site->getId());
            $settings->get('');     
        }elseif($setingsType == 'local'){
            $settings = $serviceLocator->get('Config');
            if($format=='list')
                $format = 'print_r';
            output($settings, $format);
            return;      
        }elseif($setingsType == 'application'){
            $settings = $serviceLocator->get('ApplicationConfig');
            if($format=='list')
                $format = 'print_r';
            output($settings, $format);
            return;
        }
        $settings = json_decode(json_encode((array)$settings), true);
        $settings = $settings["\0*\0cache"];

        
        if($mode == 'raw'){
            output($settings);
        }else{
            
            $humanSettings = [];
            foreach ($settings as $key => $value) {
                $value = json_encode($value);
                $humanSettings[] = [$key, $value];
            }
            output($humanSettings, $format);
        }
    }
];