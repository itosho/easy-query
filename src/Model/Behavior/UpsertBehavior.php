<?php

namespace Itosho\EasyQuery\Model\Behavior;

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
            throw new LogicException('config updateColumns is invalid.');
        }
        if (!$this->isValidArrayConfig('uniqueColumns')) {
            throw new LogicException('config uniqueColumns is invalid.');
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
     * execute bulk upsert query
     *
     * @param \Cake\ORM\Entity[] $entities upsert entities
     *
     * @return \Cake\Database\StatementInterface query result
     */
    public function bulkUpsert(array $entities)
    {
        if (!$this->isValidArrayConfig('updateColumns')) {
            throw new LogicException('config updateColumns is invalid.');
        }

        $saveData = [];
        foreach ($entities as $entity) {
            $saveData[] = $entity->toArray();
        }

        if (!isset($saveData[0])) {
            throw new LogicException('entities has no save data.');
        }
        $fields = array_keys($saveData[0]);

        $updateColumns = $this->_config['updateColumns'];
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "`{$column}`=VALUES(`{$column}`)";
        }
        $updateStatement = implode(', ', $updateValues);
        $expression = 'ON DUPLICATE KEY UPDATE ' . $updateStatement;

        $query = $this->_table
            ->query()
            ->insert($fields)
            ->epilog($expression);
        $query->clause('values')->values($saveData);

        return $query->execute();
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
