pipeline {
  agent none
  stages {
    stage('ssh connect') {
      steps {
        sh '''cd ~/
ssh -tt -i "tomcat-demo.pem" ec2-user@ec2-13-251-112-45.ap-southeast-1.compute.amazonaws.com'''
      }
    }
    stage('pull') {
      steps {
        sh '''cd /home/ec2-user/CEP-prod/
git pull origin master'''
      }
    }
  }
}