$pkgs = @('auth', 'broadcasting', 'cli', 'gateway', 'mailer', 'observability', 'queue', 'rpc', 'scheduler', 'storage')

foreach ($pkg in $pkgs) {
    if (-not (Test-Path "packages\$pkg\composer.json")) {
        $pascalCase = (Get-Culture).TextInfo.ToTitleCase($pkg)
        if ($pkg -eq 'cli') { $pascalCase = 'Cli' }
        if ($pkg -eq 'rpc') { $pascalCase = 'Rpc' }
        if ($pkg -eq 'iac') { $pascalCase = 'Iac' }
        
        $json = @"
{
    "name": "ferrox/ferrox-php-$pkg",
    "description": "Ferrox PHP $pascalCase module",
    "type": "library",
    "require": {
        "php": ">=8.3"
    },
    "autoload": {
        "psr-4": {
            "Ferrox\\$pascalCase\\": "src/"
        }
    }
}
"@
        Set-Content -Path "packages\$pkg\composer.json" -Value $json
    }
}
