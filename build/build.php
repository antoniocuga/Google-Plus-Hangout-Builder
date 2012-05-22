<?php

class HangoutBuilder {

  private $_CONFIG,
          $_HOST = 'http://my-hangout.local',
          $_PROJECT_NAME = 'my-hangout';

  public function __construct($config = array()) {

    $base_dir = dirname(dirname(__FILE__));

    $this->_CONFIG = array(

      'base_dir' => $base_dir,
      'build_dir' => sprintf('%s/%s', $base_dir, 'build'),
      'css_dir' => sprintf('%s/%s', $base_dir, 'css'),
      'js_dir' => sprintf('%s/%s', $base_dir, 'js'),

      'host' => $this->_HOST,

      'hangout_file' => sprintf('%s/%s.xml', $base_dir, $this->_PROJECT_NAME),
      'hangout_combined_url' => sprintf('%s/%s.combined.xml', $this->_HOST, $this->_PROJECT_NAME),

      'combined_format' => 'all.combined',

      'rsync_enable' => true,
      'rsync_user' => 'YOUR_USERNAME',
      'rsync_host' => 'YOUR_REMOTE_HOST',
      'rsync_path' => 'YOUR_REMOTE_PATH',
      'rsync_ignore_file' => sprintf('%s/%s', $base_dir, '.ignore'),
      'rsync_identity_file' => 'YOUR_IDENTITY_FILE',
      'rsync_use_identity_file' => true,

    );

    foreach ( $this->_CONFIG as $k => $v ) {
      if ( array_key_exists($k, $config) ) {
        $this->_CONFIG[$k] = $config[$k];
      }
    }

  }

  public function writeLine($line) {
    echo "$line\n";
  }

  public function processCssFile() {
    $this->writeLine('');
    $this->writeLine('Compressing CSS');
    $this->minify('css');
  }

  public function processJsFile() {
    $this->writeLine('');
    $this->writeLine('Compressing JS');
    $this->minify('js');
  }

  private function minify($type, $minify = true) {

    $req_url = sprintf('%s/%s/%s.%s',
      $this->_CONFIG['host'],
      $type,
      $this->_CONFIG['combined_format'],
      $type
    );
    $file = sprintf('%s/all.min.%s',
      $this->_CONFIG[$type.'_dir'],
      $type
    );

    //-- Get the combined content
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $req_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);

    //-- Write the combined content to the file
    $fh = fopen($file, 'w');
    fwrite($fh, $output);
    fclose($fh);

    //-- Minify the content
    if ( $minify ) {
      switch ( $type ) {
        case 'css':
          exec(sprintf(
            'java -jar %s/yuicompressor-2.4.7.jar %s',
            $this->_CONFIG['build_dir'],
            $file
          ), $output);
          break;
        case 'js':
          exec(sprintf(
            'java -jar %s/compiler.jar %s',
            $this->_CONFIG['build_dir'],
            $file
          ), $output);
          break;
      }
    }
    $output = is_array($output)? implode("\n", $output) : $output;

    //-- Write the minified output to the file
    $fh = fopen($file, 'w');
    fwrite($fh, $output);
    fclose($fh);

    $this->writeLine(sprintf('Successfully wrote %s...', $file));

  }

  public function processTemplate() {

    $this->writeLine('');
    $this->writeLine('Updating the project XML');

    //-- Get the combined content
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $this->_CONFIG['hangout_combined_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);

    //-- Save it to the project file
    $fh = fopen($this->_CONFIG['hangout_file'], 'w');
    fwrite($fh, $output);
    fclose($fh);

    $this->writeLine(sprintf('Successfully updated %s', $this->_CONFIG['hangout_file']));

  }

  public function publishToServer() {

    if ( $this->_CONFIG['rsync_enable'] ) {

      $this->writeLine('');
      $this->writeLine('Publishing XML to server');

      $cmd = sprintf('rsync -avm --exclude-from=%s %s %s/ %s@%s:%s',
        $this->_CONFIG['rsync_ignore_file'],
        $this->_CONFIG['rsync_use_identity_file']? sprintf('-e "ssh -i %s"', $this->_CONFIG['rsync_identity_file']) : '',
        $this->_CONFIG['base_dir'],
        $this->_CONFIG['rsync_user'],
        $this->_CONFIG['rsync_host'],
        $this->_CONFIG['rsync_path']
      );
      $result = exec($cmd);
      $result = is_array($result)? implode("\n", $result) : $result;

      $this->writeLine($result);
      $this->writeLine('Published updated contents to server');

    }

  }

}

$b = new HangoutBuilder(array(
  'rsync_enable' => false
));

//-- Minify the CSS files
$b->processCssFile();

//-- Compile the JS files
$b->processJsFile();

//-- Push the template output into the project file
$b->processTemplate();

//-- Remote sync to the server
$b->publishToServer();