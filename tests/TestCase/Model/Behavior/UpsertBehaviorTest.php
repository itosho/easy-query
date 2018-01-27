<?php

namespace Itosho\EasyQuery\Test\TestCase\Model\Behavior;

use Cake\Chronos\Chronos;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Itosho\EasyQuery\Model\Behavior\UpsertBehavior Test Case
 */
class UpsertBehaviorTest extends TestCase
{
    /**
     * TagsTable Class
     *
     * @var \Cake\ORM\Table
     */
    public $Tags;
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = ['plugin.Itosho/EasyQuery.Tags'];

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->Tags = TableRegistry::get('Itosho/EasyQuery.Tags');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'uniqueColumns' => ['name'],
            'updateColumns' => ['description', 'modified']
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Tags);
    }

    /**
     * upsert() test by insert
     *
     * @return void
     */
    public function testUpsertByInsert()
    {
        $now = Chronos::now();
        $record = [
            'name' => 'tag4',
            'description' => 'tag4 description',
            'created' => $now,
            'modified' => $now
        ];
        $entity = $this->Tags->newEntity($record);
        $actual = $this->Tags->upsert($entity);

        $this->assertTrue($this->Tags->exists($record), 'fail insert.');

        $insertId = 4;
        $this->assertSame($insertId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->name, $actual->name, 'return invalid name.');
        $this->assertSame($entity->description, $actual->description, 'return invalid description.');
        $this->assertSame(
            $entity->created->toDateTimeString(),
            $actual->created->toDateTimeString(),
            'return invalid created.'
        );
        $this->assertSame(
            $entity->modified->toDateTimeString(),
            $actual->modified->toDateTimeString(),
            'return invalid modified.'
        );
    }

    /**
     * upsert() test by insert add timestamp behavior
     *
     * @return void
     */
    public function testUpsertByInsertAddTimestamp()
    {
        $this->Tags->addBehavior('Timestamp');

        $record = [
            'name' => 'tag4',
            'description' => 'tag4 description'
        ];
        $now = Chronos::now();
        $expectedRecord = $record;
        $expectedRecord['created'] = $now;
        $expectedRecord['modified'] = $now;

        $entity = $this->Tags->newEntity($record);
        $actual = $this->Tags->upsert($entity);

        $this->assertTrue($this->Tags->exists($expectedRecord), 'fail insert.');

        $insertId = 4;
        $this->assertSame($insertId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->name, $actual->name, 'return invalid name.');
        $this->assertSame($entity->description, $actual->description, 'return invalid description.');
        $this->assertSame(
            $entity->created->toDateTimeString(),
            $actual->created->toDateTimeString(),
            'return invalid created.'
        );
        $this->assertSame(
            $entity->modified->toDateTimeString(),
            $actual->modified->toDateTimeString(),
            'return invalid modified.'
        );
    }

    /**
     * upsert() test by update
     *
     * @return void
     */
    public function testUpsertByUpdate()
    {
        $record = [
            'name' => 'tag1',
            'description' => 'brand new tag1 description',
            'created' => '2017-10-01 00:00:00',
            'modified' => '2017-10-01 00:00:00'
        ];
        $entity = $this->Tags->newEntity($record);
        $actual = $this->Tags->upsert($entity);
        $currentCreated = '2017-09-01 00:00:00';

        $record['created'] = $currentCreated;
        $this->assertTrue($this->Tags->exists($record), 'fail update.');

        $updateId = 1;
        $this->assertSame($updateId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->name, $actual->name, 'return invalid name.');
        $this->assertSame($entity->description, $actual->description, 'return invalid description.');
        $this->assertSame(
            $currentCreated,
            $actual->created->toDateTimeString(),
            'return invalid created.'
        );
        $this->assertSame(
            $entity->modified->toDateTimeString(),
            $actual->modified->toDateTimeString(),
            'return invalid modified.'
        );
    }

    /**
     * upsert() test by update add timestamp behavior
     *
     * @return void
     */
    public function testUpsertByUpdateAddTimestamp()
    {
        $this->Tags->addBehavior('Timestamp');

        $record = [
            'name' => 'tag1',
            'description' => 'brand new tag1 description'
        ];
        $now = Chronos::now();
        $currentCreated = '2017-09-01 00:00:00';
        $expectedRecord = $record;
        $expectedRecord['created'] = $currentCreated;
        $expectedRecord['modified'] = $now;

        $entity = $this->Tags->newEntity($record);
        $actual = $this->Tags->upsert($entity);

        $this->assertTrue($this->Tags->exists($expectedRecord), 'fail update.');

        $updateId = 1;
        $this->assertSame($updateId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->name, $actual->name, 'return invalid name.');
        $this->assertSame($entity->description, $actual->description, 'return invalid description.');
        $this->assertSame(
            $currentCreated,
            $actual->created->toDateTimeString(),
            'return invalid created.'
        );
        $this->assertSame(
            $entity->modified->toDateTimeString(),
            $actual->modified->toDateTimeString(),
            'return invalid modified.'
        );
    }

    /**
     * upsert() test beforeSave not dispatched
     *
     * @return void
     */
    public function testUpsertNoBeforeSave()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'uniqueColumns' => ['name'],
            'updateColumns' => ['description', 'modified'],
            'event' => ['beforeSave' => false]
        ]);
        $this->Tags->addBehavior('Timestamp');

        $record = [
            'name' => 'tag4',
            'description' => 'tag4 description'
        ];
        $expectedRecord = $record;
        $expectedRecord['created IS'] = null;
        $expectedRecord['modified IS'] = null;

        $entity = $this->Tags->newEntity($record);
        $actual = $this->Tags->upsert($entity);

        $this->assertTrue($this->Tags->exists($expectedRecord), 'fail insert.');

        $insertId = 4;
        $this->assertSame($insertId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->name, $actual->name, 'return invalid name.');
        $this->assertSame($entity->description, $actual->description, 'return invalid description.');
        $this->assertSame($entity->created, $actual->created, 'return invalid created.');
        $this->assertSame($entity->modified, $actual->modified, 'return invalid modified.');
    }

    /**
     * upsert() test when invalid update columns
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage config updateColumns is invalid.
     * @return void
     */
    public function testUpsertInvalidUpdateColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'uniqueColumns' => ['name']
        ]);

        $data = [
            'name' => 'tag4',
            'description' => 'tag4 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Tags->newEntity($data);
        $this->Tags->upsert($entity);
    }

    /**
     * upsert() test when invalid unique columns
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage config uniqueColumns is invalid.
     * @return void
     */
    public function testUpsertInvalidUniqueColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $data = [
            'name' => 'tag4',
            'description' => 'tag4 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Tags->newEntity($data);
        $this->Tags->upsert($entity);
    }

    /**
     * bulkUpsert() test by insert
     *
     * @return void
     */
    public function testBulkUpsertByInsert()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $records = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($records as $key => $val) {
            $records[$key]['created'] = $now;
            $records[$key]['modified'] = $now;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);

        foreach ($records as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * bulkUpsert() test by insert add timestamp behavior
     *
     * @return void
     */
    public function testBulkUpsertByInsertAddTimestamp()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);
        $this->Tags->addBehavior('Timestamp');

        $records = $this->getBaseInsertRecords();
        $now = Chronos::now();
        $expectedRecords = $records;
        foreach ($expectedRecords as $key => $val) {
            $expectedRecords[$key]['created'] = $now;
            $expectedRecords[$key]['modified'] = $now;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);

        foreach ($expectedRecords as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * bulkUpsert() test by update
     *
     * @return void
     */
    public function testBulkUpsertByUpdate()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $records = $this->getBaseUpdateRecords();
        $now = Chronos::now();
        foreach ($records as $key => $val) {
            $records[$key]['created'] = $now;
            $records[$key]['modified'] = $now;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);

        $currentCreated = '2017-09-01 00:00:00';
        foreach ($records as $conditions) {
            $conditions['created'] = $currentCreated;
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail update.');
        }
    }

    /**
     * bulkUpsert() test by update add timestamp behavior
     *
     * @return void
     */
    public function testBulkUpsertByUpdateAddTimestamp()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);
        $this->Tags->addBehavior('Timestamp');

        $records = $this->getBaseUpdateRecords();
        $now = Chronos::now();
        $currentCreated = '2017-09-01 00:00:00';
        $expectedRecords = $records;
        foreach ($expectedRecords as $key => $val) {
            $expectedRecords[$key]['created'] = $currentCreated;
            $expectedRecords[$key]['modified'] = $now;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);

        foreach ($expectedRecords as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail update.');
        }
    }

    /**
     * bulkUpsert() test beforeSave not dispatched
     *
     * @return void
     */
    public function testBulkUpsertNoBeforeSave()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified'],
            'event' => ['beforeSave' => false]
        ]);
        $this->Tags->addBehavior('Timestamp');

        $records = $this->getBaseInsertRecords();
        $expectedRecords = $records;
        foreach ($expectedRecords as $key => $val) {
            $expectedRecords[$key]['created IS'] = null;
            $expectedRecords[$key]['modified IS'] = null;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);

        foreach ($expectedRecords as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    /**
     * bulkUpsert() test when invalid update columns
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage config updateColumns is invalid.
     * @return void
     */
    public function testBulkUpsertInvalidUpdateColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert');

        $records = $this->getBaseInsertRecords();
        $now = Chronos::now();
        foreach ($records as $key => $val) {
            $records[$key]['created'] = $now;
            $records[$key]['modified'] = $now;
        }

        $entities = $this->Tags->newEntities($records);
        $this->Tags->bulkUpsert($entities);
    }

    /**
     * bulkUpsert() test by no data
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage entities has no save data.
     * @return void
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $this->Tags->bulkUpsert([]);
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
                'name' => 'tag4',
                'description' => 'tag4 description'
            ],
            [
                'name' => 'tag5',
                'description' => 'tag5 description'
            ],
            [
                'name' => 'tag6',
                'description' => 'tag6 description'
            ]
        ];
    }

    /**
     * get base update records
     *
     * @return array
     */
    private function getBaseUpdateRecords()
    {
        return [
            [
                'name' => 'tag1',
                'description' => 'brand new tag1 description'
            ],
            [
                'name' => 'tag2',
                'description' => 'brand new tag2 description'
            ],
            [
                'name' => 'tag3',
                'description' => 'brand new tag3 description'
            ]
        ];
    }
}
