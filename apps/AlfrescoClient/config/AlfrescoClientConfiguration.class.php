<?php

class AlfrescoClientConfiguration extends sfApplicationConfiguration
{
  private static $Version = "0.3";  
  public static function get_version() {
    return self::$Version;    
  }
  public function configure()
  {
  }
}
