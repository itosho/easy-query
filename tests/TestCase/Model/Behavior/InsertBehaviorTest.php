<?php

namespace Itosho\EasyQuery\Test\TestCase\Model\Behavior;

use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class InsertBehaviorTest extends TestCase
{
    /**
     * @var \Cake\ORM\Table
     */
    public $Articles;
    public $fixtures = [
        'plugin.Itosho/EasyQuery.Articles'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Itosho/EasyQuery.Articles');
        $this->Articles->addBehavior('Itosho/EasyQuery.Insert');
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Articles);
    }

    public function testBulkUpsert()
    {
        $records = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($records as $record) {
            $record['created'] = $now;
            $record['modified'] = $now;
        }

        $entities = $this->Articles->newEntities($records);
        $this->Articles->bulkInsert($entities);

        foreach ($records as $conditions) {
            $actual = $this->Articles->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    public function testBulkUpsertAddTimestamp()
    {
        $this->Articles->addBehavior('Timestamp');

        $records = $this->getBaseInsertRecords();
        $customNow = '2017-01-01 00:00:00';
        $records[0]['created'] = $customNow;
        $records[1]['modified'] = $customNow;

        $expectedRecords = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($expectedRecords as $expectedRecord) {
            $expectedRecord['created'] = $now;
            $expectedRecord['modified'] = $now;
        }

        $entities = $this->Articles->newEntities($records);
        $this->Articles->bulkInsert($entities);

        foreach ($expectedRecords as $conditions) {
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

    private function getBaseInsertRecords()
    {
        return  [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => 1
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => 1
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Sixth Article Body',
                'published' => 1
            ]
        ];
    }
}
