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

4. Add the following code to your .htaccess file.

        $ nano .htaccess

Paste the following:

        # deny direct access
        order deny,allow
        deny from all

        # allow access from these Bitbucket IPs
        allow from 131.103.20.160/27
        allow from 165.254.145.0/26
        allow from 165.254.145.0/26

        # php errors
        php_flag display_startup_errors on
        php_flag display_errors on
        php_flag html_errors on
        php_flag log_errors on
        php_value error_log php_errors.log



