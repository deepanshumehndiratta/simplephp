<?php

    /**
     * Set network Proxy for Application [ Supports only http proxy ]
    **/
    
    global $proxy;

    $proxy = array (
                    'type' => 0, // 0 => disabled, 1 =>  enabled
                    'username' => '',
                    'password' => '',
                    'host' => '',
                    'port' => ''
                );

?>