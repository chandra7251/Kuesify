Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$root = Split-Path -Parent $PSScriptRoot
$dist = Join-Path $root 'dist'
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$archive = Join-Path $dist "Kuesify-Submission-$stamp.zip"
$excludedRoots = @('.git', 'dist', 'node_modules', 'vendor', 'test-results')
$excludedPaths = @('.env', 'dump.rdb', '.phpunit.result.cache', 'public/build', 'public/hot', 'storage/logs', 'storage/framework')

New-Item -ItemType Directory -Force -Path $dist | Out-Null
$zip = $null

try {
    $zip = [System.IO.Compression.ZipFile]::Open($archive, [System.IO.Compression.ZipArchiveMode]::Create)

    Get-ChildItem -Path $root -Force -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($root.Length).TrimStart('\', '/').Replace('\', '/')
        $rootName = $relative.Split('/')[0]
        $excluded = $rootName -in $excludedRoots -or $relative -in $excludedPaths
        $excluded = $excluded -or ($excludedPaths | Where-Object { $relative.StartsWith("$_/") })

        if ($excluded) {
            return
        }

        $entry = $zip.CreateEntry($relative, [System.IO.Compression.CompressionLevel]::Optimal)
        $source = [System.IO.File]::Open($_.FullName, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read, [System.IO.FileShare]::ReadWrite)
        $destination = $entry.Open()

        try {
            $source.CopyTo($destination)
        } finally {
            $destination.Dispose()
            $source.Dispose()
        }
    }
} finally {
    if ($null -ne $zip) {
        $zip.Dispose()
    }
}

Write-Output $archive