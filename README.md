# Bitbucket Webhooks Script

This script will use Bitbucket Webhooks to copy files from your git repository to directories on the server. Before updating any code from the repository the script will backup the existing code to a folder under .deployments/backups/.

In this example, we will be setting up a deployment script for our staging server.

## Local Repository

1. Create a branch named 'staging' and push that to remote (origin).

        $ git checkout -b staging
        $ git push origin staging

## Remote Server Setup

1. Clone your repo a level up from the public directory. SSH into the server and find the directory one up from the public html directory. Add a '.git' extension to the directory name so we know it's a git repo.

        $ git clone git@bitbucket.org:username/repo-name.git repo-name.git

2. Create a directory within your public folder for the deployment script and then change into that new directory. Please navigate to your public html directory via command line.

        $ cd yourname.com
        $ mkdir .deployment
        $ cd .deployment

3. Create files/directories for the deployment script.

        $ touch .htaccess
        $ touch bitbucket-hook.php
        $ touch deployments.log
        $ touch index.html

4. Add some code to your .htaccess file to only allow Bitbucket's servers access to the script and enable PHP error reporting.

        $ nano .htaccess

        Copy and paste the code from .htaccess in this repo to your new file.


5. Add some code to your index.html file. It's just a directory index, no body should be able to access it, but just incase we'll add a message.

        $ nano index.html

        Copy and paste the code from index.html in this repo to your new file.

6. The log file (deployment.log) may be left blank as the hook script will be writting to it.

## Hook Script Setup

1. Over the next few steps we will require some server paths. I suggest you open a text editor and jot down your public/html path, the path to your git repository on the server, and the path to the directory one level up from your public/html directory. We'll call this your root path.

	For example:  
	Root Directory: /var/www/vhosts/yourname.com  
	Public Directory: /var/www/vhosts/yourname.com/httpdocs  
	Repo Directory: /var/www/vhosts/yourname.com/yourname-STAGING.git  
        Code Igniter: /var/www/vhosts/yourname.com/yourname-codeigniter  

2. Add the deployment hook code. If you're not already editing bitbucket-hook.php via nano, please do so.

	$ nano bitbucket-hook.php

3. Copy the PHP code from bitbucket-hook.php and paste into nano.

4. Next we need to populate the scripts settings variables. The most important variables here are the server paths and branch.

        private $_branch = 'staging';         // the git branch to pull from (your server environment)
        private $_remote = 'origin';          // name of the git remote to pull from (leave as origin)
        private $_enable_backups = false;     // enable/disable backups - backups will be saved to .deployment/backups after each deployment
        private $_root_dir = '';              // path to the directory a level up from your public/html directory
        private $_public_dir = '';            // path to the public/html directory
        private $_repo_dir = '';              // path to the git repository directory on server
        private $_log = 'deployments.log';    // log file name
        private $_date_format = 'Y-m-d H:i:s';// used in log file

5. Save bitbucket-hook.php.

# Add Bitbucket Webhook URL

Next, we'll need to add the Webhook to BitBucket. This is a URL to the bitbucket-hook.php script we created previously. Follow the steps below to add your Webhook:

1. Login to Bitbucket and find your repository page.
2. Click Settings from the left menu.
3. Slick Webhooks from the secondary menu.
4. Click the Add Webhook button.
5. For Title, type "Staging".
6. Enter the URL to your script in the URL field. For example: http://yourname.com/.deployment/bitbucket-hook.php
7. Click Save.

# MySQL Dumps/Imports (optional)

If you have a MySQL database to migrate to the server. Please follow these steps to use client side Git Hooks for automating the process:

1. Open terminal and navigate to your project directory.
2. Change into the Git 'hooks' directory.
        
        $ cd .git/hooks

3. Create a file named 'pre-commit' and 'post-commit'. These names suggest that the 'pre-commit' script will be run before the commit and 'post-commit' will be run after.

        $ touch pre-commit
        $ touch post-commit

4. Copy and paste the code from each file in this repo's 'client-side-hooks' directory to your new files.
5. Edit pre-commit to add your local database settings. You'll need the path to your local deploy/public directory and your MySQL credentials and database name.
6. Edit post-commnit to add your remote database settings. You'll need the same path used in the previous step, and your remote MySQL credentials and database name.





# Deploy!

Simply push your local 'staging' branch to origin and your files should be up there.

# Credits

Brandon Summers - [Using Bitbucket for Automated Deployments](http://brandonsummers.name/blog/2012/02/10/using-bitbucket-for-automated-deployments/)
Atlassain - [Managing Webhooks](https://confluence.atlassian.com/bitbucket/manage-webhooks-735643732.html)




































