<?php

/**
 * Lookups filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLookupsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'field'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'type'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'fielddata' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'single'    => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'field'     => new sfValidatorPass(array('required' => false)),
      'type'      => new sfValidatorPass(array('required' => false)),
      'fielddata' => new sfValidatorPass(array('required' => false)),
      'single'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('lookups_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Lookups';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'field'     => 'Text',
      'type'      => 'Text',
      'fielddata' => 'Text',
      'single'    => 'Number',
    );
  }
}
