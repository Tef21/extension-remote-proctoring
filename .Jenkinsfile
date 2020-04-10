pipeline {
    agent {
        label 'builder'
    }
    stages {
        stage('Tests') {
            parallel {
                stage('Backend Tests') {
                    agent {
                        docker {
                            image 'alexwijn/docker-git-php-composer'
                            reuseNode true
                        }
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build'){
                            sh(
                                label: 'Run backend tests',
                                script: './vendor/bin/phpunit remoteProctoring/test/unit'
                            )
                        }
                    }
                }
            }
        }
    }
}
