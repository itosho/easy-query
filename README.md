# Easy Query

CakePHP behavior plugin for easily some complicated queries like upsert, bulk upsert and bulk insert.

[![Build Status](https://travis-ci.org/itosho/easy-query.svg?branch=master)](https://travis-ci.org/itosho/easy-query)
[![codecov](https://codecov.io/gh/itosho/easy-query/branch/master/graph/badge.svg)](https://codecov.io/gh/itosho/easy-query)
[![Total Downloads](https://poser.pugx.org/itosho/easy-query/downloads)](https://packagist.org/packages/itosho/easy-query)
[![License](https://poser.pugx.org/itosho/easy-query/license)](https://packagist.org/packages/itosho/easy-query)

## Requirements

- PHP 7.0+
- CakePHP 3.5.0+
- MySQL 5.6+

## Installation

```bash
composer require itosho/easy-query
```

## Usage

### Upsert

```php
$this->Tags = TableRegistry::get('Tags');
$this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
    'uniqueColumns' => ['name'],
    'updateColumns' => ['description', 'modified']
]);

$data = [
    'name' => 'cakephp',
    'description' => 'php web framework'
    'created' => '2017-09-01 00:00:00',
    'modified' => '2017-09-01 00:00:00'
];
$entity = $this->Tags->newEntity($data);
$this->Tags->upsert($entity);
```

### Bulk Upsert

```php
$this->Tags = TableRegistry::get('Tags');
$this->Tags->addBehavior('Itosho/EasyQuery.Upsert', [
    'updateColumns' => ['description', 'modified']
]);

$data = [
    [
        'name' => 'cakephp',
        'description' => 'php web framework'
        'created' => '2017-09-01 00:00:00',
        'modified' => '2017-09-01 00:00:00'
    ],
    [
        'name' => 'rubyonrails',
        'description' => 'ruby web framework'
        'created' => '2017-09-01 00:00:00',
        'modified' => '2017-09-01 00:00:00'
    ]
];
$entities = $this->Tags->newEntities($data);
$this->Tags->bulkUpsert($entities);
```

### Bulk Insert

```php
$this->Articles = TableRegistry::get('Articles');
$this->Articles->addBehavior('Itosho/EasyQuery.Insert');

$data = [
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
        'published' => '0',
        'created' => '2017-09-01 00:00:00',
        'modified' => '2017-09-01 00:00:00'
    ]
];
$entities = $this->Articles->newEntities($data);
$this->Articles->bulkInsert($entities);
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/itosho/easy-query.

## License

The plugin is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
