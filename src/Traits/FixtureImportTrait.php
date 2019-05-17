<?php
namespace Aircargobook\CakephpFixtureImporter\Traits;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

trait FixtureImportTrait
{
    /**
     * @return void
     */
    public function insertDataFromSourceTable()
    {
        if (!isset($this->import['table']) || !isset($this->import['connection'])) {
            return;
        }

        if (empty($this->import['table']) || empty($this->import['connection'])) {
            return;
        }

        $_sourceConnection = ConnectionManager::get($this->import['connection'], false);
        $_model = TableRegistry::get($this->import['table'], ['connection' => $_sourceConnection]);

        $jsonFields = [];
        $_schema = $_model->schema();
        foreach ($_schema->typeMap() as $field => $type) {
            if ($type === 'json') {
                $jsonFields[] = $field;
            }
        }

        if ($this->import['table'] == 'routes') {
            $_model->setRequestingCompany('system');
        }

        $results = $_model->find('all')->hydrate(false);

        foreach ($results as $result) {
            foreach ($jsonFields as $jsonField) {
                $result[$jsonField] = json_encode($result[$jsonField]);
            }

            $this->records[] = $result; //->toArray();
        }

        // removeing table from table registry
        TableRegistry::remove($this->import['table']);

        $_targetConnection = ConnectionManager::get($this->connection(), false);

        // Creating Table from source to target
        $this->drop($_targetConnection);
        $this->create($_targetConnection);

        // Inserting Data in Table from source to target
        $this->insert($_targetConnection);
    }
}
