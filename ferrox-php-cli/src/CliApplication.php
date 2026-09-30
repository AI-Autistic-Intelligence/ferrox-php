<?php
namespace Ferrox\Cli;

use Ferrox\Cli\Generators\DtoGenerator;

/**
 * Main entry point for the Ferrox Command Line Interface.
 */
class CliApplication
{
    public function run(array $argv): void
    {
        $command = $argv[1] ?? 'help';

        echo "🚀 Ferrox PHP Enterprise CLI \n";
        echo "====================================\n";

        switch ($command) {
            case 'generate:dto':
                $table = $argv[2] ?? null;
                if (!$table) {
                    echo "❌ Error: Missing table name. Usage: ferrox generate:dto <table>\n";
                    exit(1);
                }
                $generator = new DtoGenerator();
                $generator->generateFromTable($table);
                break;

            case 'generate:migration':
                echo "⏳ [WIP] DB Migration generator coming soon.\n";
                break;

            case 'dr:backup-vps':
                echo "💾 Triggering VPS Database snapshot...\n";
                system("bash " . __DIR__ . "/../../ferrox-php-iac/dr/vps_backup.sh");
                break;

            case 'help':
            default:
                $this->showHelp();
                break;
        }
    }

    private function showHelp(): void
    {
        echo "Available Commands:\n";
        echo "  generate:dto <table>    - Generates a #[ValidatedDto] class from a Database Table Schema\n";
        echo "  generate:migration      - (WIP) Generate DB migrations from Entities\n";
        echo "  dr:backup-vps           - Trigger a manual backup snapshot on a VPS\n";
        echo "\n";
    }
}
