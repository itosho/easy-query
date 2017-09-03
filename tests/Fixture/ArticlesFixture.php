<?php

namespace Itosho\StrawberryCake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'body' => 'text',
        'published' => ['type' => 'integer', 'default' => '0', 'null' => false],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']]
        ]
    ];
    public $records = [
        [
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ],
        [
            'title' => 'Second Article',
            'body' => 'Second Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ],
        [
            'title' => 'Third Article',
            'body' => 'Third Article Body',
            'published' => '1',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00'
        ]
    ];
}
