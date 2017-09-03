<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UpsertBehaviorTest extends TestCase
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
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert', [
            'uniqueColumns' => ['title'],
            'updateColumns' => ['body', 'published', 'modified']
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Articles);
    }

    public function testUpsertByInsert()
    {
        $data = [
            'title' => 'Fourth Article',
            'body' => 'Fourth Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Articles->newEntity($data);
        $actual = $this->Articles->upsert($entity);

        $this->assertTrue($this->Articles->exists($data), 'fail insert.');

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
            'title' => 'First Article',
            'body' => 'Brand New First Article Body',
            'published' => '0',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Articles->newEntity($data);
        $actual = $this->Articles->upsert($entity);
        $currentCreated = '2007-03-18 10:39:23';

        $data['created'] = $currentCreated;
        $this->assertTrue($this->Articles->exists($data), 'fail update.');

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
     */
    public function testUpsertInvalidUpdateColumnsConfig()
    {
        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert', [
            'uniqueColumns' => ['title']
        ]);

        $data = [
            'title' => 'Fourth Article',
            'body' => 'Fourth Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Articles->newEntity($data);
        $this->Articles->upsert($entity);
    }

    /**
     * @expectedException \LogicException
     */
    public function testUpsertInvalidUniqueColumnsConfig()
    {
        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['body', 'published', 'modified']
        ]);

        $data = [
            'title' => 'Fourth Article',
            'body' => 'Fourth Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ];
        $entity = $this->Articles->newEntity($data);
        $this->Articles->upsert($entity);
    }

    public function testBulkUpsert()
    {
        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['body', 'published', 'modified']
        ]);

        // insert
        $insertNow = '2017-09-01 00:00:00';
        $insertData = [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => '1',
                'created' => $insertNow,
                'modified' => $insertNow
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => '1',
                'created' => $insertNow,
                'modified' => $insertNow
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Sixth Article Body',
                'published' => '1',
                'created' => $insertNow,
                'modified' => $insertNow
            ]
        ];
        $insertEntities = $this->Articles->newEntities($insertData);
        $this->Articles->bulkUpsert($insertEntities);

        foreach ($insertData as $data) {
            $actual = $this->Articles->exists($data);
            $this->assertTrue($actual, 'fail insert.');
        }

        // update
        $updateNow = '2017-09-02 00:00:00';
        $updateData = [
            [
                'title' => 'Fourth Article',
                'body' => 'Brand New Fourth Article Body',
                'published' => '0',
                'created' => $updateNow,
                'modified' => $updateNow
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Brand New Fifth Article Body',
                'published' => '0',
                'created' => $updateNow,
                'modified' => $updateNow
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Brand New Sixth Article Body',
                'published' => '0',
                'created' => $updateNow,
                'modified' => $updateNow
            ]
        ];
        $updateEntities = $this->Articles->newEntities($updateData);
        $this->Articles->bulkUpsert($updateEntities);

        foreach ($updateData as $data) {
            $data['created'] = $insertNow;
            $actual = $this->Articles->exists($data);
            $this->assertTrue($actual, 'fail update.');
        }
    }

    /**
     * @expectedException \LogicException
     */
    public function testBulkUpsertInvalidUpdateColumnsConfig()
    {
        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert');

        $data = [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => '1',
                'created' => '2017-09-01 00:00:00',
                'modified' => '2017-09-01 00:00:00'
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => '1',
                'created' => '2017-09-01 00:00:00',
                'modified' => '2017-09-01 00:00:00'
            ]
        ];

        $entities = $this->Articles->newEntities($data);
        $this->Articles->bulUpsert($entities);
    }

    /**
     * @expectedException \LogicException
     */
    public function testBulkUpsertNoSaveData()
    {
        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Itosho/StrawberryCake.Upsert', [
            'updateColumns' => ['body', 'published', 'modified']
        ]);

        $this->Articles->bulUpsert([]);
    }
}
