<?php

namespace bin\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteMigrations extends Command
{
    private string $commandName = 'execute:migrations';

    public function configure(): void
    {
        $this->setName($this->commandName);
        $this->setDescription('Executes all the json migrations');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonMigration = json_decode(file_get_contents(__DIR__ . '/utility/json/migrations.json'), true);
        $migrationsPath = __DIR__ . '/../../../../../json/JsonSchema/migrations/';
        $schemaBuilder = new \src\schema\SchemaBuilder();

        foreach ($jsonMigration as $migration) {
            $migrationClassName = $migration['migrationName'];

            $migrationClassFile = $migrationsPath . $migrationClassName . '.php';
            if (file_exists($migrationClassFile)) {
                require_once $migrationClassFile;

                $migrationClass = new $migrationClassName();
                if ($migrationClass instanceof \bin\commands\utility\Migration) {
                    $migrationClass->__invoke($schemaBuilder);

                    $schemaBuilder->build();
                }
            }
        }

        return Command::SUCCESS;
    }
}
