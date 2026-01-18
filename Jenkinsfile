pipeline {
    agent any

    environment {
        REPO_PATH   = "/srv/www/ulaf"
        DEPLOY_PATH = "/srv/www/ulaf2"
        SSH_HOST    = "cpdb.pp.ua"
        SSH_CRED_ID = "ulaf-cpdb"
    }

    triggers {
        githubPush()
    }

    stages {
        // stage('Checkout') {
        //     steps {
        //         checkout scm
        //     }
        // }

        stage('Deploy via SSH') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: 'ulaf-cpdb', keyFileVariable: 'KEYFILE', usernameVariable: 'SSH_USER')]) {
                    sh """
                        set -e
                        mkdir -p ~/.ssh
                        chmod 700 ~/.ssh

                        ssh-keyscan -H ${SSH_HOST} >> ~/.ssh/known_hosts

                        ssh -o IdentitiesOnly=yes ${SSH_USER}@${SSH_HOST} -i ${KEYFILE} << EOF
                        set -e
                        ssh-agent -s
                        ssh-add ~/.ssh/id_ed25519
                        cd /srv/www/ulaf
                        git pull
                        rsync -av --delete ${REPO_PATH}/ ${DEPLOY_PATH}/
                        chown -R www:www ${DEPLOY_PATH}
                        EOF
                    """
                }
            }
        }
    }

    post {
        success {
            echo "Deployment successful"
        }
        failure {
            echo "Deployment failed"
        }
    }
}
