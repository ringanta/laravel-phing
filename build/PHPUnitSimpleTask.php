<?php

require_once "phing/Task.php";

class PHPUnitSimpleTask extends Task {

    private $bootstrap = "";
    private $junitlog = "";
    private $htmlllog = "";
    private $cloverlog = "";
    private $template;
    private $phpunit;
    private $phplist;

    const XPATH_FILE = "file";
    const XPATH_FILTER = "filter";
    const XPATH_WHITELIST = "whitelist";
    const XPATH_ROOT_FILTER = "/phpunit/filter";

    public function setBootstrap($str) {
        $this->bootstrap = $str;
    }

    public function setJunitlog($out){
        $this->junitlog = $out;
    }

    public function setCloverlog($out){
        $this->cloverlog = $out;
    }

    public function setHtmllog($out){
        $this->htmllog = $out;
    }

    public function setTemplate($source){
        $this->template = $source;
    }

    public function setPhpunit($out){
        $this->phpunit = $out;
    }

    public function setPhplist($php){
        $this->phplist = $php;
    }

    public function init() {
        if (file_exists($this->template)){
            $phpunit = simplexml_load_file($this->template);
            $filter = $phpunit->xpath(self::XPATH_ROOT_FILTER);
        
            if ($filter){
                if ($filter[0]->whitelist){
                    $whitelist = $filter[0]->whitelist;
                } else {
                    $whitelist = $filter->addChild(self::XPATH_WHITELIST);
                }
            } else {
                $filter = $phpunit->addChild(self::XPATH_FILTER);
                $whitelist = $filter->addChild(self::XPATH_WHITELIST);
            }

            $files = file($this->phplist);
            foreach ($files as $v){
                $whitelist->addChild(self::XPATH_FILE, trim($v));
            }

            file_put_contents($this->phpunit, $phpunit->asXML());
        }
    }

    public function main() {
        if (!empty($this->phplist)){
            $this->init();
            $cmd="phpunit -c $this->template";
            
            $junitFlag = trim($this->junitlog);
            if (! empty($junitFlag)){
                $cmd .= " --log-junit $junitFlag";
            }
    
            $cloverFlag = trim($this->cloverlog);
            if (! empty($cloverFlag)){
                $cmd .= " --coverage-clover $cloverFlag";
            }

            $htmlFlag = trim($this->htmllog);
            if (! empty($htmlFlag)){
                $cmd .= " --coverage-html $htmlFlag";
            }
    
            $backup = $this->phpunit . ".orig";
            $copy = copy($this->template, $backup);
            $copy = copy($this->phpunit, $this->template);
            $phpunit = shell_exec($cmd);
            copy($backup, $this->template);
            unlink($backup);
        } else {
            echo "Could not find file: $this->phplist";
        }
    }
}

