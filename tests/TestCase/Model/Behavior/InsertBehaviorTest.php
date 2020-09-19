<?php

namespace Itosho\EasyQuery\Test\TestCase\Model\Behavior;

use Cake\Chronos\Chronos;
use Cake\I18n\FrozenTime;
use Cake\ORM\Table;
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
     * @var Table
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
            'event' => ['beforeSave' => false],
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
     * insertOnce() test
     *
     * @return void
     */
    public function testInsertOnce()
    {
        $newData = [
            'title' => 'New Article',
            'body' => 'New Article Body',
            'published' => 1,
        ];
        $entity = $this->Articles->newEntity($newData);

        $this->Articles->insertOnce($entity);

        $actual = $this->Articles
            ->find()
            ->where($newData)
            ->all();

        $this->assertCount(1, $actual, 'fail insert once.');
    }

    /**
     * insertOnce() test add timestamp behavior
     *
     * @return void
     */
    public function testInsertOnceAddTimestampBehavior()
    {
        $this->Articles->addBehavior('Timestamp');

        $newData = [
            'title' => 'New Article',
            'body' => 'New Article Body',
            'published' => 1,
        ];
        $entity = $this->Articles->newEntity($newData);
        $now = FrozenTime::now();

        $this->Articles->insertOnce($entity);

        $newData['created'] = $now;
        $newData['modified'] = $now;

        $actual = $this->Articles->exists($newData);
        $this->assertTrue($actual, 'fail insert.');
    }

    /**
     * insertOnce() test when duplicated
     *
     * @return void
     */
    public function testInsertOnceWhenDuplicated()
    {
        $duplicatedData = [
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 1,
        ];
        $entity = $this->Articles->newEntity($duplicatedData);

        $this->Articles->insertOnce($entity);

        $actual = $this->Articles
            ->find()
            ->where($duplicatedData)
            ->all();

        $this->assertCount(1, $actual, 'fail insert once.');
    }

    /**
     * insertOnce() test when is null
     *
     * @return void
     */
    public function testInsertOnceWhenIsNull()
    {
        $newData = [
            'title' => 'First Article',
            'body' => null,
            'published' => 1,
        ];
        $entity = $this->Articles->newEntity($newData);

        $this->Articles->insertOnce($entity);

        $actual = $this->Articles
            ->find()
            ->where([
                'title' => 'First Article',
                'body IS' => null,
                'published' => 1,
            ])
            ->all();

        $this->assertCount(1, $actual, 'fail insert once.');
    }

    /**
     * insertOnce() test with conditions
     *
     * @return void
     */
    public function testInsertOnceWithConditions()
    {
        $newData = [
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 0,
        ];
        $entity = $this->Articles->newEntity($newData);

        $conditions = [
            'title' => 'Brand New First Article',
            'body' => 'Brand New First Article Body',
        ];

        $this->Articles->insertOnce($entity, $conditions);

        $actual = $this->Articles
            ->find()
            ->where([
                'title' => 'First Article',
                'body' => 'First Article Body',
            ])
            ->all();

        $this->assertCount(2, $actual, 'fail insert once.');
    }

    /**
     * insertOnce() test when duplicated with conditions
     *
     * @return void
     */
    public function testInsertOnceWhenDuplicatedWithConditions()
    {
        $newData = [
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 0,
        ];
        $entity = $this->Articles->newEntity($newData);

        $conditions = [
            'title' => 'First Article',
            'body' => 'First Article Body',
        ];

        $this->Articles->insertOnce($entity, $conditions);

        $actual = $this->Articles
            ->find()
            ->where($conditions)
            ->all();

        $this->assertCount(1, $actual, 'fail insert once.');
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
                'published' => 1,
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => 1,
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Sixth Article Body',
                'published' => 1,
            ]
        ];
    }
}
