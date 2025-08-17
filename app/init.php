<?php
    session_start();
    
    require_once 'constants.php';
    require_once 'core/Controller.php';
    require_once 'functions.php';

    include 'plugins/vendor/autoload.php';

    $dirs = [
        "app/core/",
        "app/controllers/",
        "app/models/",
        "app/plugins/fox/",
    ];

    foreach($dirs as $dir) {
        foreach (glob($dir.'*.php') as $filename) {
            include_once(''.$filename.'');
        }
    }