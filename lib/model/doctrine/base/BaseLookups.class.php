<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Lookups', 'doctrine');

/**
 * BaseLookups
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $field
 * @property string $type
 * @property string $fielddata
 * @property integer $single
 * 
 * @method integer getId()        Returns the current record's "id" value
 * @method string  getField()     Returns the current record's "field" value
 * @method string  getType()      Returns the current record's "type" value
 * @method string  getFielddata() Returns the current record's "fielddata" value
 * @method integer getSingle()    Returns the current record's "single" value
 * @method Lookups setId()        Sets the current record's "id" value
 * @method Lookups setField()     Sets the current record's "field" value
 * @method Lookups setType()      Sets the current record's "type" value
 * @method Lookups setFielddata() Sets the current record's "fielddata" value
 * @method Lookups setSingle()    Sets the current record's "single" value
 * 
 * @package    AlfrescoClient
 * @subpackage model
 * @author     Dominik Danninger
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseLookups extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('lookups');
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '8',
             ));
        $this->hasColumn('field', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('type', 'string', 20, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '20',
             ));
        $this->hasColumn('fielddata', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('single', 'integer', 1, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '1',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}