pipeline {
    agent any

    environment {
        DEPLOY_PATH = 'C:\\xampp\\htdocs\\dfsms'
    }

    stages {
        stage('Install Test Dependencies') {
            steps {
                bat 'npm.cmd install'
            }
        }

        stage('Deploy To XAMPP') {
            steps {
                bat 'if not exist "%DEPLOY_PATH%" mkdir "%DEPLOY_PATH%"'
                bat 'xcopy app "%DEPLOY_PATH%" /E /Y /I'
            }
        }

        stage('Smoke Check') {
            steps {
                bat 'powershell -NoProfile -Command "try { $r = Invoke-WebRequest -UseBasicParsing http://localhost/dfsms/ -TimeoutSec 10; if ($r.StatusCode -ne 200) { exit 1 } } catch { Write-Error $_; exit 1 }"'
            }
        }

        stage('Cypress E2E') {
            steps {
                bat 'npm.cmd run cy:run'
            }
        }
    }
}
