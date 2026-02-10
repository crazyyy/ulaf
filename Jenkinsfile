pipeline {
    agent any

    // ------------------------------
    // Global environment variables
    // ------------------------------
    environment {
        SSH_HOST       = "cpdb.pp.ua"              // Remote server hostname
        SSH_USER       = "jenkins"                 // SSH user on remote server
        GIT_DIR        = "/srv/www/ulaf"            // Git repository path on remote host
        DEPLOY_DIR     = "/www/wwwroot/ulaf.com.ua" // Web root / deploy target
        SSH_CREDENTIAL = "ulaf-cpdb"                // Jenkins SSH credentials ID
    }

    // ------------------------------
    // Trigger pipeline on GitHub push
    // ------------------------------
    triggers {
        githubPush()
    }

    stages {

        // ------------------------------
        // Main deployment stage
        // ------------------------------
        stage("Deploy") {
            steps {

                // Inject SSH private key from Jenkins credentials
                withCredentials([
                    sshUserPrivateKey(
                        credentialsId: SSH_CREDENTIAL,
                        keyFileVariable: "SSH_KEY"
                    )
                ]) {

                    sh """
                    set -e  # Exit immediately if any command fails

                    # Prepare SSH known_hosts to avoid interactive prompt
                    mkdir -p ~/.ssh
                    chmod 700 ~/.ssh
                    ssh-keyscan -H ${SSH_HOST} >> ~/.ssh/known_hosts

                    # Run deployment commands on the remote server
                    ssh -i "\$SSH_KEY" \\
                        -o IdentitiesOnly=yes \\
                        -o StrictHostKeyChecking=accept-new \\
                        ${SSH_USER}@${SSH_HOST} \\
                        "set -e && \\
                        cd ${GIT_DIR} && \\
                        git pull --ff-only && \\
                        sudo rsync -av --delete --exclude-from=.rsyncignore ${GIT_DIR}/ ${DEPLOY_DIR} && \\
                        sudo chown -R www:www ${DEPLOY_DIR} && \\
                        echo 'WordPress stack deployed successfully'"
                    """
                }
            }
        }
    }

    // ------------------------------
    // Post-build actions
    // ------------------------------
    post {
        success {
            echo "Pipeline completed successfully."
        }
        failure {
            echo "Deployment failed. Check Jenkins logs."
        }
    }
}
