<?php

//date_default_timezone_set('America/Toronto');


class Deploy {

    /**
     * A callback function to call after the deploy has finished.
     *
     * @var callback
     */
    // public $post_deploy;

    /**
     * The name of the branch to pull from.
     *
     * @var string
     */
    private $_branch = 'staging';

    /**
     * The name of the remote to pull from.
     *
     * @var string
     */
    private $_remote = 'origin';

    /**
     * Enable backups of each past deployment.
     * Backups saved to .deployment/backups
     *
     * @var boolean
     */
    private $_enable_backups = false;

    /**
     * The directory above the public directory.
     * Used to store codeigniter folder and git repo.
     *
     * @var string
     */
    private $_root_dir = '/var/www/vhosts/ristaging.ca';

    /**
     * The public/html directory.
     *
     * @var string
     */
    private $_public_dir = '/var/www/vhosts/ristaging.ca/contest-templates.ristaging.ca';

    /**
     * The git repo directory.
     *
     * @var string
     */
    private $_repo_dir = '/var/www/vhosts/ristaging.ca/contest-templates-STAGING.git';

    /**
     * The name of the file that will be used for logging deployments. Set to
     * FALSE to disable logging.
     *
     * @var string
     */
    private $_log = 'deployments.log';

    /**
     * The timestamp format used for logging.
     *
     * @link    http://www.php.net/manual/en/function.date.php
     * @var     string
     */
    private $_date_format = 'Y-m-d H:i:s';

    /**
     * Sets up defaults.
     *
     * @param  array   $data       Information about the deployment
     */
    public function __construct($options = array())
    {

        $this->log("\n================================================================================================\n");
        $this->log('Attempting deployment...');

    }

    /**
     * Writes a message to the log file.
     *
     * @param  string  $message  The message to write
     * @param  string  $type     The type of log message (e.g. INFO, DEBUG, ERROR, etc.)
     */
    public function log($message, $type = 'INFO')
    {
        if ($this->_log)
        {
            // Set the name of the log file
            $filename = $this->_log;

            if ( ! file_exists($filename))
            {
                // Create the log file
                file_put_contents($filename, '');

                // Allow anyone to write to log files
                chmod($filename, 0666);
            }

            // Write the message into the log file
            // Format: time --- type: message
            file_put_contents($filename, date($this->_date_format) . ' --- ' . $type . ': ' . $message . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Executes the necessary commands to deploy the website.
     */
    public function execute()
    {

        try
        {

            // output settings
            $this->log('_branch: ' . $this->_branch);
            $this->log('_remote: ' . $this->_remote);
            $this->log('_root_dir: ' . $this->_root_dir);
            $this->log('_public_dir: ' . $this->_public_dir);
            $this->log('_repo_dir: ' . $this->_repo_dir);

            $this->log('_log: ' . $this->_log);

            if(isset($this->_branch, $this->_remote, $this->_root_dir, $this->_public_dir, $this->_repo_dir)){

                // change directory to root
                exec('cd ' . $this->_root_dir);

                // backup
                if($this->_enable_backups){

                    // create backups directory if it does not already exist
                    if(!file_exists($this->_public_dir . '/.deployment/backups')){
                        exec('mkdir ' . $this->_public_dir . '/.deployment/backups');
                        $this->log('Created deployment/backups directory...');
                    }

                    // create the backup directory .deployment/backups/Y-m-d_H-i
                    exec('mkdir ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i'));
                    $this->log('Created deployment/backups/' . date('Y-m-d_H-i') . ' directory...');

                    // copy the codeigniter directory
                    exec('cp -r ' . $this->_ci_dir . ' ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i'));
                    $this->log('Backed up codeigniter directory...');

                    // create the .deployment/backups/Y-m-d_H-i/public-html directory
                    exec('mkdir ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i') . '/public-html');
                    $this->log('Created deployment/backups/' . date('Y-m-d_H-i') . '/public-html directory...');
                    // move the contents of the public/html directory to backup
                    exec('mv -f ' . $this->_public_dir . '/{*,.*} ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i') . '/public-html/');
                    $this->log('Backed up public/html directory...');
                }

                // Discard any changes to tracked files since our last deploy
                exec('cd ' . $this->_repo_dir . ' && git reset --hard HEAD', $output);
                $this->log('Reseting repository... ' . "\n" . implode(' ', $output)) . "\n";

                // Update the local repository
                exec('cd ' . $this->_repo_dir . ' && git pull '.$this->_remote.' '.$this->_branch, $output);
                $this->log('Pulling in changes...' . "\n" . implode(' ', $output)) . "\n";

                // Secure the .git directory
                exec('cd ' . $this->_repo_dir . ' && chmod -R og-rx .git');
                $this->log('Securing .git directory... ');

                // Delete the files/folders in public dir
                exec('rm -rf ' . $this->_public_dir . '/*');
                $this->log('Deleted files/folders from public...');

                // Move public files to public dir
                exec('cp -r ' . $this->_repo_dir . '* ' . $this->_public_dir);
                exec('cp -r ' . $this->_repo_dir . '.htaccess ' . $this->_public_dir);
                $this->log('Copied deploy dir files to public dir...');

                // if (is_callable($this->post_deploy))
                // {
                //     // error_log('data: ' . $this->_data);
                //     call_user_func($this->post_deploy, $this->_data);
                // }

                $this->log('Deployment successful.' . "\n\n");

            }else{

                $this->log('Settings have not yet been set. Please set the object settings at the top of ' . $_SERVER['PHP_SELF'] . '.');

            }

        }
        catch (Exception $e)
        {
            $this->log($e, 'ERROR');
        }
    }

}

// go
$deploy = new Deploy();
$deploy->execute();

?>