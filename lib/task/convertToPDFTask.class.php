<?php

class convertToPDFTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));
    
    $this->addArgument('nodeId', sfCommandArgument::REQUIRED, 'Alfresco Node Id');
    $this->addArgument('repositoryUrl', sfCommandArgument::REQUIRED, 'Alfresco Repository');
    $this->addArgument('userName', sfCommandArgument::REQUIRED, 'Alfresco User or Ticket');
    $this->addArgument('password', sfCommandArgument::OPTIONAL, 'Alfresco Password');

    $ps2PDF = AlfrescoConfiguration::getInstance()->PS2PDF;              

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),

      new sfCommandOption('PS2PDF', null, sfCommandOption::PARAMETER_OPTIONAL, 'PS2PDF', $ps2PDF),
    ));

    $this->namespace        = 'Alfresco';
    $this->name             = 'convertToPDF';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [Alfresco:convertToPDF|INFO] task convert tiff to pdf
Call it with:

  [php symfony Alfresco:convertToPDF|INFO]
EOF;
  }
  

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
    
    $viewer = Doctrine::getTable('ViewerRelations')->find($nodeId);
    if ($viewer != null) {
        // TODO - check File SUM
        return;
    }
    

    
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
    
    $this->spacesStore = new SpacesStore($this->session);
        
    $Node = $this->session->getNode($this->spacesStore, $nodeId);
    if ($Node != null) {
        $content = $Node->cm_content;
        $mimetype = $content->getMimetype();
        
        $fileName = $Node->cm_name;
        $fileName = preg_replace("/[^a-zA-Z0-9]/","",$fileName);

        $fileName = preg_replace("/(.*)\..*/is","$1",$fileName);
        $tempFile = $this->writeTempNode($Node);   

        $tempFilePDF = sfConfig::get('sf_cache_dir')."/$fileName.pdf";

        $ConvertNotToPDF = array("image/tiff");
        if (in_array($mimetype,$ConvertNotToPDF)) {         
            $tempFileIn = $tempFile;
            
            if (file_exists($tempFileIn)) {
                //$this->tiff2pdf($tempFileIn,$tempFilePDF);
                if (($return = $this->tiff2pdf($tempFileIn, $tempFilePDF,$options)) !== true) {
                    print_r($return);
                } else {
                }
            }
            
             if (file_exists($tempFilePDF)) {
                    
                   $viewer = new ViewerRelations();
                   $viewer->setNodeid($nodeId);
                   $viewer->setViewernode($nodeId);
                   $viewer->setViewerurl("http");

                   $viewer->setViewercontent(file_get_contents($tempFilePDF)); 
                   
                   $viewer->save();
             }
        }
        
        @unlink($tempFile);
        @unlink($tempFileIn);
        @unlink($tempFilePDF);
    }
  }
  
  private function writeTempNode(Node $Node) 
  {
    //$tempFile = tempnam("/temp", $Node->cm_name);
    $name = $Node->cm_name;
    $name = preg_replace("/[^a-zA-Z0-9]/","",$name);
    $tempFile = sfConfig::get('sf_cache_dir')."/".$name;

    $content = $Node->cm_content;

    if ($content != null) {

        $content->readContentToFile($tempFile);
    }
    return $tempFile;
  }
  
  private function tiff2pdf($file_tif, $file_pdf, $options = array()){
    $errors     = array();
    //$cmd_ps2pdf = "/usr/bin/ps2pdfwr";
    $cmd_ps2pdf = $options["PS2PDF"];
    //$file_tif   = escapeshellarg($file_tif);
    //$file_pdf   = escapeshellarg($file_pdf);

    if (!file_exists($file_tif)) $errors[] = "Original TIFF file: ".$file_tif." does not exist";
    if (!file_exists($cmd_ps2pdf)) $errors[] = "Ghostscript PostScript to PDF converter not found at: ".$cmd_ps2pdf;
    if (!extension_loaded("imagick")) $errors[] = "Imagick extension not installed or not loaded";

    if (!count($errors)) {
        $base = $file_pdf;
        if(($ext = strrchr($file_pdf, '.')) !== false) $base = substr($file_pdf, 0, -strlen($ext));
        
        $file_ps = $base.".ps";
        
        $document = new Imagick($file_tif);
        
        if (!$document->writeImages($file_ps, true)) {
            $errors[] = "Unable to use Imagick to write multiple pages to 1 .ps file: ".$file_ps;
        } else {
            $document->clear();
            exec($cmd_ps2pdf." -sPAPERSIZE=a4 ".$file_ps." ".$file_pdf, $o, $r);
            if ($r) {
                $errors[] = "Unable to use ghostscript to convert .ps(".$file_ps.") -> .pdf(".$file_pdf."). Check rights. ";
            } 
        }
    }
    @unlink($file_ps);
    if (!count($errors)) {
        return true;
    } else {
        return $errors;
    }
  }
}
