@echo off
chcp 65001 >nul
cd /d "c:\Users\Asus\assistant-sante"

echo.
echo ========================================
echo     GIT PUSH - MedicalAnalysis
echo ========================================
echo.

echo [1] Verifying repository...
git rev-parse --is-inside-work-tree >nul 2>&1
if errorlevel 1 (
    echo ERROR: Not a git repository
    exit /b 1
)

echo [2] Checking status...
git status --short

echo.
echo [3] Adding all changes...
git add -A
if errorlevel 1 (
    echo ERROR: Failed to add files
    exit /b 1
)

echo.
echo [4] Creating commit...
git commit -m "edit dashboard - Code review et optimisations MedicalAnalysis" -m "Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"
if errorlevel 1 (
    echo ERROR: Failed to commit
    exit /b 1
)

echo.
echo [5] Pushing to remote...
git push
if errorlevel 1 (
    echo ERROR: Failed to push
    exit /b 1
)

echo.
echo ========================================
echo     ✅ PUSH SUCCESSFUL!
echo ========================================
echo.

echo [6] Last commit:
git log --oneline -1

echo.
pause
