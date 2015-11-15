<?php

$options = new stdClass();
$options->git_branch = 'staging';
$options->git_remote = 'origin';
$options->log_file = 'deployments.log';
$options->date_format = 'Y-m-d H:i:s';
$options->root_dir = '/var/www/vhosts/ristaging.ca';
$options->repo_dir = '/var/www/vhosts/ristaging.ca/ri-reports.git';
$options->public_dir = '/var/www/vhosts/ristaging.ca/ri-reports.ristaging.ca';

//date_default_timezone_set('America/Toronto');

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
    private $_date_format = 'Y-m-d H:i:s';

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
    private $_root_dir = '';

    /**
     * The git repo directory.
     *
     * @var string
     */
    private $_repo_dir = '';

    /**
     * The public/html directory.
     *
     * @var string
     */
    private $_public_dir = '';

    /**
     * Sets up defaults.
     *
     * @param  array   $data       Information about the deployment
     */
    public function __construct($options = array())
    {
        // Determine the directory path
        $this->_directory = realpath($this->_public_dir).DIRECTORY_SEPARATOR;

        $available_options = array('log', 'date_format', 'branch', 'remote', 'root_dir', 'repo_dir', 'public_dir');

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

            if (is_callable($this->post_deploy))
            {
                // error_log('data: ' . $this->_data);
                call_user_func($this->post_deploy, $this->_data);
            }

            $this->log('Deployment successful.');
        }
        catch (Exception $e)
        {
            $this->log($e, 'ERROR');
        }
    }

}

// go
$deploy = new Deploy(
                array(
                    $options->log_file,
                    $options->date_format,
                    $options->git_remote,
                    $options->git_remote,
                    $options->root_dir,
                    $options->repo_dir,
                    $options->public_dir
                )
            );
$deploy->execute();

?>