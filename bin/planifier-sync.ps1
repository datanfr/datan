<#
.SYNOPSIS
    Planifie la mise à jour quotidienne de la base datan.

.DESCRIPTION
    Enregistre une tâche Windows qui lance bin\sync-quotidien.bat chaque jour.

    L'heure par défaut est 6 h : les Tricoteuses remoissonnent l'open data de
    l'Assemblée dans la nuit, la base est donc à jour au réveil. La tâche
    rattrape les exécutions manquées (machine éteinte), ce qui compte pour un
    poste de travail.

.EXAMPLE
    pwsh -File bin\planifier-sync.ps1
    pwsh -File bin\planifier-sync.ps1 -Heure 05:30
    pwsh -File bin\planifier-sync.ps1 -Supprimer
#>
[CmdletBinding()]
param(
    [string] $Nom = 'datan-sync-quotidien',
    [string] $Heure = '06:00',
    [switch] $Supprimer
)

$ErrorActionPreference = 'Stop'

$projet = Split-Path -Parent $PSScriptRoot
$script = Join-Path $PSScriptRoot 'sync-quotidien.bat'

if ($Supprimer) {
    Unregister-ScheduledTask -TaskName $Nom -Confirm:$false
    Write-Host "Tâche « $Nom » supprimée."
    return
}

if (-not (Test-Path $script)) {
    throw "Script introuvable : $script"
}

$action = New-ScheduledTaskAction -Execute $script -WorkingDirectory $projet
$declencheur = New-ScheduledTaskTrigger -Daily -At $Heure
$reglages = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -DontStopIfGoingOnBatteries `
    -AllowStartIfOnBatteries `
    -ExecutionTimeLimit (New-TimeSpan -Hours 2) `
    -MultipleInstances IgnoreNew

Register-ScheduledTask `
    -TaskName $Nom `
    -Action $action `
    -Trigger $declencheur `
    -Settings $reglages `
    -Description 'Moissonne les données des Tricoteuses et met à jour la base datan.' `
    -Force | Out-Null

Write-Host "Tâche « $Nom » planifiée tous les jours à $Heure."
Write-Host "Journal : $projet\var\log\sync-quotidien.log"
Write-Host "Exécution immédiate pour test : Start-ScheduledTask -TaskName $Nom"
