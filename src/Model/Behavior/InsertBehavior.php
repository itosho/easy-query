<?php
declare(strict_types=1);

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\StatementInterface;
use Cake\I18n\FrozenTime;
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
        'event' => ['beforeSave' => true],
    ];

    /**
     * execute bulk insert query
     *
     * @param Entity[] $entities insert entities
     * @throws LogicException no save data
     * @return StatementInterface query result
     */
    public function bulkInsert(array $entities): StatementInterface
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
     * execute insert select query for saving a record just once
     *
     * @param Entity $entity insert entity
     * @param array|null $conditions search conditions
     * @return StatementInterface query result
     */
    public function insertOnce(Entity $entity, array $conditions = null): StatementInterface
    {
        if ($this->_config['event']['beforeSave']) {
            $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
        }

        $entity->setVirtual([]);
        $insertData = $entity->toArray();
        if (isset($insertData['created']) && !is_null($insertData['created'])) {
            $insertData['created'] = FrozenTime::now()->toDateTimeString();
        }
        if (isset($insertData['modified']) && !is_null($insertData['modified'])) {
            $insertData['modified'] = FrozenTime::now()->toDateTimeString();
        }

        $fields = array_keys($insertData);
        $existsConditions = $conditions;
        if (is_null($existsConditions)) {
            $existsConditions = $this->getExistsConditions($insertData);
        }

        $query = $this->_table
            ->query()
            ->insert($fields)
            ->epilog(
                $this
                    ->buildTmpTableSelectQuery($insertData)
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
     * @param array $insertData insert data
     * @throws LogicException select query is invalid
     * @return Query tmp table's select query
     */
    private function buildTmpTableSelectQuery($insertData): Query
    {
        $driver = $this->_table
            ->getConnection()
            ->getDriver();
        $schema = [];
        $binds = [];
        foreach ($insertData as $key => $value) {
            $col = $driver->quoteIdentifier($key);
            if (is_null($value)) {
                $schema[] = "NULL AS {$col}";
            } else {
                $bindKey = ':' . strtolower($key);
                $binds[$bindKey] = $value;
                $schema[] = "{$bindKey} AS {$col}";
            }
        }

        $tmpTable = TableRegistry::getTableLocator()->get('tmp', [
            'schema' => $this->_table->getSchema(),
        ]);
        $query = $tmpTable
            ->find()
            ->select(array_keys($insertData))
            ->from(
                sprintf('(SELECT %s) as tmp', implode(',', $schema))
            );
        /** @var Query $selectQuery */
        $selectQuery = $query;
        foreach ($binds as $key => $value) {
            $selectQuery->bind($key, $value);
        }

        return $selectQuery;
    }

    /**
     * get conditions for finding a record already exists
     *
     * @param array $insertData insert data
     * @return array conditions
     */
    private function getExistsConditions(array $insertData): array
    {
        $autoFillFields = ['created', 'modified'];
        $existsConditions = [];
        foreach ($insertData as $field => $value) {
            if (in_array($field, $autoFillFields, true)) {
                continue;
            }
            $existsConditions[$field . ' IS'] = $value;
        }

        return $existsConditions;
    }
}
