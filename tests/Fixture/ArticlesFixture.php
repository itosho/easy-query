<?php
declare(strict_types=1);

namespace Itosho\EasyQuery\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public array $records = [
        [
            'title' => 'First Article',
            'body' => 'First Article Body',
            'published' => 1,
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'title' => 'Second Article',
            'body' => 'Second Article Body',
            'published' => 1,
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'title' => 'Third Article',
            'body' => 'Third Article Body',
            'published' => 1,
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
    ];
}
