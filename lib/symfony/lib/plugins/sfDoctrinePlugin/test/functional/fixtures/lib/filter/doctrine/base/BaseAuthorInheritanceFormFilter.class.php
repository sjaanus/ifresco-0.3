<?php

/**
 * AuthorInheritance filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceFormFilter extends AuthorFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('author_inheritance_filters[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritance';
  }
}
