<?php

/**
 * Viewerrelations filter form base class.
 *
 * @package    AlfrescoClient
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseViewerrelationsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'nodeid'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'viewernode'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'viewerurl'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'viewercontent' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'md5sum'        => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'nodeid'        => new sfValidatorPass(array('required' => false)),
      'viewernode'    => new sfValidatorPass(array('required' => false)),
      'viewerurl'     => new sfValidatorPass(array('required' => false)),
      'viewercontent' => new sfValidatorPass(array('required' => false)),
      'md5sum'        => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('viewerrelations_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Viewerrelations';
  }

  public function getFields()
  {
    return array(
      'id'            => 'Number',
      'nodeid'        => 'Text',
      'viewernode'    => 'Text',
      'viewerurl'     => 'Text',
      'viewercontent' => 'Text',
      'md5sum'        => 'Text',
    );
  }
}
