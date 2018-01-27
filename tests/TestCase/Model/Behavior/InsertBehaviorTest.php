<?php

namespace Itosho\EasyQuery\Test\TestCase\Model\Behavior;

use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Itosho\EasyQuery\Model\Behavior\InsertBehavior Test Case
 */
class InsertBehaviorTest extends TestCase
{
    /**
     * ArticlesTable Class
     *
     * @var \Cake\ORM\Table
     */
    public $Articles;
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = ['plugin.Itosho/EasyQuery.Articles'];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Itosho/EasyQuery.Articles');
        $this->Articles->addBehavior('Itosho/EasyQuery.Insert');
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Articles);
    }

    /**
     * bulkInsert() test
     *
     * @return void
     */
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

    /**
     * bulkInsert() test add timestamp behavior
     *
     * @return void
     */
    public function testBulkUpsertAddTimestamp()
    {
        $this->Articles->removeBehavior('Insert');
        $this->Articles->addBehavior('Itosho/EasyQuery.Insert', [
            'event' => ['beforeSave' => true]
        ]);
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
     * bulkInsert() test by no data
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage entities has no save data.
     * @return void
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Articles->bulkInsert([]);
    }

    /**
     * get base insert records
     *
     * @return array
     */
    private function getBaseInsertRecords()
    {
        return [
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
