$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location $projectRoot
try {
    if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) {
        throw 'Execute composer install antes de gerar o pacote.'
    }
    & composer dump-autoload --optimize --no-dev
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao gerar o autoload.' }

    $files = @('.htaccess', 'index.php', 'config/Database.php', 'config/database.example.php')
    foreach ($folder in @('app', 'core', 'public', 'vendor')) {
        $files += Get-ChildItem -LiteralPath $folder -File -Recurse -Force |
            Where-Object { $_.Name -ne '.gitkeep' -and $_.Extension -ne '.md' -and $_.FullName -notmatch '[\\/]uploads[\\/]' } |
            ForEach-Object { $_.FullName.Substring($projectRoot.Length + 1) }
    }
    New-Item -ItemType Directory -Path 'dist' -Force | Out-Null
    $destination = Join-Path $projectRoot ('dist/rastreio-ti-modulos-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.zip')
    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $zip = [System.IO.Compression.ZipFile]::Open($destination, [System.IO.Compression.ZipArchiveMode]::Create)
    try {
        $directories = @{}
        foreach ($file in $files) {
            $entryName = $file.Replace('\', '/')
            if ($entryName -match 'database\.local|^\.git|\.zip$|\.sql$') { throw "Arquivo proibido no pacote: $entryName" }
            $segments = $entryName.Split('/')
            $directory = ''
            for ($i = 0; $i -lt $segments.Length - 1; $i++) {
                $directory += $segments[$i] + '/'
                if (-not $directories.ContainsKey($directory)) {
                    $zip.CreateEntry($directory) | Out-Null
                    $directories[$directory] = $true
                }
            }
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, (Join-Path $projectRoot $file), $entryName) | Out-Null
        }
    } finally { $zip.Dispose() }
    $checkZip = [System.IO.Compression.ZipFile]::OpenRead($destination)
    try {
        foreach ($required in @('.htaccess', 'index.php', 'public/.htaccess', 'vendor/autoload.php', 'app/Services/AtivosService.php', 'config/Database.php')) {
            if ($null -eq $checkZip.GetEntry($required)) { throw "Arquivo ausente: $required" }
        }
        Write-Output "Pacote validado: $destination ($($checkZip.Entries.Count) arquivos)."
        Write-Output 'Não contém credenciais, SQL ou uploads. Preserve config/database.local.php no servidor.'
    } finally { $checkZip.Dispose() }
} finally { Pop-Location }
