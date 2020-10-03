<?php
declare(strict_types=1);

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\Database\StatementInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use LogicException;

/**
 * Upsert Behavior
 */
class UpsertBehavior extends Behavior
{
    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'updateColumns' => null,
        'uniqueColumns' => null,
        'event' => ['beforeSave' => true],
    ];

    /**
     * execute upsert query
     *
     * @param Entity $entity upsert entity
     * @return EntityInterface|array|null result entity
     * @throws LogicException invalid config
     */
    public function upsert(Entity $entity)
    {
        if (!$this->isValidArrayConfig('updateColumns')) {
            throw new LogicException('config updateColumns is invalid.');
        }
        if (!$this->isValidArrayConfig('uniqueColumns')) {
            throw new LogicException('config uniqueColumns is invalid.');
        }

        if ($this->_config['event']['beforeSave']) {
            $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
        }
        $entity->setVirtual([]);
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

        return $this->_table
            ->find()
            ->where($conditions)
            ->first();
    }

    /**
     * execute bulk upsert query
     *
     * @param Entity[] $entities upsert entities
     * @return StatementInterface query result
     * @throws LogicException invalid config or no save data
     */
    public function bulkUpsert(array $entities): StatementInterface
    {
        if (!$this->isValidArrayConfig('updateColumns')) {
            throw new LogicException('config updateColumns is invalid.');
        }

        $saveData = [];
        foreach ($entities as $entity) {
            if ($this->_config['event']['beforeSave']) {
                $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
            }
            $entity->setVirtual([]);
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
        $query->clause('values')->setValues($saveData);

        return $query->execute();
    }

    /**
     * validate config value
     *
     * @param string $configName config key
     *
     * @return bool valid or invalid
     */
    private function isValidArrayConfig(string $configName): bool
    {
        $config = $this->_config[$configName];

        return is_array($config) && !empty($config);
    }
}
