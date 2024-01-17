<?php

declare(strict_types=1);

namespace src\schema;

class SchemaBuilder
{
    private static string $currentTable;
    private static array $tables;
    private array $constraints;

    public function addTable(string $tableName): void
    {
        self::$currentTable = $tableName;
        self::$tables[$tableName] = [];
    }

    public function addColumn(
        string $name,
        string $type,
        int $length,
        bool $isAutoIncrement = false,
        bool $isPk = false,
        bool $isFk = false,
    ): void
    {
        self::$tables[self::$currentTable][$name] = [
            'type' => $type,
            'length' => $length,
            'isAutoIncrement' => $isAutoIncrement,
            'isPk' => $isPk,
            'isFk' => $isFk
        ];
    }

    public function addForeignKeyConstraint(
        string $foreignTable,
        string $foreignColumnName,
        string $localColumnName,
        bool $onDeleteRestrict = true,
        bool $onDeleteCascade = false
    ): void
    {
        $this->constraints[self::$currentTable][$localColumnName][$foreignTable][$foreignColumnName] = [
            'onDeleteRestrict' => $onDeleteRestrict,
            'onDeleteCascade' => $onDeleteCascade
        ];

        self::$tables[self::$currentTable]['constraints'][$localColumnName][$foreignTable][$foreignColumnName] = [
            'onDeleteRestrict' => $onDeleteRestrict,
            'onDeleteCascade' => $onDeleteCascade
        ];
    }

    public function getSchema(): string
    {
        return json_encode(self::$tables);
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