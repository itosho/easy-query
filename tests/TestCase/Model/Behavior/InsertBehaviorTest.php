<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class InsertBehaviorTest extends TestCase
{
    /**
     * @var \Cake\ORM\Table
     */
    public $Tags;
    public $fixtures = [
        'plugin.Itosho/StrawberryCake.Tags'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Tags = TableRegistry::get('Itosho/StrawberryCake.Tags');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Insert');
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Tags);
    }

    public function testBulkUpsert()
    {
        // insert
        $now = '2017-09-01 00:00:00';
        $data = [
            [
                'name' => 'tag4',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag5',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag6',
                'created' => $now,
                'modified' => $now
            ]
        ];
        $entities = $this->Tags->newEntities($data);
        $this->Tags->bulkInsert($entities);

        foreach ($data as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage entities has no save data.
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Tags->bulkUpsert([]);
    }
}
