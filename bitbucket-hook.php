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
    private $_public_dir = '/var/www/vhosts/ristaging.ca/ri-reports.ristaging.ca';

    /**
     * The git repo directory.
     *
     * @var string
     */
    private $_repo_dir = '/var/www/vhosts/ristaging.ca/ri-reports.git';

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

        $this->log("\n\n================================================================================================\n");
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

            // $this->log('_repo_dir: ' . $this->_repo_dir);
            // $this->log('_remote: ' . $this->_remote);
            // $this->log('_branch: ' . $this->_branch);

            // create backups directory if it does not already exist
            if(!file_exists($this->_public_dir . '/.deployment/backups')){
                exec('mkdir ' . $this->_public_dir . '/.deployment/backups');
                $this->log('Created deployment/backups directory...');
            }

            // // backup the codeigniter directory
            // exec('cp -r ' . $this->_root_dir . '/ri-reports-codeigniter ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i'));

            // $this->log('origin: ' . $this->_root_dir . '/ri-reports-codeigniter');
            // $this->log('destination: ' . $this->_public_dir . '/.deployment/backups/' . date('Y-m-d_H-i'));

            // Discard any changes to tracked files since our last deploy
            exec('cd ' . $this->_repo_dir . ' && git reset --hard HEAD', $output);
            $this->log('Reseting repository... ' . "\n" . implode(' ', $output)) . "\n";

            // Update the local repository
            exec('cd ' . $this->_repo_dir . ' && git pull '.$this->_remote.' '.$this->_branch, $output);
            $this->log('Pulling in changes...' . "\n" . implode(' ', $output)) . "\n";

            // Secure the .git directory
            exec('cd ' . $this->_repo_dir . ' && chmod -R og-rx .git');
            $this->log('Securing .git directory... ');

            if(isset($this->_root_dir)){
                // Delete the previous codeigniter folder
                exec('rm -rf ' . $this->_root_dir . '/ri-reports-codeigniter');
                $this->log('Deleted previous codeigniter folder');
            }else{
                $this->log('_root_dir variable not set...', 'ERROR');
            }

            // Move codeigniter files one up from public dir
            exec('cp -r ' . $this->_repo_dir . '/ri-reports-codeigniter ' . $this->_root_dir . '/ri-reports-codeigniter');
            $this->log('Copied codeigniter dir one up from public...');

            // error_log('root: ' . $this->_public_dir);
            if(isset($this->_public_dir)){
                // // Delete the files/folders in public dir
                exec('rm -rf ' . $this->_public_dir . '/*');
                $this->log('Deleted files/folders from public');
            }else{
                $this->log('_public_dir variable not set...', 'ERROR');
            }

            // Move public files to public dir
            exec('cp -r ' . $this->_repo_dir . '/public-html/* ' . $this->_public_dir);
            exec('cp -r ' . $this->_repo_dir . '/public-html/.htaccess ' . $this->_public_dir);
            $this->log('Copied public-html files to public dir...');

            // if (is_callable($this->post_deploy))
            // {
            //     // error_log('data: ' . $this->_data);
            //     call_user_func($this->post_deploy, $this->_data);
            // }

            $this->log('Deployment successful.');
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