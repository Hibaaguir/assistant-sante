# Git Push Script
$repoPath = "c:\Users\Asus\assistant-sante"
Set-Location $repoPath

Write-Host "📂 Repertoire: $(Get-Location)" -ForegroundColor Cyan
Write-Host ""

# Vérifier status
Write-Host "📋 Status Git:" -ForegroundColor Yellow
git status --short
Write-Host ""

# Ajouter tous les fichiers
Write-Host "➕ Ajout de tous les fichiers modifiés..." -ForegroundColor Yellow
git add -A

Write-Host ""
Write-Host "✅ Fichiers ajoutés" -ForegroundColor Green
Write-Host ""

# Commit avec message
$commitMessage = "edit dashboard - Code review et optimisations MedicalAnalysis"
Write-Host "💾 Création du commit..." -ForegroundColor Yellow
Write-Host "Message: $commitMessage" -ForegroundColor Cyan

git commit -m "$commitMessage" -m "Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"

Write-Host ""
Write-Host "🚀 Push des changements..." -ForegroundColor Yellow

# Push
git push

Write-Host ""
Write-Host "✅ Push complété!" -ForegroundColor Green
Write-Host ""

# Afficher le dernier commit
Write-Host "📍 Dernier commit:" -ForegroundColor Cyan
git log --oneline -1
