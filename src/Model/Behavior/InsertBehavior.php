<?php

namespace Itosho\EasyQuery\Model\Behavior;

use Cake\ORM\Behavior;
use LogicException;

class InsertBehavior extends Behavior
{
    protected $_defaultConfig = [
        'event' => ['beforeSave' => false]
    ];

    /**
     * execute bulk insert query
     *
     * @param \Cake\ORM\Entity[] $entities insert entities
     *
     * @return \Cake\Database\StatementInterface query result
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
        $query->clause('values')->values($saveData);

        return $query->execute();
    }
}
