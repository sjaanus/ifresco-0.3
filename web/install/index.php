<?php
/**
 * @package AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client v0.1alpha
 * 
 * Copyright (c) 2011 Dominik Danninger, MAY Computer GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
ini_set("display_errors",0);

class Installer
{
  protected $baseDir = null;
    
  public function __construct()
  {
    $this->baseDir = realpath(dirname(__FILE__).'/../..');
    
  }
  
  public function executeInstaller()
  {

    try {
        $DatabaseConfig = sfYaml::load(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'databases.yml'); 

        $ConfigExists = false;
        if (count($DatabaseConfig) > 0 && is_array($DatabaseConfig)) {
            $ConfigExists = true;
        }
    }
    catch (Exception $e) {
        $ConfigExists = false;
    }
    
    $CheckInit = array(
                        $this->check(version_compare(phpversion(), '5.3.0', '>='), sprintf('PHP version is at least 5.3.0 (%s)', phpversion()), 'Current version is '.phpversion(), true),
                        $this->check(class_exists('PDO'), 'PDO is installed', 'Install PDO (mandatory for Doctrine - Database)', true),
                        $this->check(function_exists('token_get_all'), 'The token_get_all() function is available', 'Install and enable the Tokenizer extension (highly recommended)', false),
                        $this->check(function_exists('mb_strlen'), 'The mb_strlen() function is available', 'Install and enable the mbstring extension', false),
                        $this->check(function_exists('iconv'), 'The iconv() function is available', 'Install and enable the iconv extension', false),
                        $this->check(function_exists('utf8_decode'), 'The utf8_decode() is available', 'Install and enable the XML extension', false),
                        $this->check(!ini_get('magic_quotes_gpc'), 'php.ini has magic_quotes_gpc set to off', 'Set it to off in php.ini', false),
                        $this->check(!ini_get('register_globals'), 'php.ini has register_globals set to off', 'Set it to off in php.ini', false),
                        $this->check(!ini_get('session.auto_start'), 'php.ini has session.auto_start set to off', 'Set it to off in php.ini', false)
                        );
    $sqlDrivers = array();
    if (class_exists('PDO')) {
      $sqlDrivers = PDO::getAvailableDrivers();
      $CheckInit[] = $this->check(count($sqlDrivers), 'PDO has some drivers installed: '.implode(', ', $sqlDrivers), 'Install PDO drivers (mandatory for Doctrine - Database)', true);
    }
    
                        
    $CheckInit = array_merge($CheckInit, array("<br>","<br>",$this->check($ConfigExists==false, 'Database config exists? (OK if it doesn\'t exists)', 'By using the installer you will overwrite the config!', false)));
    
    
    $FolderRights = array($this->isWritable($this->baseDir."/config","- JUST FOR THE INSTALLATION!"));
        
    if ($ConfigExists == true)                   
        $FolderRights[] = $this->isWritable($this->baseDir."/config/databases.yml","- JUST FOR THE INSTALLATION!");
                            
    $FolderRights = array_merge($FolderRights,array($this->isWritable($this->baseDir."/web/install","- JUST FOR THE INSTALLATION!"),
                        $this->isWritable($this->baseDir."/web/uploads"),
                        $this->isWritable($this->baseDir."/web/cache"),
                        $this->isWritable($this->baseDir."/cache"),
                        $this->isWritable($this->baseDir."/data"),
                        $this->isWritable($this->baseDir."/log"),
                        ));
    
    
    $arrayAllDrivers = array("mysql"=>"MySQL",
                                "mysqli"=>"MySQLi",
                                "sqlite"=>"SQLite",
                                "pgsql"=>"PostgreSQL",
                                "mssql"=>"MSSQL",
                                "oci"=>"Oracle",
                                "fbsql"=>"Frontbase",
                                "sqlsrv"=>"Sqlsrv - MSSQL",
                                "dblib"=>"DBLIB - MSSQL",
                                "odbc"=>"ODBC - MSSQL",
                                "ibase"=>"InterBase / Firebird");
    
    $DatabaseTypes = "";                            
    foreach ($sqlDrivers as $Driver) {
        if (array_key_exists($Driver,$arrayAllDrivers)) {
            $DriverName = $arrayAllDrivers[$Driver];
            $selected = "";
            if ($Driver == "mysql") {
                $DatabaseTypes = '<option value="'.$Driver.'" selected=selected>'.$DriverName.'</option>'.$DatabaseTypes;    
            }
            else
                $DatabaseTypes .= '<option value="'.$Driver.'">'.$DriverName.'</option>';    
        }
    }
    
                    
    $Fatal = false;

    echo <<< EOF
    <html>
<head>
<title>Install</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="/css/install.css">
<script type="text/javascript" src="/ysJQueryRevolutionsPlugin/js/jquery/jquery-1.4.min.js"></script>
<script type="text/javascript" src="/js/jquery.progressbar/js/jquery.progressbar.min.js"></script>
<script type="text/javascript" src="/js/form2object.js"></script>
<script type="text/javascript" src="/js/jquery.json-2.2.min.js"></script>
</head>
<body>
<script type="text/javascript">
var allowSubmit = false;
$(document).ready(function() {
    $('#StepInit').submit(function() {
        $("#StepInitContainer").slideUp(function() {
            $("#StepOneContainer").slideDown();
        });
        
        return false;
    });
    
    $('#StepOne').submit(function() {
      var formData = form2object("StepOne");
      var jsonFormData = $.toJSON(formData);
      $.post('?', "testConnection="+jsonFormData, function(success) {
        if (success == "false" || success.length == 0) {
            $(".connectionError").html("<b>Connection Failed!</b>");
            $(".stepOneBtn").fadeOut(function() {
                $(".stepOneBtn").html('Try-again!');
                $(".stepOneBtn").fadeIn();
            });
            
            return false;
        }
        else {
            $(".connectionError").html("");
            $("#StepOneContainer").slideUp(function() {
                $("#StepTwoContainer").slideDown();
            });
            
        }
      });
      return false;
    });
    
    $('#StepTwo').submit(function() {
        $("#installProgressbar").progressBar();
        $("#StepTwoContainer").slideUp(function() {
            $("#StepFinalContainer").slideDown(function() {
                startInstall();
            });
        });
        
        return false;
    });
});

function startInstall() {
    var formDbData = form2object("StepOne");
    var db_data = $.toJSON(formDbData);
    
    var alfrescoUrl = $("[name=alfrescoUrl]").val();

    var array = [{url:"?",data:"startInstall=true&action=removeInstaller&data=true",text:'Remove Installer manually please /web/install!'},
                 {url:"?",data:"startInstall=true&action=writeConfig&data="+db_data,text:'Write Database config!'},
                 {url:"?",data:"startInstall=true&action=generateSql&data="+db_data,text:'Load Database Tables!'},
                 {url:"?",data:"startInstall=true&action=writeAlfrescoConfig&data="+alfrescoUrl,text:'Write Alfresco config!'},
                 
                ]
    var addbar = 100 / array.length;
    var progress = 0;
    var waiting = false;
    for (var i = 0; i < array.length; ++i) {
        var installObj = array[i];

        $.post(installObj.url+"text="+installObj.text, installObj.data, function(success) {
            
            progress = progress + (addbar);
            $("#installProgressbar").progressBar(progress);
            $("#installProgress").append('<li><span class="installtext">'+success+'</span></li>').slideDown();
        });
    }
    
    if (progress < 100) {
        $("#installProgressbar").progressBar(100);
    }
}

</script>
<div align="center">
    <div id="installContainer">
        <div id="installBox">
            <div class="installTop">
                <div>
                    <img src="/images/logo200x106.png" height="106" width="200">
                </div>
            </div>
            <div class="installContent">
EOF;

                    
                echo <<< EOF
                <div id="StepInitContainer">
                <form action="" method="POST" id="StepInit" name="StepInit">
                <h1>Check Requirements</h1>
                <ul class="error_list error_list_black" style="margin-left:20px;font-weight:bold;padding-top:5px;">
EOF;

                foreach($CheckInit as $Check) { 
                    $message = $Check;
                    if (is_array($Check)) {
                        $Fatal = true;
                        $message = $Check["text"]." - ".$Check["fatal"];
                    }

                    echo "<li>$message</li>";
                
                }
                
                echo <<< EOF
                </ul><br><br>
                <h1>Check File/Folder Permissions</h1>
                
                <ul class="error_list error_list_black" style="margin-left:20px;font-weight:bold;padding-top:5px;">
EOF;


                foreach($FolderRights as $Check) { 
                    
                    echo "<li>$Check</li>";
                
                }
                
                $disabledSubmitOne = "";
                if ($Fatal==true) {
                    $disabledSubmitOne = 'disabled=disabled readonly id="disabledBtn"';
                }
                
                echo <<< EOF
                </ul><br>
                <ul>
                    <li>
                        <button type="submit" class="submit stepOneBtn" $disabledSubmitOne>Continue</button>
                    </li>
                </ul>
                </form>
                </div>
EOF;
                
                    
                echo <<< EOF
                <div id="StepOneContainer" style="display:none">
                <form action="" method="POST" id="StepOne" name="StepOne">

                <h1>Database Connection</h1>
EOF;
                if ($ConfigExists == true) {       
                echo <<< EOF
                <ul class="error_list" style="margin-left:20px;font-weight:bold;padding-top:5px;">
                    <li>WARNING: Database config already exists!! (You will overwrite the Config!)</li>
                </ul>
EOF;
                }
                
                echo <<< EOF
                <div style="width:100%;float:left;">
                    <div style="float:left;width:40%;">
                        <ul>
                            <li>
                                <label for="db_user">Username</label><input type="text" name="db_user">
                            </li>
                            <li>
                                <label for="db_pass">Password</label><input type="password" name="db_pass">
                            </li>
                            <li>
                                <span class="red connectionError"></span>
                            </li>
                        </ul>
                    </div>
                    <div style="float:left;width:40%;">
                        <ul>
                            <li>
                                <label for="db_host">Host</label><input type="text" name="db_host" value="localhost">
                            </li>
                            
                            <li>
                                <label for="db_database">Database</label><input type="text" name="db_database">
                            </li>
                            <li><label for="db_type">Database Type</label><select name="db_type">
EOF;
                                echo $DatabaseTypes;

                                echo <<< EOF
                                </select></li>
                            <li>
                                <button type="submit" class="submit stepOneBtn">Continue</button>
                            </li>
                        </ul>
                    </div>
                </div>
                </form>
                </div>
EOF;


                echo <<< EOF
                <div id="StepTwoContainer" style="display:none">
                <form action="" method="POST" id="StepTwo" name="StepTwo">

                <h1>Alfresco Repository</h1>          

                <ul>
                    <li>
                        <label for="alfrescoUrl">Alfresco Url</label><input type="text" name="alfrescoUrl"> <span class="help"><i>(e.g http://alfresco:8080/alfresco/api)</i></span>
                    </li>
                    <li>
                        <button type="submit" class="submit stepTwoBtn">Finish</button>
                    </li>
                </ul>
                </form>
                </div>
EOF;

                echo <<< EOF
                <div id="StepFinalContainer" style="display:none">

                <h1>Installation</h1>          
                
                <div id="installProgressbar" style="margin-left:20px;"></div>
                <ul id="installProgress">
                   
                </ul>

                <div style="margin-left:20px;"><a href="../index.php">Login - ifresco Client</a></div>
                </div>

                <div class="copyright">&copy; 2011 May Computer GmbH. All rights reserved. </div>
            </div>
            
        </div>
    </div>    
</div>
</body>
</html>
EOF;

  }
  
  private function check($boolean, $message, $help = '', $fatal = false) {
    $returnText .= $boolean ? '<span class="green">OK</span> =>' : sprintf("%s => ", $fatal ? '<span class="red">ERROR</span>' : '<span class="orange">WARNING</span>');
    $returnText .= $message;

    if (!$boolean) {
        $returnText .= " <i>*** $help ***</i><br>";
        if ($fatal) {
            return array("text"=>$returnText,"fatal"=>"You must fix this problem before resuming.");
        }
    }
    return $returnText;
  }
  
  private function isWritable($folderFile,$addInfo="") {
      $folderFileText = $folderFile;
      $folderFileText = str_replace($this->baseDir,"",$folderFileText);
      $folderFileText = str_replace("\\","/",$folderFileText);
    $fatal = true;  
    $boolean = is_writable($folderFile);
    $returnText .= $boolean ? '<span class="green">OK</span> =>' : sprintf("%s => ", $fatal ? '<span class="red">ERROR</span>' : '<span class="orange">WARNING</span>');
    $returnText .= $folderFileText." <i>$addInfo</i>";

    if (!$boolean) {
        $returnText .= " <i>*** is not writable - please set chmod's ***</i><br>";
    }
    return $returnText;
  }
      
  public function executeTestDatabase() {
  
      $data = $_POST['testConnection'];
      if (!empty($data))
          $data = json_decode($data);
      else
        $data = array();
      
      try {
          $testDN = $data->db_type .
                            '://' . $data->db_user .
                            ':' . $data->db_pass .
                            '@' . $data->db_host .
                            '/' . $data->db_database;
          
          $testConnection = Doctrine_Manager::connection($testDN, 'testConnection');
          $return = "false";
      
          if ($testConnection->connect()) {
              $return = "true";
          }
      }
      catch (Exception $e) {
          $return = "false";
      }

      die($return);
  }
  
  public function executeStartInstall() {
      /*data:"startInstall=true&writeConfig="+db_data,text:'Write Database config!'},
                 {url:"?",data:"startInstall=true&writeAlfrescoConfig="+alfrescoUrl,text:'Write Alfresco config!'},
                 {url:"?",data:"startInstall=true&removeInstaller=true",text:'Remove Installer!'}*/
      $data = $_POST["data"]; 
      $text = $_GET["text"];  
      $action = $_POST["action"];  
      if (!empty($data) && !empty($text)) {   
          $status = "FAIL";      
          $statusClr = "red";   
          try {   
              switch ($action) {
                  case "writeConfig":
                    $data = json_decode($data);
                    $statusClr = "green";
                    $status = "O.K";
                    try {
                        if ($data->db_type == "sqlsrv") {
                            $dsn = $data->db_type . ':'.
                                'server=' . $data->db_host .';'.
                                'Database=' . $data->db_database .';';
                        }
                        else {
                            $dsn = $data->db_type . ':'.
                                'host=' . $data->db_host .';'.
                                'dbname=' . $data->db_database .';';
                        }
                        
                        $dataBaseArray = array("all"=>
                                array("doctrine"=>
                                    array(
                            "class"=>"sfDoctrineDatabase",
                            "param"=>array(
                              "dsn"=>$dsn,
                              "username"=>$data->db_user,
                              "password"=>$data->db_pass))));

                        $fp = @fopen($this->baseDir."/config/databases.yml", "w+");
                        @fwrite($fp, sfYaml::dump($dataBaseArray));
                        @fclose($fp);
                    }
                    catch (Exception $e) {
                        $statusClr = "red";
                        $status = "FAIL";        
                    }
                  break;
                  case "generateSql":
                    $statusClr = "green";
                    $status = "O.K";
                        //sfDoctrineBuildTask  
                    /*$dispatcher = sfContext::getInstance()->getEventDispatcher();
                    $formatter = new sfFormatter();
                    $task = new sfDoctrineBuildTask($dispatcher, $formatter);
                    $task->run("xxx"); */
                    //$this->executeTask("sfDoctrineBaseTask","","");
                    /*$formatter = new sfFormatter();
                    $dispatcher = new sfEventDispatcher();
                    chdir($this->baseDir);
                    
                    $task = new sfDoctrineBuildSqlTask($dispatcher, $formatter);
                    echo $task->run(array(),array());*/
                    @$this->executeTask("sfDoctrineBuildDbTask");
                    //@$this->executeTask("sfDoctrineBuildModelTask");
                    @$this->executeTask("sfDoctrineBuildSqlTask");
                    @$this->executeTask("sfDoctrineInsertSqlTask");
                    @$this->executeTask("sfDoctrineDataLoadTask");
                  break;
                  case "writeAlfrescoConfig":
                    $statusClr = "green";
                    $status = "O.K";
                    try {
                        $url = $data;
                        $content = '<?xml version="1.0"?><config><RepositoryUrl>'.$url.'</RepositoryUrl><JAVA></JAVA><SWFTools></SWFTools></config>';
                        $fp = @fopen($this->baseDir."/config/alfresco.xml", "w+");
                        @fwrite($fp, $content);
                        @fclose($fp);
                    }
                    catch (Exception $e) {
                        $statusClr = "red";
                        $status = "FAIL";    
                    }
                  break;
                  case "removeInstaller":
                    $statusClr = "red";
                    $status = "PLEASE - IMPORTANT";
                  break;
                  default:
                  break;
              }
          }
          catch (Exception $e) {
              //echo $e->getMessage();
          }
          echo '<span class="'.$statusClr.'">'.$status.'</span> '.$text; 
      }
  }
  
  public function executeTask($class_name, $arguments = array(), $options = array()) {    
    $dispatcher = new sfEventDispatcher();
    $formatter = new sfFormatter();
    $task = new $class_name(new $dispatcher, $formatter);

    chdir($this->baseDir);
    
    @$task->run($arguments, $options);
  }
}
?>