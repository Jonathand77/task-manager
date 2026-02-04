# PowerShell migration helper
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$repoRoot = Resolve-Path -Path (Join-Path $scriptDir '..')
$composeFile = Join-Path $repoRoot 'docker-compose.yml'
$migrationsDir = Join-Path $scriptDir 'database\migrations'

if (-not (Test-Path $composeFile)) {
    Write-Error "Cannot find docker-compose.yml at $composeFile"
    exit 1
}

# Read DB user and DB name from .env or .env.example
$envPath = Join-Path $repoRoot '.env'
if (-not (Test-Path $envPath)) { $envPath = Join-Path $repoRoot '.env.example' }

$pgUser = 'task_manager_user'
$pgDb = 'task_manager_db'
if (Test-Path $envPath) {
    Get-Content $envPath | ForEach-Object {
        if ($_ -match '^\s*DB_USER\s*=\s*(.+)') { $pgUser = $Matches[1].Trim() }
        if ($_ -match '^\s*DB_NAME\s*=\s*(.+)') { $pgDb = $Matches[1].Trim() }
    }
}

Write-Host "Starting postgres container (if not running)..."
docker-compose -f $composeFile up -d postgres

Write-Host "Waiting for Postgres to be ready (user: $pgUser)..."
while ($true) {
    try {
        docker-compose -f $composeFile exec -T postgres pg_isready -U $pgUser -d $pgDb > $null 2>&1
        if ($LASTEXITCODE -eq 0) { break }
    } catch {
        # ignore and retry
    }
    Start-Sleep -Seconds 1
}

Write-Host "Applying migrations from $migrationsDir"
Get-ChildItem -Path $migrationsDir -Filter *.sql | Sort-Object Name | ForEach-Object {
    $fileName = $_.Name
    Write-Host "Applying $fileName"
    docker-compose -f $composeFile exec -T postgres psql -U $pgUser -d $pgDb -f /docker-entrypoint-initdb.d/$fileName | Out-Null
}

Write-Host "Migrations finished."