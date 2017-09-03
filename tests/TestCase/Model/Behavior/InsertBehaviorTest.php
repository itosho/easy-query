<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class InsertBehaviorTest extends TestCase
{
    /**
     * @var \Cake\ORM\Table
     */
    public $Articles;
    public $fixtures = [
        'plugin.Itosho/StrawberryCake.Articles'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Itosho/StrawberryCake.Articles');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Insert');
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Articles);
    }

    public function testBulkUpsert()
    {
        $now = '2017-09-01 00:00:00';
        $data = [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => '1',
                'created' => $now,
                'modified' => $now
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => '1',
                'created' => $now,
                'modified' => $now
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Sixth Article Body',
                'published' => '1',
                'created' => $now,
                'modified' => $now
            ]
        ];
        $entities = $this->Articles->newEntities($data);
        $this->Articles->bulkInsert($entities);

        foreach ($data as $conditions) {
            $actual = $this->Articles->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage entities has no save data.
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Articles->bulkInsert([]);
    }
}
