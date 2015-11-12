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

3. Create files for the deployment script.

        $ touch .htaccess
        $ touch bitbucket-hook.php
        $ touch deployments.log
        $ touch index.html

4. Add some code to your .htaccess file to only allow Bitbucket's servers access to the script and enable PHP error reporting.

        $ nano .htaccess

        Copy and paste the code from .htaccess in this repo to your new file.


5. Add some code to your index.html file. It's just a directory index, no body should be able to access it, but just incase we'll add a message.

        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                <title></title>
                <meta name="description" content="">
                <meta name="viewport" content="width=device-width">
            </head>
            <body>
                <p>nothing to see here</p>
            </body>
        </html>






