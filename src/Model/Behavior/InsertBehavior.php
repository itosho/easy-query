<?php

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\StatementInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use LogicException;

/**
 * Insert Behavior
 */
class InsertBehavior extends Behavior
{
    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'event' => ['beforeSave' => true]
    ];

    /**
     * execute bulk insert query
     *
     * @param Entity[] $entities insert entities
     * @throws LogicException no save data
     * @return StatementInterface query result
     */
    public function bulkInsert(array $entities)
    {
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

        $query = $this->_table
            ->query()
            ->insert($fields);
        $query->clause('values')->setValues($saveData);

        return $query->execute();
    }

    /**
     * execute insert select query for saving a record at once
     *
     * @param Entity $entity insert entity
     * @param array|null $conditions search conditions
     * @return StatementInterface query result
     */
    public function insertOnce(Entity $entity, array $conditions = null)
    {
        if ($this->_config['event']['beforeSave']) {
            $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
        }

        $entity->setVirtual([]);
        $insertData = $entity->toArray();
        $escape = function ($content) {
            return is_null($content) ? 'NULL' : '\'' . addslashes($content) . '\'';
        };

        $escapedInsertData = array_map($escape, $insertData);
        $fields = array_keys($insertData);
        $existsConditions = $conditions;
        if (is_null($existsConditions)) {
            $existsConditions = $this->getExistsConditions($escapedInsertData);
        }

        $query = $this->_table
            ->query()
            ->insert($fields)
            ->epilog(
                $this
                    ->buildTmpTableSelectQuery($escapedInsertData)
                    ->where(function (QueryExpression $exp) use ($existsConditions) {
                        $query = $this->_table
                            ->find()
                            ->where($existsConditions);

                        return $exp->notExists($query);
                    })
                    ->limit(1)
            );

        return $query->execute();
    }

    /**
     * build tmp table's select query for insert select query
     *
     * @param array $escapedData escaped array data
     * @throws LogicException select query is invalid
     * @return Query tmp table's select query
     */
    private function buildTmpTableSelectQuery($escapedData)
    {
        $driver = $this->_table
            ->getConnection()
            ->getDriver();
        $schema = [];
        foreach ($escapedData as $key => $value) {
            $col = $driver->quoteIdentifier($key);
            $schema[] = "{$value} AS {$col}";
        }

        $tmpTable = TableRegistry::getTableLocator()->get('tmp', [
            'schema' => $this->_table->getSchema()
        ]);
        $query = $tmpTable
            ->find()
            ->select(array_keys($escapedData))
            ->from(
                sprintf('(SELECT %s) as tmp', implode(',', $schema))
            );

        if (is_array($query)) {
            throw new LogicException('select query is invalid.');
        }

        return $query;
    }

    /**
     * get conditions for finding a record already exists
     *
     * @param array $escapedData escaped array data
     * @return array conditions
     */
    private function getExistsConditions($escapedData)
    {
        $autoFillFields = ['created', 'modified'];
        $existsConditions = [];
        foreach ($escapedData as $field => $value) {
            if (in_array($field, $autoFillFields, true)) {
                continue;
            }
            if ($value === 'NULL') {
                $existsConditions[] = "{$field} IS NULL";
            } else {
                $existsConditions[] = "{$field} = {$value}";
            }
        }

        return $existsConditions;
    }
}
