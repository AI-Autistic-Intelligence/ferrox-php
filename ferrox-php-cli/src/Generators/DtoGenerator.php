<?php
namespace Ferrox\Cli\Generators;

/**
 * Scaffolds Data Transfer Objects automatically based on Database Schemas,
 * enforcing Ferrox validation patterns.
 */
class DtoGenerator
{
    public function generateFromTable(string $tableName): void
    {
        // Simulated DB inspection (Information Schema). 
        // In a real scenario, PDO is injected and we query `DESCRIBE $tableName`.
        $columns = [
            ['name' => 'id', 'type' => 'uuid', 'nullable' => false],
            ['name' => 'title', 'type' => 'varchar', 'nullable' => false],
            ['name' => 'price', 'type' => 'decimal', 'nullable' => false],
            ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true],
        ];

        $className = $this->toPascalCase($tableName) . 'Dto';
        
        $code = "<?php\nnamespace App\Dto;\n\n";
        $code .= "use Ferrox\Validation\Attributes\ValidatedDto;\n\n";
        $code .= "#[ValidatedDto(strict: true)]\n";
        $code .= "class {$className} {\n";
        $code .= "    public function __construct(\n";

        $props = [];
        foreach ($columns as $col) {
            $phpType = $this->mapSqlTypeToPhp($col['type']);
            $nullMark = $col['nullable'] ? '?' : '';
            $props[] = "        public {$nullMark}{$phpType} \${$col['name']}";
        }
        
        $code .= implode(",\n", $props) . "\n";
        $code .= "    ) {}\n";
        $code .= "}\n";

        // Write to src/Dto/ directory
        $outputDir = getcwd() . '/src/Dto';
        if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);
        
        $filePath = $outputDir . '/' . $className . '.php';
        file_put_contents($filePath, $code);

        echo "✅ Generated DTO successfully at: {$filePath}\n";
    }

    private function mapSqlTypeToPhp(string $sqlType): string
    {
        return match ($sqlType) {
            'int', 'bigint', 'tinyint' => 'int',
            'decimal', 'float', 'double' => 'float',
            'boolean', 'tinyint(1)' => 'bool',
            default => 'string',
        };
    }

    private function toPascalCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }
}
