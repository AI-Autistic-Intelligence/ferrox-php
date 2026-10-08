$token = "521f8cb5217d5f5cac123379c2ff233daae5dd2d"
$username = "AI-Autistic-Intelligence"
$repos = @(
    "auth", "broadcasting", "cli", "config", "core", "cqrs", "crud-gen", "data", "database-core", "events"
)

foreach ($repo in $repos) {
    $url = "https://github.com/AI-Autistic-Intelligence/ferrox-php-$repo"
    $body = @{
        repository = @{
            url = $url
        }
    } | ConvertTo-Json

    try {
        $response = Invoke-RestMethod -Uri "https://packagist.org/api/create-package?username=$username&apiToken=$token" -Method Post -Body $body -ContentType "application/json"
        Write-Host "Successfully submitted $url to Packagist!"
    } catch {
        Write-Host "Failed to submit $url : $_"
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $reader.BaseStream.Position = 0
            $reader.DiscardBufferedData()
            $responseBody = $reader.ReadToEnd()
            Write-Host "Response Body: $responseBody"
        }
    }
}
