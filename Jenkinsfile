pipeline {
    agent any

    environment {
        SSH_HOST        = "cpdb.pp.ua"
        SSH_USER        = "jenkins"

        GIT_DIR         = "/srv/www/ulaf"
        DEPLOY_DIR      = "/www/wwwroot/ulaf.com.ua"

        SSH_CREDENTIAL  = "ulaf-cpdb"
    }

    triggers {
        githubPush()
    }

    stages {

        stage("Deploy") {
            steps {
                withCredentials([
                    sshUserPrivateKey(
                        credentialsId: "${SSH_CREDENTIAL}",
                        keyFileVariable: "SSH_KEY"
                    )
                ]) {
                    sh '''
                    set -e

                    mkdir -p ~/.ssh
                    chmod 700 ~/.ssh

                    ssh-keyscan -H ${SSH_HOST} >> ~/.ssh/known_hosts

                    ssh \
                    -i "$SSH_KEY" \
                    -o IdentitiesOnly=yes \
                    -o StrictHostKeyChecking=accept-new \
                    ${SSH_USER}@${SSH_HOST} << EOF

                        set -e

                        cd ${GIT_DIR}
                        git pull --ff-only

                        sudo rsync -av --delete \
                            --exclude-from=.rsyncignore \
                            ${GIT_DIR}/ \
                            ${DEPLOY_DIR}

                        sudo chown -R www:www ${DEPLOY_DIR}
                    EOF
                    '''
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
