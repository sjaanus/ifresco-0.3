<?php

/**
 * Searchcolumnsets filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSearchcolumnsetsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'defaultset' => new sfWidgetFormFilterInput(),
      'name'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'jsonfields' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'defaultset' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'name'       => new sfValidatorPass(array('required' => false)),
      'jsonfields' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('searchcolumnsets_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Searchcolumnsets';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'defaultset' => 'Number',
      'name'       => 'Text',
      'jsonfields' => 'Text',
    );
  }
}
