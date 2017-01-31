<?php

class AlfrescoSpaceCreateForm extends sfForm {
  public function configure()
  {
      
    if (!$user = $this->getOption('userEl'))
        return;
         
    /*$repositoryUrl = "http://192.168.254.128:8080/alfresco/api";
    $userName = "admin";
    $password = "admin";

    $repository = new Repository($repositoryUrl);
    $ticket = $repository->authenticate($userName, $password);
    $session = $repository->createSession($ticket);  */
    //$user = $this->getUser();                
    $repository = $user->getRepository();
    $session = $user->getSession();
    $ticket = $user->getTicket();

    $spacesStore = new SpacesStore($session);
    
    $namespaceMap = new NamespaceMap();

    $companyHome = $spacesStore->companyHome;  
    
    $this->widgets = array();
    $this->widgetsValues = array();

    $elements = array("cm_name"=>array("type"=>"text","title"=>"Name"),"cm_title"=>array("type"=>"text","title"=>"Title"),"cm_description"=>array("type"=>"text","title"=>"Description"));
    
    foreach ($elements as $key => $value) {
        $type = $value["type"];
        $title = $value["title"];
        switch ($type) {
            case "text":
                $widget = new sfWidgetFormInputText();
            break;
            case "mltext":
                $widget = new sfWidgetFormInputText();
            break;
            case "int":
                $widget = new sfWidgetFormInputText();
            break;
            case "long":
                $widget = new sfWidgetFormInputText();
            break;
            case "float":
                $widget = new sfWidgetFormInputText();
            break;
            case "double":
                $widget = new sfWidgetFormInputText();
            break;
            case "date":
                $widget = new sfWidgetFormDate(array('format' => '%year% - %month% - %day%'));
            break;
            case "datetime":
                $widget = new sfWidgetFormDateTime();
            break;
            case "boolean":
                $widget = new sfWidgetFormInputCheckbox();
            break;
        }
        
        $widget->setLabel($title);
        
        $widget->setAttribute("id",$key);
        //$widget->setAttribute("name",$key);
        $widget->setAttribute("class","metaDataInput");
        
        $this->widgets[$key] = $widget;
    }
    
    
    $this->setWidgets($this->widgets);
    $this->widgetSchema->setNameFormat('properties[%s]');
    
  }
}
?>