<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Allowedtypes', 'doctrine');

/**
 * BaseAllowedtypes
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * 
 * @method integer      getId()   Returns the current record's "id" value
 * @method string       getName() Returns the current record's "name" value
 * @method Allowedtypes setId()   Sets the current record's "id" value
 * @method Allowedtypes setName() Sets the current record's "name" value
 * 
 * @package    AlfrescoClient
 * @subpackage model
 * @author     Dominik Danninger
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseAllowedtypes extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('allowedtypes');
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '8',
             ));
        $this->hasColumn('name', 'string', 180, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '180',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        
    }
}