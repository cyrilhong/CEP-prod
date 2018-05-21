pipeline {
  agent any
  stages {
    stage('build') {
      steps {
        sh '''sh \'cd ~/\'
sh \'ssh -tt -i "tomcat-demo.pem" ec2-user@ec2-13-251-112-45.ap-southeast-1.compute.amazonaws.com\'
sh \'cd /home/ec2-user/CEP-prod/\'
sh \'git pull origin master\''''
      }
    }
  }
}