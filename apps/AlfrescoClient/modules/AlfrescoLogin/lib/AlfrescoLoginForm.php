<?php   
class AlfrescoLoginForm extends sfFormSymfony   
{
  public function configure()
  {
      $context = $context = sfContext::getInstance();

    $this->setWidgets(array(
      'username' => new sfWidgetFormInput(), 
      'password' => new sfWidgetFormInputPassword(),
      'ticket' => new sfWidgetFormInputHidden() 
    ));

    $this->widgetSchema->setLabel('username', $context->getI18N()->__("Username", null, 'messages'));
    $this->widgetSchema->setLabel('password', $context->getI18N()->__("Password", null, 'messages'));
    
    $this->setValidators(array(
      'username' => new sfValidatorString(array('min_length' => 1), array('required' =>  $context->getI18N()->__('Please provide your username.'))), 
      'password' => new sfValidatorString(array('min_length' => 1), array('required' =>  $context->getI18N()->__('Please provide your password.')))
    ));
    
    
    $this->widgetSchema->setNameFormat('login[%s]');
    
    
    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', false);


    
    sfWidgetFormSchema::setDefaultFormFormatterName('AlfrescoLogin');
    /*$this->getWidgetSchema()->setHelps(array('username' => 'Please enter your username',
                                                 'password' => 'Please enter your password'
                                           )
    );*/
    
    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'checkUserdata')))
    );
    
  }
  public function checkUserdata($validator, $values) 
  {  
      $context = $context = sfContext::getInstance();
      
    $errors = array();
    
    if (!empty($values['username']) && !empty($values['password']))
    {           
        $repositoryUrl = AlfrescoConfiguration::getInstance()->RepositoryUrl;                 
        $userName = $values['username'];
        $password = $values['password'];
        $alfrescoLogin = new RESTAuthentication($repositoryUrl);
                
        $ticket = $alfrescoLogin->login($userName,$password);
        
        if ($ticket != null && !empty($ticket)) {
            
            $success = true;
        } 
        else {
            $success = false;
           
        }   
        
       if ($success == false) 
            $errors["username"] = new sfValidatorError($validator, $context->getI18N()->__('Invalid username or password'));
    }
    
    if (count($errors) > 0)
        throw new sfValidatorErrorSchema($validator, $errors); 
    
    return $values;
  }
  
}

?>