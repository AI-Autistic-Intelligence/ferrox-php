$ErrorActionPreference = "Continue"
$token = "521f8cb5217d5f5cac123379c2ff233daae5dd2d"
$username = "AI-Autistic-Intelligence"
$pkgs = @('gateway', 'mailer', 'observability', 'queue', 'rate-limiter', 'rpc', 'scheduler', 'security', 'storage', 'utils', 'validation')

foreach ($pkg in $pkgs) {
    $repoName = "ferrox-php-$pkg"
    $branchName = "split-$pkg"
    $url = "https://github.com/AI-Autistic-Intelligence/$repoName"

    Write-Host "--- Creazione e Split per $pkg ---"
    
    # Crea repo
    gh repo view "AI-Autistic-Intelligence/$repoName" >$null 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Creazione repository su GitHub..."
        gh repo create "AI-Autistic-Intelligence/$repoName" --public
        if ($LASTEXITCODE -ne 0) {
            Write-Host "Rate limit ancora attivo. Riproviamo tra un po'!"
            exit
        }
    }
    
    # Split e Push
    git subtree split -P "packages/$pkg" -b $branchName
    git push "$url.git" "$branchName`:master" --force
    git push "$url.git" "$branchName`:refs/tags/v1.1.0" --force
    
    # Packagist
    Write-Host "Invio a Packagist API..."
    $body = @{ repository = @{ url = $url } } | ConvertTo-Json
    try {
        Invoke-RestMethod -Uri "https://packagist.org/api/create-package?username=$username&apiToken=$token" -Method Post -Body $body -ContentType "application/json"
        Write-Host "Successo per $pkg su Packagist!"
    } catch {
        Write-Host "Fallito invio a Packagist: $_"
    }
}
Write-Host "Tutti i pacchetti restanti sono stati pubblicati!"
