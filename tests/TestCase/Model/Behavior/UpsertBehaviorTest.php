<?php

namespace Itosho\StrawberryCake\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class UpsertBehaviorTest extends TestCase
{
    public $Articles;
    public $Behavior;
    public $fixtures = [
        'plugin.Itosho/StrawberryCake.Articles'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Itosho/StrawberryCake.Articles');
        $this->Behavior = $this->Articles->behaviors()->Upsert;
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset($this->Behavior, $this->Articles);
    }

    public function testUpsert()
    {
        $this->assertTrue(true);
    }
}
