<?php

declare(strict_types=1);

namespace src\schema;

class SchemaBuilder
{
    private static string $currentTable;
    private static array $tables;
    private array $constraints = [];

    public function addTable(string $tableName): self
    {
        self::$currentTable = $tableName;
        self::$tables[$tableName] = [];

        return $this;
    }

    public function addColumn(
        string $name,
        string $type,
        int $length,
        bool $isAutoIncrement = false,
        bool $isPk = false,
        bool $isFk = false,
    ): self
    {
        self::$tables[self::$currentTable][$name] = [
            'type' => $type,
            'length' => $length,
            'isAutoIncrement' => $isAutoIncrement,
            'isPk' => $isPk,
            'isFk' => $isFk
        ];

        return $this;
    }

    public function addForeignKeyConstraint(
        string $foreignTable,
        string $foreignColumnName,
        string $localColumnName,
        bool $onDeleteRestrict = true,
        bool $onDeleteCascade = false
    ): self
    {
        $this->constraints[self::$currentTable][$localColumnName][$foreignTable][$foreignColumnName] = [
            'onDeleteRestrict' => $onDeleteRestrict,
            'onDeleteCascade' => $onDeleteCascade
        ];

        self::$tables[self::$currentTable]['constraints'][$localColumnName][$foreignTable][$foreignColumnName] = [
            'onDeleteRestrict' => $onDeleteRestrict,
            'onDeleteCascade' => $onDeleteCascade
        ];

        return $this;
    }

    public function build(string $path = 'json'): void
    {
        if (!is_dir($path . '/JsonSchema')) {
            if (!mkdir($path . '/JsonSchema', 0777, true)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path . '/JsonSchema'));
            }
        }

        file_put_contents($path . '/JsonSchema/schema.json', json_encode(self::$tables));
        file_put_contents($path . '/JsonSchema/constraints.json', json_encode($this->constraints));
    }
}