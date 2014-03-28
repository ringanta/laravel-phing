<?php

require_once "phing/Task.php";

class CSSLintTask extends Task {

    private $source;
    private $contents;
    private $reporter = "checkstyle-xml";
    private $output;

    const START_ESCAPE = '"';
    const END_ESCAPE = '"';
    const BUFFER_SIZE = 4096;

    public function setSource($str) {
        $this->source = $str;
    }

    public function setOutput($out){
        $this->output = $out;
    }

    public function setReporter($reporter){
        $this->reporter = $reporter;
    }

    public function init() {
        if (file_exists($this->source)){
            $handle = fopen($this->source, "r");
            if ($handle){
                $this->contents = $this->transform($handle);
                fclose($handle);
            }
        }
    }

    public function main() {
        $this->init();
        $cmd="csslint --format=$this->reporter $this->contents";
        
        $jshint = shell_exec($cmd);
        file_put_contents($this->output, $jshint);
    }
    
    private function transform($handle){
        $result = "";
        while(($buffer = fgets($handle, self::BUFFER_SIZE)) !== false){
            $result .=  ' ' . self::START_ESCAPE . trim($buffer) . self::END_ESCAPE;
        }
        return trim($result);
    }
}

