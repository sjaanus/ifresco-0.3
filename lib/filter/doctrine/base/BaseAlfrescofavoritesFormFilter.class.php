<?php

/**
 * Alfrescofavorites filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseAlfrescofavoritesFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'nodename' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nodeid'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'nodetype' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'userkey'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'nodename' => new sfValidatorPass(array('required' => false)),
      'nodeid'   => new sfValidatorPass(array('required' => false)),
      'nodetype' => new sfValidatorPass(array('required' => false)),
      'userkey'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('alfrescofavorites_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Alfrescofavorites';
  }

  public function getFields()
  {
    return array(
      'id'       => 'Number',
      'nodename' => 'Text',
      'nodeid'   => 'Text',
      'nodetype' => 'Text',
      'userkey'  => 'Text',
    );
  }
}
