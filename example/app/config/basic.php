<?php

    /**
     * Basic App Configuration
    **/
    
    global $config;

    $config = array (
                        'mode' => 0, //0 => Develop | 1 => Test | 2 => Live
                        'error_reporting' => array (
                                                        E_ALL,
                                                        E_ALL & ~E_NOTICE,
                                                        0
                                                    ),
                        'scribe' => true, // Use Scribe Handler or not
                        'curl' => true, // Enable cURL
                        'salt' => './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', // 64 Character SALT
                        'salt_blowfish' => './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', // 64 Character SALT (More Secure)
                        'cipher' => 'bYHJSfw5jtGZDWd8akqNMesCnA2up10OEz4vXyLVxTlQ73RoU6KF9hcIiBPrmg', // Used for generating random Alphanumeric IDs from numbers
                        'ajaxProfiling' => false, // Typecasts Ajax output into an array and appends the profiler content into it as the variable SimplePHP
                        'name' => 'My Test App', // Application Name
                        'schemaCachingDuration' => 3600,
                        'mail' => array (
                                        'isSMTP' => true,
                                        'config' => array (
                                                            array (
                                                                'host' => 'ssl://smtp.gmail.com', // SMTP Host
                                                                'port' => 465, // SMTP Port
                                                                'name' => 'My Test App', // Sender's Name
                                                                'username' => '<your_username>', // SMTP Username
                                                                'passwd' => '<your_password>', // SMTP Password
                                                                'sender' => 'notifications@mytestapp.com' // Sender's Email
                                                            ),
                                                            array (
                                                                'name' => 'My Test App', // Sender's Name
                                                                'sender' => 'notifications@mytestapp.com' // Sender's Email
                                                            )
                                                        )
                                                            
                        
                        ),
                        'cache' => array (
                                            'enabled' => 1, // 0 => Disabled | 1 => Enabled
                                            'servers' => array (
                                                            array( 'host' => 'localhost',
                                                                    'port' => 11211
                                                            )
                                                        ),
                                            'salt' => null, // Salt to be used for storing queries in case of shared server
                                            'exitFlush' => true // Flush session cache on exit
                                            )
                );
    
?>