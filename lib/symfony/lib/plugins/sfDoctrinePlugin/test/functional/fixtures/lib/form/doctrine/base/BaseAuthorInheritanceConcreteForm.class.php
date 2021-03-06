<?php

/**
 * AuthorInheritanceConcrete form base class.
 *
 * @method AuthorInheritanceConcrete getObject() Returns the current form's model object
 *
 * @package    AlfrescoClient
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAuthorInheritanceConcreteForm extends AuthorForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['additional'] = new sfWidgetFormInputText();
    $this->validatorSchema['additional'] = new sfValidatorString(array('max_length' => 255, 'required' => false));

    $this->widgetSchema->setNameFormat('author_inheritance_concrete[%s]');
  }

  public function getModelName()
  {
    return 'AuthorInheritanceConcrete';
  }

}
