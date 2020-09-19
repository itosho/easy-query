<?php

namespace Itosho\EasyQuery\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class TagsFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'length' => 255, 'null' => false],
        'description' => ['type' => 'string', 'length' => 255, 'null' => false],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique' => ['type' => 'unique', 'columns' => ['name']],
        ],
    ];
    public $records = [
        [
            'name' => 'tag1',
            'description' => 'tag1 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'name' => 'tag2',
            'description' => 'tag2 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'name' => 'tag3',
            'description' => 'tag3 description',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ]
    ];
}
