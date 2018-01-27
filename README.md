# Easy Query

CakePHP behavior plugin for easily some complicated queries like upsert, bulk upsert and bulk insert.

[![Build Status](https://travis-ci.org/itosho/easy-query.svg?branch=master)](https://travis-ci.org/itosho/easy-query)
[![codecov](https://codecov.io/gh/itosho/easy-query/branch/master/graph/badge.svg)](https://codecov.io/gh/itosho/easy-query)
[![Latest Stable Version](https://poser.pugx.org/itosho/easy-query/v/stable)](https://packagist.org/packages/itosho/easy-query)
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
    ],
    [
        'name' => 'rubyonrails',
        'description' => 'ruby web framework'
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
        'published' => '1'
    ],
    [
        'title' => 'Second Article',
        'body' => 'Second Article Body',
        'published' => '0'
    ]
];
$entities = $this->Articles->newEntities($data);
$this->Articles->bulkInsert($entities);
```

### Notice from v1.1.0
Need to use `Timestamp` behavior, if you want to update `created` and `modified` fields automatically.
And you can change the action manually by using `event` config like this.

```php
// default value is true
$this->Articles->addBehavior('Itosho/EasyQuery.Insert', [
    'event' => ['beforeSave' => false]
]);
```

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/itosho/easy-query.

## License

The plugin is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
