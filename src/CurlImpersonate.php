<?php
namespace CurlImpersonate;
class CurlImpersonate {
    private $url;
    private $method = 'GET';
    private $headers = array();
    private $data;
    private $includeHeaders = false; 
    private $engineCurl = "curl"; 
    private $handle;

    public function setopt($option, $value) {
        switch ($option) {
            case CURLCMDOPT_URL:
                $this->url = $value;
                break;
            case CURLCMDOPT_METHOD:
                $this->method = strtoupper($value);
                break;
            case CURLCMDOPT_POSTFIELDS:
                $this->data = $value;
                break;
            case CURLCMDOPT_HTTP_HEADERS:
                $this->headers = array_merge($this->headers, $value);
                break;
            case CURLCMDOPT_HEADER:
                $this->includeHeaders = (bool)$value;
                break;
            case CURLCMDOPT_ENGINE:
                $this->engineCurl = $value;
                break;
            default:
                throw new InvalidArgumentException("Invalid option: {$option}");
        }
    }

    private function prepareData() {
        if (is_array($this->data) || is_object($this->data)) {
            $this->data = json_encode($this->data);
        }
    }

    public function exec() {
        $this->prepareData();

        $curlCommand = "{$this->engineCurl} -X {$this->method}";

        if ($this->data !== null) {
            $curlCommand .= " -d '{$this->data}'";
        }

        foreach ($this->headers as $header) {
            $curlCommand .= " -H '{$header}'";
        }

        if ($this->includeHeaders) {
            $curlCommand .= " -i";
        }

        $curlCommand .= " {$this->url}";

        return $curlCommand;
    }

    public function execStandard($output = null) {
        $command = $this->exec();
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($process);
        }

        return $output;
    }

    public function execStream() {
        $this->prepareData();

        $command = $this->exec();
        $this->handle = popen($command, 'r');
    }

    public function readStream($chunkSize = 4096) {
        if ($this->handle) {
            $output = fread($this->handle, $chunkSize);
            if ($output === false || feof($this->handle)) {
                $this->closeStream();
            }
            return $output;
        }
        return false;
    }

    public function closeStream() {
        if ($this->handle) {
            pclose($this->handle);
            $this->handle = null;
        }
    }
}


define('CURLCMDOPT_URL', 1);
define('CURLCMDOPT_METHOD', 2);
define('CURLCMDOPT_POSTFIELDS', 3);
define('CURLCMDOPT_HTTP_HEADERS', 4);
define('CURLCMDOPT_HEADER', 5);
define('CURLCMDOPT_ENGINE', 6);
?>
