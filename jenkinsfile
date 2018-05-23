pipeline {
  agent any
  stages {
    stage('pull latest code') {
      steps {
        sh '''cd ~/
ssh -tt -i "tomcat-demo.pem" ec2-user@ec2-13-251-112-45.ap-southeast-1.compute.amazonaws.com bash test.sh'''
      }
    }
  }
}