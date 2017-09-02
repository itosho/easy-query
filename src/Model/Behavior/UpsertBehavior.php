<?php

namespace Itosho\StrawberryCake\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use LogicException;

class UpsertBehavior extends Behavior
{
    protected $_defaultConfig = [
        'updateColumns' => null,
        'uniqueColumns' => null
    ];

    /**
     * execute upsert query
     *
     * @param \Cake\ORM\Entity $entity upsert entity
     *
     * @return \Cake\Datasource\EntityInterface|array|null result entity
     */
    public function upsert(Entity $entity)
    {
        if (!$this->isValidArrayConfig('updateColumns')) {
            throw new LogicException('config `updateColumns` is invalid.');
        }
        if (!$this->isValidArrayConfig('uniqueColumns')) {
            throw new LogicException('config `uniqueColumns` is invalid.');
        }

        $upsertData = $entity->toArray();
        $fields = array_keys($upsertData);

        $updateColumns = $this->_config['updateColumns'];

        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "`{$column}`=VALUES(`{$column}`)";
        }
        $updateStatement = implode(', ', $updateValues);
        $expression = 'ON DUPLICATE KEY UPDATE ' . $updateStatement;

        $this->_table
            ->query()
            ->insert($fields)
            ->values($upsertData)
            ->epilog($expression)
            ->execute();

        $uniqueColumns = $this->_config['uniqueColumns'];

        $conditions = [];
        foreach ($uniqueColumns as $column) {
            $conditions[$column] = $upsertData[$column];
        }

        $upsertEntity = $this->_table
            ->find()
            ->where($conditions)
            ->first();

        return $upsertEntity;
    }

    /**
     * validate config value
     *
     * @param string $configName config key
     *
     * @return bool valid or invalid
     */
    private function isValidArrayConfig($configName)
    {
        $config = $this->_config[$configName];

        return is_array($config) && !empty($config);
    }
}
