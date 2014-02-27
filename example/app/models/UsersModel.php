<?php

    // Users Model Class

    class UsersModel extends AppModel
    {
        
        public $validators = array(
            'email' => array (
                'checkEmail' => array (
                    'msg' => 'Please supply a valid email address.',
                    'code' => 'invalidEmail',
                ),
            ),
            'passwd' => array (
                'checkPassword' => array (
                    'msg' => 'Password should be 8-20 characters and should contain [a-zA-z] and [0-9] or a special character.',
                    'code' => 'invalidPasswd',
                ),
            ),
            'name' => array (
                'notEmpty' => array (
                    'msg' => 'Please supply a valid Name.',
                    'code' => 'emptyName',
                ),
            ),
            'phone' => array (
                'notEmpty' => array (
                    'msg' => 'Please supply a valid Phone Number.',
                    'code' => 'emptyPhone',
                ),
            ),
            'college' => array (
                'notEmpty' => array (
                    'msg' => 'Please supply a valid College.',
                    'code' => 'emptyCollege',
                ),
            ),
            'state' => array (
                'notEmpty' => array (
                    'msg' => 'Please supply a valid State.',
                    'code' => 'emptyName',
                ),
            ),
        );
        
    }
    
    
?>