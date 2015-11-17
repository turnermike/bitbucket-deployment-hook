# Bitbucket Webhooks Script

This script will use Bitbucket Webhooks to copy files from your git repository to directories on the server.

## Local Repository

1. Create a branch named 'staging' and push that to remote (origin).

        $ git checkout -b staging
        $ git push origin staging

## Remote Server Setup

1. Clone your repo a directory up from the public directory. SSH into the server and find the directory one up from the public html directory. Add a '.git' extension to the directory name so we know it's a git repo.

        $ git clone git@bitbucket.org:username/repo-name.git repo-name.git

2. Create a directory within your public folder for the deployment script and then change into that new directory. Please navigate to your public html directory via command line.

        $ mkdir .deployment
        $ cd .deployment

3. Create files/directories for the deployment script.

        $ touch .htaccess
        $ touch bitbucket-hook.php
        $ touch deployments.log
        $ touch index.html
        $ mkdir backups

4. Add some code to your .htaccess file to only allow Bitbucket's servers access to the script and enable PHP error reporting.

        $ nano .htaccess

        Copy and paste the code from .htaccess in this repo to your new file.


5. Add some code to your index.html file. It's just a directory index, no body should be able to access it, but just incase we'll add a message.

        $ nano index.html

        Copy and paste the code from index.html in this repo to your new file.

6. Add the deployment script code.

    $ nano bitbucket-hook.php

    Copy and paste the code from bitbucket-hook.php in this repo to your new file.

7. The log file (deployment.log) may be left blank as the hook script will be writting to it.

## Hook Script Setup

1. Over the next few steps we will require some server paths. I suggest you open a text editor and jot down your public/html path, the path to your git repository on the server, and the path to the directory one level up from your public/html directory. We'll call this your root path.

For example:  
Root Directory: /var/www/vhosts/yourname.com  
Public Directory: /var/www/vhosts/yourname.com/httpdocs  
Repo Directory: /var/www/vhosts/yourname.com/yourname-STAGING.git  

2. Add the deployment hook code. If you're not already editing bitbucket-hook.php via nano, please do so.

		$ nano bitbucket-hook.php

3. Copy the PHP code from bitbucket-hook.php and paste into nano.

4. Next we need to populate the scripts settings variables. The most important variables here are the server paths and branch.

		private $_branch = 'staging';   // the git branch to pull from (your server environment)

private $_remote = 'origin';    // name of the git remote to pull from (leave as origin)
private $_root_dir = '';        // path to the directory a level up from your public/html directory
private $_public_dir = '';      // path to the public/html directory
private $_repo_dir = '';        // path to the git repository directory on server
private $_log = '';             // log file name
private $_date_format = '';     // used in log file


































