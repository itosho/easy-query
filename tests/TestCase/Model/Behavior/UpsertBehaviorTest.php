<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UpsertBehaviorTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Cake\ORM\Table
     */
    public $Articles;
    public $Behavior;
    public $fixtures = [
        'plugin.Itosho/StrawberryCake.Articles'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Itosho/StrawberryCake.Articles');
        $this->Behavior = $this->Articles->addBehavior('Upsert', [
            'uniqueColumns' => ['title'],
            'updateColumns' => ['body', 'published', 'modified']
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Behavior, $this->Articles);
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

        $this->assertSame(4, $actual->id, 'return invalid id.');
        $this->assertSame($entity->body, $actual->body, 'return invalid body.');
        $this->assertSame($entity->published, $actual->published, 'return invalid published.');
        $this->assertSame($entity->created, $actual->created, 'return invalid created.');
        $this->assertSame($entity->modified, $actual->modified, 'return invalid modified.');
    }
}
