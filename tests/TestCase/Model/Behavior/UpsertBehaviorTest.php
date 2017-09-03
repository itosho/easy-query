<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UpsertBehaviorTest extends TestCase
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
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
            'uniqueColumns' => ['name'],
            'updateColumns' => ['description', 'modified']
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Tags);
    }

    public function testUpsertByInsert()
    {
        $data = [
            'name' => 'tag4',
            'description' => 'tag4 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Tags->newEntity($data);
        $actual = $this->Tags->upsert($entity);

        $this->assertTrue($this->Tags->exists($data), 'fail insert.');

        $insertId = 4;
        $this->assertSame($insertId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->body, $actual->body, 'return invalid body.');
        $this->assertSame($entity->published, $actual->published, 'return invalid published.');
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

    public function testUpsertByUpdate()
    {
        $data = [
            'name' => 'tag1',
            'description' => 'tag1 description',
            'created' => '2017-10-01 00:00:00',
            'modified' => '2017-10-01 00:00:00'
        ];
        $entity = $this->Tags->newEntity($data);
        $actual = $this->Tags->upsert($entity);
        $currentCreated = '2007-09-01 00:00:00';

        $data['created'] = $currentCreated;
        $this->assertTrue($this->Tags->exists($data), 'fail update.');

        $updateId = 1;
        $this->assertSame($updateId, $actual->id, 'return invalid id.');
        $this->assertSame($entity->body, $actual->body, 'return invalid body.');
        $this->assertSame($entity->published, $actual->published, 'return invalid published.');
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
     * @expectedException \LogicException
     * @expectedExceptionMessage config updateColumns is invalid.
     */
    public function testUpsertInvalidUpdateColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
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
     * @expectedException \LogicException
     * @expectedExceptionMessage config uniqueColumns is invalid.
     */
    public function testUpsertInvalidUniqueColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
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

    public function testBulkUpsertByInsert()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $now = '2017-09-01 00:00:00';
        $data = [
            [
                'name' => 'tag4',
                'description' => 'tag4 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag5',
                'description' => 'tag5 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag6',
                'description' => 'tag6 description',
                'created' => $now,
                'modified' => $now
            ]
        ];
        $entities = $this->Tags->newEntities($data);
        $this->Tags->bulkUpsert($entities);

        foreach ($data as $conditions) {
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail insert.');
        }
    }

    public function testBulkUpsertByUpdate()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $now = '2017-10-01 00:00:00';
        $data = [
            [
                'name' => 'tag1',
                'description' => 'tag1 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag2',
                'description' => 'tag2 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag3',
                'description' => 'tag3 description',
                'created' => $now,
                'modified' => $now
            ]
        ];
        $entities = $this->Tags->newEntities($data);
        $this->Tags->bulkUpsert($entities);

        $currentCreated = '2007-09-01 00:00:00';
        foreach ($data as $conditions) {
            $conditions['created'] = $currentCreated;
            $actual = $this->Tags->exists($conditions);
            $this->assertTrue($actual, 'fail update.');
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage config updateColumns is invalid.
     */
    public function testBulkUpsertInvalidUpdateColumnsConfig()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert');

        $now = '2017-09-01 00:00:00';
        $data = [
            [
                'name' => 'tag4',
                'description' => 'tag4 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag5',
                'description' => 'tag5 description',
                'created' => $now,
                'modified' => $now
            ],
            [
                'name' => 'tag6',
                'description' => 'tag6 description',
                'created' => $now,
                'modified' => $now
            ]
        ];

        $entities = $this->Tags->newEntities($data);
        $this->Tags->bulkUpsert($entities);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage entities has no save data.
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Tags->removeBehavior('Upsert');
        $this->Tags->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['description', 'modified']
        ]);

        $this->Tags->bulkUpsert([]);
    }
}
