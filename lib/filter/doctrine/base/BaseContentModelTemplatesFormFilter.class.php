<?php

/**
 * Contentmodeltemplates filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseContentmodeltemplatesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'class'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'multicolumn' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'aspectview'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'jsondata'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'class'       => new sfValidatorPass(array('required' => false)),
      'multicolumn' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'aspectview'  => new sfValidatorPass(array('required' => false)),
      'jsondata'    => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('contentmodeltemplates_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Contentmodeltemplates';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'class'       => 'Text',
      'multicolumn' => 'Number',
      'aspectview'  => 'Text',
      'jsondata'    => 'Text',
    );
  }
}
