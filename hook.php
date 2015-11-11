<?php

//date_default_timezone_set('Europe/London');

class Deploy {

    /**
     * A callback function to call after the deploy has finished.
     *
     * @var callback
     */
    public $post_deploy;

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
    private $_date_format = 'Y-m-d H:i:sP';

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
     * The directory where your website and git repository are located, can be
     * a relative or absolute path
     *
     * @var string
     */
    // private $_directory;


    // private $_deloyment_script_dir = '/var/www/vhosts/ristaging.ca/ri-reports.ristaging.ca/.deployment';
    private $_root_dir = '/var/www/vhosts/ristaging.ca';
    private $_repo_dir = '/var/www/vhosts/ristaging.ca/ri-reports.git';
    private $_public_dir = '/var/www/vhosts/ristaging.ca/ri-reports.ristaging.ca';

    /**
     * Sets up defaults.
     *
     * @param  string  $directory  Directory where your website is located
     * @param  array   $data       Information about the deployment
     */
    public function __construct($directory, $options = array())
    {
        // Determine the directory path
        $this->_directory = realpath($directory).DIRECTORY_SEPARATOR;

        $available_options = array('log', 'date_format', 'branch', 'remote');

        foreach ($options as $option => $value)
        {
            if (in_array($option, $available_options))
            {
                $this->{'_'.$option} = $value;
            }
        }

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
            // file_put_contents($filename, date($this->_date_format).' --- '.$type.': '.$message.PHP_EOL, FILE_APPEND);
            file_put_contents($filename, date('Y-m-d H:i:s').' --- '.$type.': '.$message.PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Executes the necessary commands to deploy the website.
     */
    public function execute()
    {

        try
        {

            // // Change into the repo dir
            // exec('cd ' . $this->_repo_dir);
            // $this->log('Changing into repo directory...');

            // Discard any changes to tracked files since our last deploy
            exec('cd ' . $this->_repo_dir . ' && git reset --hard HEAD', $output);
            $this->log('Reseting repository... '.implode(' ', $output));

            // Update the local repository
            exec('cd ' . $this->_repo_dir . ' && git pull '.$this->_remote.' '.$this->_branch, $output);
            $this->log('Pulling in changes... '.implode(' ', $output));

            // Secure the .git directory
            exec('cd ' . $this->_repo_dir . ' && chmod -R og-rx .git');
            $this->log('Securing .git directory... ');

            // Delete the previous codeigniter folder
            exec('rm -rf ' . $this->_root_dir . '/ri-reports-codeigniter');
            $this->log('Deleted previous codeigniter folder');

            // error_log('repo_dir: ' . $this->_repo_dir);

            // Move codeigniter files one up from public dir
            exec('cp -r ' . $this->_repo_dir . '/ri-reports-codeigniter ' . $this->_root_dir . '/ri-reports-codeigniter');
            $this->log('Copied codeigniter dir one up from public...');

            // // Delete the files/folders in public dir
            exec('rm -rf ' . $this->_public_dir . '/*');
            $this->log('Deleted files/folders from public');

            // Move public files to public dir
            exec('cp -r ' . $this->_repo_dir . '/public-html/* ' . $this->_public_dir);
            exec('cp -r ' . $this->_repo_dir . '/public-html/.htaccess ' . $this->_public_dir);
            $this->log('Copied public-html files to public dir...');

            // if (is_callable($this->post_deploy))
            // {
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

// This is just an example
$deploy = new Deploy('/var/www/vhosts/ristaging.ca/ri-reports.ristaging.ca', array('deployments.log', 'Y-m-d H:i:sP', 'staging', 'origin'));

$deploy->execute();

?>