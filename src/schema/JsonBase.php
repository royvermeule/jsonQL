<?php

namespace src\schema;
class JsonBase
{
    private array $data;
    private array $schema;
    private ?array $select = null;
    private ?string $from = null;
    private array $where = [];
    private ?string $insert = null;
    private ?array $values = null;
    private ?string $update = null;
    private ?array $set = null;
    private array $errors = [];

    public function __construct()
    {
        if (!is_dir(__DIR__ . '/../../../../../json/JsonSchema')) {
            mkdir(__DIR__ . '/../../../../../json/JsonSchema');
        }

        if (!file_exists(__DIR__ . '/../../../../../json/JsonSchema/data.json')) {
            file_put_contents(__DIR__ . '/../../../../../json/JsonSchema/data.json', '');
        }

        $jsonData = file_get_contents(__DIR__ . '/../../../../../json/JsonSchema/data.json');
        $this->data = json_decode($jsonData, true) ?? [];

        $jsonSchema = file_get_contents(__DIR__ . '/../../../../../json/JsonSchema/schema.json');
        $this->schema = json_decode($jsonSchema, true);
    }

    public function update(string $table): self
    {
        $this->update = $table;

        return $this;
    }

    public function set(array $newData): self
    {
        $this->set = $newData;

        return $this;
    }

    public function insert(string $table): self
    {
        $this->insert = $table;

        return $this;
    }

    public function values(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function select(array $columns): self
    {
        $this->select = $columns;

        return $this;
    }

    public function from(string $table): self
    {
        $this->from = $table;

        return $this;
    }

    public function where(array $where): self
    {
        $this->where = $where;

        return $this;
    }

    public function getAssociative(): array
    {
        $results = [];

        foreach ($this->data[$this->from] as $rows) {
            $match = true;

            if (!empty($this->where)) {
                foreach ($this->where as $wichColumn => $expectedValue) {
                    if (!isset($rows[$wichColumn]) || $rows[$wichColumn] !== $expectedValue) {
                        $match = false;

                        break;
                    }
                }
            }

            if ($match) {
                foreach ($this->select as $expectedColumns) {
                    if ($expectedColumns === '*') {
                        $results = array_merge($results, $rows);
                    } else {
                        $results = array_merge($results, $rows[$expectedColumns]);
                    }
                }
            }
        }

        $this->select = null;
        $this->from = null;
        $this->where = [];

        return $results;
    }

    public function getAllAssociative(): array
    {
        $results = [];

        foreach ($this->data[$this->from] as $rows) {
            $match = true;

            if (!empty($this->where)) {
                foreach ($this->where as $wichColumn => $expectedValue) {
                    if (!isset($rows[$wichColumn]) || $rows[$wichColumn] !== $expectedValue) {
                        $match = false;

                        break;
                    }
                }
            }

            if ($match) {
                foreach ($this->select as $expectedColumns) {
                    if ($expectedColumns === '*') {
                        $results = array_merge($results, [$rows]);
                    } else {
                        $results = array_merge($results, [$rows[$expectedColumns]]);
                    }
                }
            }
        }

        $this->select = null;
        $this->from = null;
        $this->where = [];

        return $results;
    }

    public function execute(): void
    {
        if ($this->insert !== null && $this->values !== null) {
            $firstColumn = key($this->schema[$this->insert]);
            if (
                $this->schema[$this->insert][$firstColumn]['isPk'] === true &&
                $this->schema[$this->insert][$firstColumn]['isAutoIncrement'] === true
            ) {
                $this->values[$firstColumn] = mt_rand(1000, 9999);
            }

            $newRows = [$this->values];
            $allRows = [$this->values];
            if (isset($this->data[$this->insert])) {
                $allRows = array_merge($this->data[$this->insert], $newRows);
            }

            $this->data[$this->insert] = $allRows;

            $this->writeToData($this->data);

            $this->insert = null;
            $this->values = null;

            return;
        }

        if ($this->update !== null && $this->set !== null && !empty($this->where)) {
            foreach ($this->data[$this->update] as &$tableRows) {
                $matchWhere = true;

                foreach ($this->where as $whereKey => $whereValue) {
                    if (!isset($tableRows[$whereKey]) || $tableRows[$whereKey] !== $whereValue) {
                        $matchWhere = false;
                    }
                }

                if ($matchWhere) {
                    $matchSet = true;
                    foreach ($this->set as $setKey => $setValue) {
                        if (!isset($tableRows[$setKey])) {
                            $matchSet = false;
                        }

                        if ($matchSet) {
                            $tableRows[$setKey] = $setValue;
                        }
                    }
                }
            }

            $this->writeToData($this->data);

            $this->update = null;
            $this->set = null;
            $this->where = [];
        }
    }

    private function writeToData(array $data): void
    {
        file_put_contents(__DIR__ . '/../../../../../json/JsonSchema/data.json', json_encode($data), JSON_PRETTY_PRINT);
    }
}