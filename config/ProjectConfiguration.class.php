<?php

require_once realpath(dirname(__FILE__).'/../lib/symfony/lib/autoload/sfCoreAutoload.class.php');

sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfDoctrinePlugin');
    $this->enablePlugins('sfJSLibManagerPlugin');
    $this->enablePlugins('sfJqueryReloadedPlugin');  
    $this->enablePlugins('ysJQueryRevolutionsPlugin');
    $this->enablePlugins('ysJQueryUIPlugin');
    //$this->enablePlugins('sfJqueryTreeDoctrineManagerPlugin');
  }
}
