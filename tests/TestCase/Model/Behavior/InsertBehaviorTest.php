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
        $now = Chronos::now();
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

    public function testBulkUpsertAddTimestamp()
    {
        $this->Articles->addBehavior('Timestamp');

        $customNow = '2017-01-01 00:00:00';
        $data = [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => '1',
                'created' => $customNow,
                'modified' => $customNow
            ],
            [
                'title' => 'Fifth Article',
                'body' => 'Fifth Article Body',
                'published' => '1'
            ],
            [
                'title' => 'Sixth Article',
                'body' => 'Sixth Article Body',
                'published' => '1'
            ]
        ];
        $now = Chronos::now();
        $expected = [
            [
                'title' => 'Fourth Article',
                'body' => 'Fourth Article Body',
                'published' => '1',
                'created' => $customNow,
                'modified' => $customNow
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

        foreach ($expected as $conditions) {
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
