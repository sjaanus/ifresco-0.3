<?php

/**
 * Searchtemplates filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSearchtemplatesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'defaultview'  => new sfWidgetFormFilterInput(),
      'multicolumn'  => new sfWidgetFormFilterInput(),
      'columnset_id' => new sfWidgetFormFilterInput(),
      'showdoctype'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'jsondata'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'         => new sfValidatorPass(array('required' => false)),
      'defaultview'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'multicolumn'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'columnset_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'showdoctype'  => new sfValidatorPass(array('required' => false)),
      'jsondata'     => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('searchtemplates_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Searchtemplates';
  }

  public function getFields()
  {
    return array(
      'id'           => 'Number',
      'name'         => 'Text',
      'defaultview'  => 'Number',
      'multicolumn'  => 'Number',
      'columnset_id' => 'Number',
      'showdoctype'  => 'Text',
      'jsondata'     => 'Text',
    );
  }
}
