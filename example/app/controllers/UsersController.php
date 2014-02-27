<?php

    /**
     * Users Controller
    **/

    class UsersController extends AppController
    {
        
        function index ()
        {
            
            if (!isset ($this->args->req_args[0])) {
                
                $this->set ('title_for_layout', 'Welcome');
                $this->render ('default');
                
            } else {
                
                $this->set ('data', Model::get('admin')->fetchAll());
                $this->set ('title_for_layout', 'Viewing Administrators');
                
            }
            
        }
        
        protected function mail ()
        {
            
            $mail = $this->sendMail
            (
                array (
                    'to' => array (
                                array (
                                    'email' => 'deepanshumehndiratta@gmail.com',
                                    'name' => 'Deepanshu'
                                )
                            ),
                    'cc' => array ( 
                                array (
                                    'email' => 'contact@deepanshumehndiratta.com',
                                    'name' => 'Deepanshu Mehndiratta'
                                )
                            ),
                    'bcc' => array ( 
                                array (
                                    'email' => 'f2010455@goa.bits-pilani.ac.in',
                                    'name' => 'Mr. Mehndiratta'
                                )
                            )
                ),
                'Wassup?',
                array ('content' => 'Testing 1..2..3')
            );
            
            $this->set ('data', ($mail) ? 'Mail Successfully sent' : 'Problem with sending mail');
            
        }
    
        function _view ()
        {
            
            $data = Model::get()->fetchAll();
            
            if ($this->ajax) {
                
                $this->returnAjaxJson (array ('status' => true, 'users' => $data));
                
            } else {
                
                $title_for_layout = 'Viewing Users';
                $this->set (compact ('data', 'title_for_layout'));
                
            }
        
        }
        
        function _add() {}
        
        function add()
        {
            
            $this->data['passwd'] = $this->PasswordHash->HashPassword($this->data['passwd']);

            if(Model::get()->add($this->data)) {
                
                $this->set('smsg', 'User Added successfully');
                
            } else {
                
                $this->set('error', 'There was an error in processing your request, please try again later.');
                
            }
        }
    
    }
    
?>