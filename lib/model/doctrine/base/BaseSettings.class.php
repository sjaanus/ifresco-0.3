<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Settings', 'doctrine');

/**
 * BaseSettings
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $keystring
 * @property string $valuestring
 * 
 * @method integer  getId()          Returns the current record's "id" value
 * @method string   getKeystring()   Returns the current record's "keystring" value
 * @method string   getValuestring() Returns the current record's "valuestring" value
 * @method Settings setId()          Sets the current record's "id" value
 * @method Settings setKeystring()   Sets the current record's "keystring" value
 * @method Settings setValuestring() Sets the current record's "valuestring" value
 * 
 * @package    AlfrescoClient
 * @subpackage model
 * @author     Dominik Danninger
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseSettings extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('settings');
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '8',
             ));
        $this->hasColumn('keystring', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('valuestring', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}