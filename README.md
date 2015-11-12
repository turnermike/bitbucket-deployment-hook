# Bitbucket Webhooks Script

This script will use Bitbucket Webhooks to copy files from your git repository to directories on the server.

## Local Repository

1. Create a branch named 'staging' and push that to remote.

        $ git checkout -b staging
        $ git push origin staging

## Remote Server

1. Clone your repo a directory up from the public directory. SSH into the server and find the directory one up from the public html directory. Add a '.git' extension to the directory name so we know it's a git repo.

        $ git clone git@bitbucket.org:username/repo-name.git repo-name.git

2. Create a directory within your public folder for the deployment script and then change into that new directory. Please navigate to your public html directory via command line.

        $ mkdir .deployment
        $ cd .deployment




