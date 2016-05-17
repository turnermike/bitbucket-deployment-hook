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
    private $_branch = 'production';

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
    private $_enable_backups = true;

    /**
     * The directory above the public directory.
     * Used to store codeigniter folder and git repo.
     *
     * @var string
     */
    private $_root_dir = '/var/www/vhosts/ridevelopment.ca/';

    /**
     * The public/html directory.
     *
     * @var string
     */
    private $_public_dir = '/var/www/vhosts/ridevelopment.ca/httpdocs/';

    /**
     * The git repo directory.
     *
     * @var string
     */
    private $_repo_dir = '/var/www/vhosts/ridevelopment.ca/ridevelopment-PRODUCTION.git/';

    /**
     * The CodeIgniter directory. This should be one level up from the public/html directory.
     *
     * @var string
     */
    private $_ci_dir = '/var/www/vhosts/ridevelopment.ca/ridevelopment-codeigniter/';

    /**
     * The new backup directory name. This is where a previous version will be saved before latest is copied.
     *
     * @var string
     */
    private $_backup_dir = '/var/www/vhosts/ridevelopment.ca/httpdocs/.deployment/backups/';

    /**
     * Date and Time. Used for backup directory name.
     *
     * @var datetime
     */
    private $_now = '';

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

        $this->_now = date('Y-m-d_H-i-s');

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
            $this->log('_ci_dir: ' . $this->_ci_dir);
            $this->log('_backup_dir: ' . $this->_backup_dir);
            $this->log('_log: ' . $this->_log);

            if(isset($this->_branch, $this->_remote, $this->_root_dir, $this->_public_dir, $this->_repo_dir, $this->_ci_dir, $this->_backup_dir, $this->_log)){

                // change directory to root
                exec('cd ' . $this->_root_dir);

                // backup
                if($this->_enable_backups){

                    // create backups directory if it does not already exist
                    if(!file_exists($this->_public_dir . '.deployment/backups')){
                        exec('mkdir ' . $this->_public_dir . '.deployment/backups');
                        $this->log('Created deployment/backups directory...');
                    }

                    // delete backups older than 7 days
                    exec('find ' . $this->_public_dir . '.deployment/backups/* -mtime +7 -exec rm {} \;');
                    $this->log('Removing backups older than 7 days...');

                    // create the backup directory .deployment/backups/Y-m-d_H-i
                    exec('mkdir ' . $this->_backup_dir . $this->_now);
                    $this->log('Created deployment/backups/' . date('Y-m-d_H-i') . ' directory...');

                    // copy the codeigniter directory
                    exec('cp -r ' . $this->_ci_dir . ' ' . $this->_backup_dir . $this->_now);
                    $this->log('Backed up codeigniter directory...');

                    // create the .deployment/backups/Y-m-d_H-i/public-html directory
                    exec('mkdir ' . $this->_backup_dir . $this->_now . '/public-html');
                    $this->log('Created backup public_html directory...');
                    // move the contents of the public/html directory to backup
                    exec('mv -f ' . $this->_public_dir . '{*,.*} ' . $this->_backup_dir . $this->_now  . '/public-html/');
                    $this->log('Backed up public_html directory...');
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

                // Delete the previous codeigniter folder
                exec('rm -rf ' . $this->_ci_dir);
                $this->log('Deleted previous codeigniter folder');

                // Move codeigniter files one up from public dir
                exec('cp -r ' . $this->_repo_dir . '/codeigniter ' . $this->_ci_dir);
                $this->log('Copied codeigniter dir one up from public...');

                // Move all public files to public dir
                exec('cp -a ' . $this->_repo_dir . 'public-html/. ' . $this->_public_dir);
                $this->log('Copied public-html files to public dir...');

                // Create a placeholder index.html file (comming soon)
                exec('touch ' . $this->_public_dir . 'index.html');
                $fp = fopen($this->_public_dir . 'index.html', 'w');
                fwrite($fp, 'comming soon');
                fclose($fp);
                $this->log('Created temporary index.html file.');

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