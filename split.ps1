$ErrorActionPreference = "Continue"
$packages = Get-ChildItem -Path packages -Directory
foreach ($pkg in $packages) {
    $pkgName = $pkg.Name
    $repoName = "ferrox-php-$pkgName"
    $branchName = "split-$pkgName"
    
    Write-Host "--- Processing $pkgName ---"
    
    # Check if repo exists, create if not
    gh repo view "AI-Autistic-Intelligence/$repoName" >$null 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Creating repository $repoName..."
        gh repo create "AI-Autistic-Intelligence/$repoName" --public
    }
    
    # Extract subtree
    Write-Host "Splitting $pkgName..."
    git subtree split -P "packages/$pkgName" -b $branchName
    
    # Push to remote master
    Write-Host "Pushing to $repoName (master)..."
    git push "https://github.com/AI-Autistic-Intelligence/$repoName.git" "$branchName`:master" --force
    
    # Push the tag v1.1.0 to the remote
    Write-Host "Pushing tag v1.1.0..."
    git push "https://github.com/AI-Autistic-Intelligence/$repoName.git" "$branchName`:refs/tags/v1.1.0" --force
}
Write-Host "Done!"
