pipeline {
    agent any

    // ------------------------------
    // Environment variables
    // ------------------------------
    environment {
        SSH_HOST       = "cpdb.pp.ua"                        // Remote server IP or hostname
        SSH_USER       = "jenkins"                            // Remote SSH user
        GIT_DIR        = "/srv/www/ulaf"                     // Path to Git repository on remote
        DEPLOY_DIR     = "/www/wwwroot/ulaf.com.ua"          // Deployment target directory
        SSH_CREDENTIAL = "ulaf-cpdb"                         // Jenkins SSH credentials ID
    }

    // ------------------------------
    // Trigger on GitHub push
    // ------------------------------
    triggers {
        githubPush()
    }

    stages {

        // ------------------------------
        // Deployment stage
        // ------------------------------
        stage("Deploy") {
            steps {

                // Use SSH credentials stored in Jenkins
                withCredentials([
                    sshUserPrivateKey(
                        credentialsId: "${SSH_CREDENTIAL}",
                        keyFileVariable: "SSH_KEY"
                    )
                ]) {

                    // ------------------------------
                    // Execute deployment commands over SSH
                    // ------------------------------
                    sh """
                    set -e  # Stop on any error

                    # Ensure known_hosts exists and has the server fingerprint
                    mkdir -p ~/.ssh
                    chmod 700 ~/.ssh
                    ssh-keyscan -H ${SSH_HOST} >> ~/.ssh/known_hosts

                    # Execute commands on remote server without heredoc
                    ssh -i "$SSH_KEY" \
                        -o IdentitiesOnly=yes \
                        -o StrictHostKeyChecking=accept-new \
                        ${SSH_USER}@${SSH_HOST} \
                        "set -e && \
                        cd ${GIT_DIR} && \
                        git pull --ff-only && \
                        sudo rsync -av --delete --exclude-from=.rsyncignore ${GIT_DIR}/ ${DEPLOY_DIR} && \
                        sudo chown -R www:www ${DEPLOY_DIR}"
                    """
                }
            }
        }
    }

    // ------------------------------
    // Post actions
    // ------------------------------
    post {
        success {
            echo "Deployment successful"
        }
        failure {
            echo "Deployment failed"
        }
    }
}
