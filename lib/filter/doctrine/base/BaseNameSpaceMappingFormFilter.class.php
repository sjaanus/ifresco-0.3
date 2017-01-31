<?php

/**
 * Namespacemapping filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseNamespacemappingFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'namespace' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'prefix'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'namespace' => new sfValidatorPass(array('required' => false)),
      'prefix'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('namespacemapping_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Namespacemapping';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'namespace' => 'Text',
      'prefix'    => 'Text',
    );
  }
}
