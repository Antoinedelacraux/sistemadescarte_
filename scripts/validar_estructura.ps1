# Comprobación de existencia y metadatos YAML sencillos. No valida el funcionamiento interno de Antigravity.
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$agents = @(Get-ChildItem (Join-Path $root '.agents/agents') -Filter 'agent.md' -Recurse -File)
$skills = @(Get-ChildItem (Join-Path $root '.agents/skills') -Filter 'SKILL.md' -Recurse -File)
$rules = @(Get-ChildItem (Join-Path $root '.agents/rules') -Filter '*.md' -File)
$ok = $true
foreach ($name in @('Agents','Skills','Rules')) {
  switch ($name) {
    'Agents' { $expected = 9; $actual = $agents.Count }
    'Skills' { $expected = 12; $actual = $skills.Count }
    'Rules' { $expected = 6; $actual = $rules.Count }
  }
  $passed = $actual -eq $expected
  if (-not $passed) { $ok = $false }
  Write-Host "${name}: esperado $expected, encontrado $actual => $(if ($passed) {'OK'} else {'ERROR'})"
}
foreach ($f in @($agents + $skills + $rules)) {
  $t = Get-Content -LiteralPath $f.FullName -Raw -Encoding UTF8
  if ($t -notmatch '(?s)^---\s*\r?\n.+?\r?\n---\s*\r?\n') {
    $ok = $false
    Write-Host "ERROR frontmatter: $($f.FullName)"
  }
}
if (-not (Test-Path (Join-Path $root 'AGENTS.md'))) { $ok=$false; Write-Host 'ERROR: AGENTS.md no existe' }
if ($ok) { Write-Host 'Estructura OK. Falta verificar reconocimiento por la versión instalada de Antigravity.'; exit 0 }
else { Write-Host 'Hay problemas estructurales.'; exit 1 }
