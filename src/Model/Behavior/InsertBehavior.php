<?php

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\ORM\Behavior;
use LogicException;

class InsertBehavior extends Behavior
{
    /**
     * execute bulk insert query
     *
     * @param array $entities insert entities
     *
     * @return \Cake\Database\StatementInterface query result
     */
    public function bulkInsert(array $entities)
    {
        $saveData = [];
        foreach ($entities as $entity) {
            $saveData[] = $entity->toArray();
        }

        if (!isset($saveData[0])) {
            throw new LogicException('entities has no save data.');
        }
        $fields = array_keys($saveData[0]);

        $query = $this->_table
            ->query()
            ->insert($fields);
        $query->clause('values')->values($saveData);

        return $query->execute();
    }
}
