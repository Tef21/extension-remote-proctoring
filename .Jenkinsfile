pipeline {
    agent {
        label 'builder'
    }
    stages {
        stage('Resolve TAO dependencies') {
            environment {
                GITHUB_ORGANIZATION='oat-sa'
                REPO_NAME='oat-sa/extension-remote-proctoring'
            }
            steps {
                sh(
                    label : 'Create build build directory',
                    script: 'mkdir -p build'
                )
            }
        }
        stage('Install') {
            agent {
                docker {
                    image 'alexwijn/docker-git-php-composer'
                    reuseNode true
                }
            }
            environment {
                HOME = '.'
            }
            options {
                skipDefaultCheckout()
            }
            steps {
                dir('build') {
                    sh(
                        label : 'Change composer minimum stability',
                        script: 'composer config minimum-stability dev'
                    )
                    sh(
                        label : 'Change composer prefer-stable option',
                        script: 'composer config prefer-stable true'
                    )
                    sh(
                        label: 'Install/Update sources from Composer',
                        script: 'COMPOSER_DISCARD_CHANGES=true composer update --no-interaction --no-ansi --no-progress --no-scripts'
                    )
                    sh(
                        label: "Extra filesystem mocks",
                        script: '''
mkdir -p taoQtiItem/views/js/mathjax/ && touch taoQtiItem/views/js/mathjax/MathJax.js
mkdir -p tao/views/locales/en-US/
    echo "{\\"serial\\":\\"${BUILD_ID}\\",\\"date\\":$(date +%s),\\"version\\":\\"3.3.0-${BUILD_NUMBER}\\",\\"translations\\":{}}" > tao/views/locales/en-US/messages.json
mkdir -p tao/views/locales/en-US/
                        '''
                    )
                }
            }
        }
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
                stage('Frontend Tests') {
                    agent {
                        docker {
                            image 'btamas/puppeteer-git'
                            reuseNode true
                        }
                    }
                    environment {
                        HOME = '.'
                    }
                    options {
                        skipDefaultCheckout()
                    }
                    steps {
                        dir('build/tao/views'){
                            sh(
                                label: 'Ensure FE resource are available',
                                script: 'npm install --production'
                            )
                        }
                        dir('build/tao/views/build') {
                            sh(
                                label: 'Setup frontend toolchain',
                                script: 'npm install'
                            )
                            sh (
                                label : 'Run frontend tests',
                                script: 'npx grunt connect:test taotest'
                            )
                        }
                    }
                }
            }
        }
    }
}
