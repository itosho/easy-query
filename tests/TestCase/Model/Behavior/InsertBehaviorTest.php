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
        $this->Articles = TableRegistry::getTableLocator()->get('Itosho/EasyQuery.Articles');
        $this->Articles->addBehavior('Itosho/EasyQuery.Insert');
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::getTableLocator()->clear();
        unset($this->Articles);
    }

    /**
     * bulkInsert() test
     *
     * @return void
     */
    public function testBulkInsert()
    {
        $records = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($records as $key => $val) {
            $record[$key]['created'] = $now;
            $record[$key]['modified'] = $now;
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
    public function testBulkInsertAddTimestamp()
    {
        $this->Articles->addBehavior('Timestamp');

        $records = $this->getBaseInsertRecords();
        $customNow = '2017-01-01 00:00:00';
        $records[0]['created'] = $customNow;
        $records[0]['modified'] = $customNow;

        $expectedRecords = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($expectedRecords as $key => $val) {
            $expectedRecords[$key]['created'] = $now;
            $expectedRecords[$key]['modified'] = $now;
        }
        $expectedRecords[0]['created'] = $customNow;
        $expectedRecords[0]['modified'] = $customNow;

        $entities = $this->Articles->newEntities($records);
        $this->Articles->bulkInsert($entities);

        foreach ($expectedRecords as $conditions) {
            $actual = $this->Articles->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * bulkInsert() test beforeSave not dispatched
     *
     * @return void
     */
    public function testBulkInsertNoBeforeSave()
    {
        $this->Articles->removeBehavior('Insert');
        $this->Articles->addBehavior('Itosho/EasyQuery.Insert', [
            'event' => ['beforeSave' => false]
        ]);

        $records = $this->getBaseInsertRecords();
        $customNow = '2017-01-01 00:00:00';
        $records[0]['created'] = $customNow;
        $records[0]['modified'] = $customNow;

        $expectedRecords = $this->getBaseInsertRecords();
        foreach ($expectedRecords as $key => $val) {
            $expectedRecords[$key]['created IS'] = null;
            $expectedRecords[$key]['modified IS'] = null;
        }
        unset($expectedRecords[0]['created IS']);
        unset($expectedRecords[0]['modified IS']);
        $expectedRecords[0]['created'] = $customNow;
        $expectedRecords[0]['modified'] = $customNow;

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
    public function testBulkInsertNoSaveData()
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
