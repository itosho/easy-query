<?php
declare(strict_types=1);

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\Database\Expression\QueryExpression;
use Cake\Database\StatementInterface;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query\SelectQuery;
use LogicException;

/**
 * Insert Behavior
 */
class InsertBehavior extends Behavior
{
    use LocatorAwareTrait;

    /**
     * Default config
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'event' => ['beforeSave' => true],
    ];

    /**
     * execute bulk insert query
     *
     * @param array<\Cake\Datasource\EntityInterface> $entities insert entities
     * @throws \LogicException no save data
     * @return \Cake\Database\StatementInterface query result
     */
    public function bulkInsert(array $entities): StatementInterface
    {
        $saveData = [];
        foreach ($entities as $entity) {
            if ($this->_config['event']['beforeSave']) {
                $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
            }
            $entity->setVirtual([]);
            array_push($saveData, $entity->toArray());
        }

        if (!isset($saveData[0])) {
            throw new LogicException('entities has no save data.');
        }
        $fields = array_keys($saveData[0]);

        $query = $this->_table
            ->insertQuery()
            ->insert($fields);
        $query->clause('values')->setValues($saveData);

        return $query->execute();
    }

    /**
     * execute insert select query for saving a record just once
     *
     * @param \Cake\Datasource\EntityInterface $entity insert entity
     * @param array|null $conditions search conditions
     * @return \Cake\Database\StatementInterface query result
     */
    public function insertOnce(EntityInterface $entity, ?array $conditions = null): StatementInterface
    {
        if ($this->_config['event']['beforeSave']) {
            $this->_table->dispatchEvent('Model.beforeSave', compact('entity'));
        }

        $entity->setVirtual([]);
        $insertData = $entity->toArray();
        if (isset($insertData['created'])) {
            $insertData['created'] = DateTime::now()->toDateTimeString();
        }
        if (isset($insertData['modified'])) {
            $insertData['modified'] = DateTime::now()->toDateTimeString();
        }

        $fields = array_keys($insertData);
        $existsConditions = $conditions;
        if (is_null($existsConditions)) {
            $existsConditions = $this->getExistsConditions($insertData);
        }
        $query = $this->_table->insertQuery()->insert($fields);
        $subQuery = $this
            ->buildTmpTableSelectQuery($insertData)
            ->where(function (QueryExpression $exp) use ($existsConditions) {
                $query = $this->_table
                    ->find()
                    ->where($existsConditions);

                return $exp->notExists($query);
            })
            ->limit(1);
        /* @phpstan-ignore-next-line */
        $query = $query->epilog($subQuery);

        return $query->execute();
    }

    /**
     * build tmp table's select query for insert select query
     *
     * @param array $insertData insert data
     * @return \Cake\ORM\Query\SelectQuery tmp table's select query
     * @throws \LogicException select query is invalid
     */
    private function buildTmpTableSelectQuery(array $insertData): SelectQuery
    {
        $driver = $this->_table
            ->getConnection()
            ->getDriver();
        $schema = [];
        $binds = [];
        foreach ($insertData as $key => $value) {
            $col = $driver->quoteIdentifier($key);
            if (is_null($value)) {
                $schema[] = "NULL AS $col";
            } else {
                $bindKey = ':' . strtolower($key);
                $binds[$bindKey] = $value;
                $schema[] = "$bindKey AS $col";
            }
        }

        $tmpTable = $this->fetchTable('tmp', [
            'schema' => $this->_table->getSchema(),
        ]);
        $query = $tmpTable
            ->find()
            ->select(array_keys($insertData))
            ->from(
                sprintf('(SELECT %s) as tmp', implode(',', $schema))
            );
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
