<?php

/**
 * Settings filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSettingsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'keystring'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'valuestring' => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'keystring'   => new sfValidatorPass(array('required' => false)),
      'valuestring' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('settings_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Settings';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'keystring'   => 'Text',
      'valuestring' => 'Text',
    );
  }
}
