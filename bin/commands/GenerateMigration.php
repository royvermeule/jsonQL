<?php

namespace bin\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigration extends Command
{
    private string $commandName = 'generate:migrations';
    private string $jsonPath = __DIR__ . '/../../../../../json/JsonSchema/';

    public function configure(): void
    {
        $this->setName($this->commandName);
        $this->setDescription('generates a new jsonQL migration');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_dir($this->jsonPath . 'migrations')) {
            mkdir($this->jsonPath . 'migrations');
        }

        $migrationShell = file_get_contents(__DIR__ . '/utility/migrationShell.txt');
        $migrationName = $this->generateMigrationName();
        $migration = str_replace('--name--', $migrationName, $migrationShell);

        file_put_contents($this->jsonPath . 'migrations/' . $migrationName . '.php', $migration);

        $registeredMigrations = null;
        if (file_exists(__DIR__ . '/utility/json/migrations.json')) {
            $registeredMigrations = json_decode(file_get_contents(__DIR__ . '/utility/json/migrations.json'), true);
        }

        $newMigrations = [
          [
              'migrationName' => $migrationName
          ]
        ];

        if ($registeredMigrations !== null) {
            $finalMigrations = array_merge($registeredMigrations, $newMigrations);
        } else {
            $finalMigrations = $newMigrations;
        }

        file_put_contents(__DIR__ . '/utility/json/migrations.json', json_encode($finalMigrations, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }


    private function generateMigrationName(): string
    {
        $migrationNumber = mt_rand(1000, 9999);

        return 'Migration' . $migrationNumber;
    }
}