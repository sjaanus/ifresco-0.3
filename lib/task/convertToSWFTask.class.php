<?php
require_once(sfConfig::get('sf_root_dir').'/apps/AlfrescoClient/lib/AlfrescoConfiguration.php');
class convertToSWFTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));
    
    //echo sfConfig::get('sf_root_dir').'/apps/AlfrescoClient/lib/AlfrescoConfiguration.php';

    $this->addArgument('nodeId', sfCommandArgument::REQUIRED, 'Alfresco Node Id');
    $this->addArgument('repositoryUrl', sfCommandArgument::REQUIRED, 'Alfresco Repository');
    $this->addArgument('userName', sfCommandArgument::REQUIRED, 'Alfresco User or Ticket');
    $this->addArgument('password', sfCommandArgument::OPTIONAL, 'Alfresco Password');

    $javaPath = AlfrescoConfiguration::getInstance()->JAVA;
    $swftoolsPath = AlfrescoConfiguration::getInstance()->SWFTools;

    $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'webmaster'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 'dev'),
        new sfCommandOption('connection', null, sfCommandOption::PARAMETER_OPTIONAL, 'the connection name', 'doctrine'),
        new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_OPTIONAL, 'Enables verbose output', false),
        
        new sfCommandOption('java', null, sfCommandOption::PARAMETER_OPTIONAL, 'java', $javaPath),
        new sfCommandOption('jODConverter', null, sfCommandOption::PARAMETER_OPTIONAL, 'jODConverter', sfConfig::get('sf_root_dir').'/lib/jodconverter/lib/jodconverter-cli-2.2.2.jar'),
        new sfCommandOption('SWFTools', null, sfCommandOption::PARAMETER_OPTIONAL, 'SWFTools', $swftoolsPath),
    ));

    $this->namespace        = 'Alfresco';
    $this->name             = 'convertToSWF';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [convertToSWF|INFO] task converts office/pdf to SWF
Call it with:

  [php symfony convertToSWF|INFO]
EOF;

  }
  
  private $session;
  private $ticket;
  private $repository;
  private $spacesStore;

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    require_once "lib/Alfresco/Service/Repository.php";
    require_once "lib/Alfresco/Service/Session.php";
    require_once "lib/Alfresco/Service/SpacesStore.php";

    require_once "lib/Alfresco/Service/Administration.php";
    
    $repositoryUrl = $arguments["repositoryUrl"];
    $nodeId = $arguments["nodeId"];
    
    $authenticateUser = false;    
    if (!empty($arguments["userName"]) && !empty($arguments["password"])) {
        $userName = $arguments["userName"];
        $password = $arguments["password"];    
        $authenticateUser = true;
    }
    else if (!empty($arguments["userName"]) && empty($arguments["password"])) {
        $ticket = $arguments["userName"];
        $authenticateUser = false;
    }
    else {
        throw new Exception("No Ticket or UserName or Password");    
    }
    
    if (!empty($options["java"]))
        $options["java"] = '"'.$options["java"].'"';
    if (!empty($options["SWFTools"]))
        $options["SWFTools"] = '"'.$options["SWFTools"].'"';
    
    //try {
        // Specify the connection details
        //$repositoryUrl = "http://192.168.254.128:8080/alfresco/api";
        
        
        // Authenticate the user and create a session
        if ($authenticateUser == true) {
            $this->repository = new Repository($repositoryUrl);
            $this->ticket = $this->repository->authenticate($userName, $password);
            $this->session = $this->repository->createSession($this->ticket);
        }
        else {
            $this->repository = new Repository($repositoryUrl);  
            $this->ticket = $arguments["userName"];
            $this->session = $this->repository->createSession($this->ticket);                 
        }

        // Create a reference to the 'SpacesStore'
        $this->store = new SpacesStore($this->session);
        if (preg_match("#workspace://version2store/(.*)#eis",$nodeId,$match)) {
            $this->store = new VersionStore($this->session);
            $nodeId = $match[1];
        }
        else if (preg_match("#workspace://SpacesStore/(.*)#eis",$nodeId,$match)) {
            $nodeId = $match[1];
        }
        
        $Node = $this->session->getNode($this->store, $nodeId);
        
        try {
            $contentData = $Node->cm_content;
            $size = 0;
            if ($contentData != null && $contentData instanceof ContentData) {
                $size = $contentData->getSize();  
            }
            
            $viewer = Doctrine::getTable('ViewerRelations')->find($nodeId);
            if ($viewer != null) {
                if ($viewer->getMd5sum() == $size) {
                    
                    return;
                }
            }
        }
        catch (Exception $e) {
            
        }

        
        if ($Node != null) {
            $content = $Node->cm_content;
            $mimetype = "";
            if ($content != null && $content instanceof ContentData)
                $mimetype = $content->getMimetype();
            
            $fileName = $Node->cm_name;
            $fileName = preg_replace("/(.*)\..*/is","$1",$fileName);
            $tempFile = $this->writeTempNode($Node);   

            $tempFileSWF = sfConfig::get('sf_cache_dir')."/$fileName.swf";
            @unlink($tempFileSWF);
            
            $ConvertNotToPDF = array("application/pdf",
                                     "image/jpg",
                                     "image/jpeg",
                                     "image/png",
                                     "image/gif",
                                     "audio/x-wav",
                                     "video/x-msvideo");
            if (!in_array($mimetype,$ConvertNotToPDF)) {         
                //$fileName = $Node->cm_name;
                //$fileName = preg_replace("/(.*)\..*/is","$1",$fileName);

                if ($fileName != null && !empty($fileName)) {
                    
                    //$tempFileIn = tempnam("/temp", $fileName.".pdf");
                    $tempFileIn = sfConfig::get('sf_cache_dir')."/$fileName.pdf";
                    
                    // windows fix
                    $tempFile = str_replace("\\","/",$tempFile);
                    $tempFileIn = str_replace("\\","/",$tempFileIn);
                    
                    if (!empty($tempFile)) {
                        shell_exec($options["java"].'java -jar "'.$options["jODConverter"].'" "'.$tempFile.'" "'.$tempFileIn.'"');
                    } 
                }
            }
            else {
                //$tempFileIn = "C:/Users/ddanninger/Documents/www/MVC/AlfrescoClient/test/test.pdf";
                $tempFileIn = $tempFile;
            }

            if (file_exists($tempFileIn)) {
                // windows fix
                $tempFileIn = str_replace("\\","/",$tempFileIn);
                $tempFileSWF = str_replace("\\","/",$tempFileSWF);
                switch ($mimetype) {
                    case "image/jpg":
                    case "image/jpeg":
                        exec($options["SWFTools"].'jpeg2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -T 9 -z');  
                    break;
                    case "image/png":
                        exec($options["SWFTools"].'png2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -T 9 -z');  
                    break;  
                    case "image/gif":
                        exec($options["SWFTools"].'gif2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -z');  
                        //echo $options["SWFTools"].'gif2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -z';
                    break;  
                    case "audio/x-wav":
                        exec($options["SWFTools"].'wav2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'"');  
                    break;
                    case "video/x-msvideo":
                        exec($options["SWFTools"].'avi2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -T 9');      
                    break;
                    default:
                        exec($options["SWFTools"].'pdf2swf "'.$tempFileIn.'" -o "'.$tempFileSWF.'" -T 9 -f');  

                    break;
                }
                
                if (file_exists($tempFileSWF)) {
                   $viewer = Doctrine_Query::create()
                          ->from('ViewerRelations v')
                          ->where('v.nodeid = ?', $nodeId)
                          ->fetchOne();
                   if ($viewer == null)       
                       $viewer = new ViewerRelations();
                       
                   $viewer->setNodeid($nodeId);
                   $viewer->setViewernode($nodeId);
                   $viewer->setViewerurl("http");
                   
                   $viewer->setMd5sum($size);

                   $viewer->setViewercontent(file_get_contents($tempFileSWF)); 
                   
                   $viewer->save();
                    
                   /*$parent = $Node->getPrimaryParent();
                   
                   if ($parent != null) {
                        $CacheNode = $this->spaceExists("cache",Node::__toNodeRef($this->store,$parent->getId()));
                        if ($CacheNode == false)
                            $CacheNode = $this->createSpace("cache",$parent);   
                            
                        $cmFileName = preg_replace("/.*\/(.*)/is","$1",$tempFileSWF);
                        $uploadNode = $this->spaceExists($cmFileName,Node::__toNodeRef($this->store,$CacheNode->getId()));
                        if ($CacheNode == false)
                            $uploadNode = $this->uploadData($tempFileSWF,'application/x-shockwave-flash',$CacheNode);
                            
                        $contentUpload = $uploadNode->cm_content;
                        
                        $viewer = new ViewerRelations();
                        $viewer->setNodeid("659be981-e44a-4e04-b692-424e047d773a");
                        $viewer->setViewernode($uploadNode->getId());
                        $viewer->setViewerurl($contentUpload->getUrl());

                        $viewer->setViewercontent($contentUpload->getContent());
                        

                        $viewer->save();
                        //echo $contentUpload->getUrl();
                        
                        
                   }   */                       
                }
            }
            @unlink($tempFile);
            @unlink($tempFileIn);
            @unlink($tempFileSWF);
        }
    //}
    //catch(SoapFault $ex) {
         
    //}
  }
  
  private function writeTempNode(Node $Node) 
  {
    //$tempFile = tempnam("/temp", $Node->cm_name);
    $tempFile = sfConfig::get('sf_cache_dir')."/".$Node->cm_name;
    @unlink($tempFile);
    $content = $Node->cm_content;
    if ($content != null) {
        $content->readContentToFile($tempFile);
    }
    return $tempFile;
  }
  
  private function createSpace($spaceName,$NodeObj=null) 
  {
    if ($NodeObj != null) {
        $newEntityNode = $NodeObj->createChild("cm_folder", "cm_contains", "cm_".$spaceName);
        $newEntityNode->cm_name = $spaceName;
        $newEntityNode->cm_title = $spaceName;
        $newEntityNode->cm_description = "";

        $this->session->save();
        $this->session = $this->repository->createSession($this->ticket);      
    }
    return $newEntityNode;
  }
  
  private function uploadData($fileName,$fileType,$NodeObj) {  
    if ($NodeObj != null) {  
        $cmFileName = preg_replace("/.*\/(.*)/is","$1",$fileName);
        $contentNode = $NodeObj->createChild("cm_content", "cm_contains", "cm_".$cmFileName);   
                  
        $contentNode->cm_name = $cmFileName;
        $contentNode->cm_title = "";
        $contentNode->cm_description = "";   

        $contentData = $contentNode->setContent("cm_content", $fileType, "UTF-8");  

        $contentData->writeContentFromFile($fileName);
           
        $this->session->save();
        $this->session = $this->repository->createSession($this->ticket);      
    }
    return $contentNode;
  }
  
  private function spaceExists($spaceName,$nodeRef) {
  //{PARENT:"FolderNodeRef"  +@cm\:name:"name"
    $folderPath = "@cm\:name:\"".$spaceName."\"";
    $folderPath = str_replace(" ","_",$folderPath);

    //echo $folderPath."<br>";
    $results = $this->session->query($this->store, 'PARENT:"'.$nodeRef.'" +@cm\:name:"'.$spaceName.'"');  
    $Node = $results[0];
    
    if ($Node == null) {
        return false;
    }  
    return $Node;
  }

}
