<?php

declare(strict_types=1);

namespace CurlImpersonate;

/**
 * Curl-Impersonate-PHP
 *
 * Execute HTTP requests using curl-impersonate binaries to mimic
 * real browser TLS fingerprints and HTTP/2 behavior.
 *
 * @see https://github.com/lwthiker/curl-impersonate
 * @see https://github.com/kelvinzer0/curl-impersonate-php
 */
class CurlImpersonate
{
    // Option constants
    public const OPT_URL         = 1;
    public const OPT_METHOD      = 2;
    public const OPT_POSTFIELDS  = 3;
    public const OPT_HTTP_HEADERS = 4;
    public const OPT_HEADER      = 5;
    public const OPT_ENGINE      = 6;
    public const OPT_COOKIEFILE  = 7;
    public const OPT_COOKIEJAR   = 8;
    public const OPT_PROXY       = 9;
    public const OPT_TIMEOUT     = 10;
    public const OPT_FOLLOW_LOCATION = 11;
    public const OPT_VERIFY_SSL  = 12;

    // Browser presets
    public const BROWSER_CHROME  = 'chrome116';
    public const BROWSER_CHROME_120 = 'chrome120';
    public const BROWSER_FIREFOX = 'firefox102';
    public const BROWSER_FIREFOX_117 = 'firefox117';
    public const BROWSER_SAFARI  = 'safari15_3';
    public const BROWSER_SAFARI_17 = 'safari17_0';
    public const BROWSER_EDGE    = 'edge99';

    private ?string $url = null;
    private string $method = 'GET';
    private array $headers = [];
    private ?string $cookieFile = null;
    private ?string $cookieJar = null;
    private $data = null;
    private bool $includeHeaders = false;
    private string $engineCurl = 'curl';
    private ?string $proxy = null;
    private int $timeout = 30;
    private bool $followLocation = true;
    private bool $verifySsl = true;
    private $handle = null;

    /**
     * Set a cURL option.
     *
     * @param int    $option One of the OPT_* constants
     * @param mixed  $value  The option value
     * @return self
     * @throws \InvalidArgumentException on unknown option
     */
    public function setopt(int $option, $value): self
    {
        switch ($option) {
            case self::OPT_URL:
                $this->url = (string) $value;
                break;
            case self::OPT_METHOD:
                $this->method = strtoupper((string) $value);
                break;
            case self::OPT_POSTFIELDS:
                $this->data = $value;
                break;
            case self::OPT_HTTP_HEADERS:
                $this->headers = array_merge($this->headers, (array) $value);
                break;
            case self::OPT_HEADER:
                $this->includeHeaders = (bool) $value;
                break;
            case self::OPT_ENGINE:
                $this->engineCurl = (string) $value;
                break;
            case self::OPT_COOKIEFILE:
                $this->cookieFile = (string) $value;
                break;
            case self::OPT_COOKIEJAR:
                $this->cookieJar = (string) $value;
                break;
            case self::OPT_PROXY:
                $this->proxy = (string) $value;
                break;
            case self::OPT_TIMEOUT:
                $this->timeout = (int) $value;
                break;
            case self::OPT_FOLLOW_LOCATION:
                $this->followLocation = (bool) $value;
                break;
            case self::OPT_VERIFY_SSL:
                $this->verifySsl = (bool) $value;
                break;
            default:
                throw new \InvalidArgumentException("Unknown option: {$option}");
        }

        return $this;
    }

    /**
     * Set the browser engine using a preset constant.
     *
     * @param string $preset One of BROWSER_* constants
     * @param string|null $basePath Directory containing curl-impersonate binaries
     * @return self
     */
    public function setBrowser(string $preset, ?string $basePath = null): self
    {
        if ($basePath === null) {
            $basePath = $this->findEnginePath();
        }

        $binary = $this->resolveBinaryName($preset);
        $fullPath = rtrim($basePath, '/') . '/' . $binary;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException(
                "curl-impersonate binary not found: {$fullPath}. " .
                "Install from https://github.com/lwthiker/curl-impersonate/releases"
            );
        }

        $this->engineCurl = $fullPath;
        return $this;
    }

    /**
     * Build the shell command string.
     *
     * @return string
     * @throws \RuntimeException if URL is not set
     */
    public function buildCommand(): string
    {
        if ($this->url === null) {
            throw new \RuntimeException('URL is required. Call setopt(OPT_URL, ...) first.');
        }

        $cmd = escapeshellarg($this->engineCurl);
        $cmd .= ' -X ' . escapeshellarg($this->method);

        if ($this->timeout > 0) {
            $cmd .= ' --max-time ' . (int) $this->timeout;
        }

        if ($this->followLocation) {
            $cmd .= ' -L';
        }

        if (!$this->verifySsl) {
            $cmd .= ' -k';
        }

        if ($this->proxy !== null) {
            $cmd .= ' -x ' . escapeshellarg($this->proxy);
        }

        if ($this->cookieFile !== null) {
            $cmd .= ' --cookie ' . escapeshellarg($this->cookieFile);
        }

        if ($this->cookieJar !== null) {
            $cmd .= ' --cookie-jar ' . escapeshellarg($this->cookieJar);
        }

        if ($this->data !== null) {
            $prepared = is_array($this->data) || is_object($this->data)
                ? json_encode($this->data)
                : (string) $this->data;
            $cmd .= ' -d ' . escapeshellarg($prepared);
        }

        foreach ($this->headers as $header) {
            $cmd .= ' -H ' . escapeshellarg($header);
        }

        if ($this->includeHeaders) {
            $cmd .= ' -i';
        }

        $cmd .= ' ' . escapeshellarg($this->url);

        return $cmd;
    }

    /**
     * Execute request and return the full response.
     *
     * @return string|null Response body (or null on failure)
     */
    public function exec(): ?string
    {
        $command = $this->buildCommand();

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to execute: {$command}");
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                "curl-impersonate exited with code {$exitCode}: " . trim($stderr ?? '')
            );
        }

        return $output ?: null;
    }

    /**
     * Start streaming the response.
     *
     * @return self
     */
    public function execStream(): self
    {
        $command = $this->buildCommand();
        $this->handle = popen($command, 'r');

        if (!is_resource($this->handle)) {
            throw new \RuntimeException("Failed to start stream: {$command}");
        }

        return $this;
    }

    /**
     * Read a chunk from the stream.
     *
     * @param int $chunkSize Bytes to read
     * @return string|false Chunk data or false when done
     */
    public function readStream(int $chunkSize = 4096)
    {
        if (!is_resource($this->handle)) {
            return false;
        }

        $data = fread($this->handle, $chunkSize);

        if ($data === false || feof($this->handle)) {
            $this->closeStream();
            return $data ?: false;
        }

        return $data;
    }

    /**
     * Close the active stream.
     */
    public function closeStream(): void
    {
        if (is_resource($this->handle)) {
            pclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * Reset all options for reuse.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->url = null;
        $this->method = 'GET';
        $this->headers = [];
        $this->cookieFile = null;
        $this->cookieJar = null;
        $this->data = null;
        $this->includeHeaders = false;
        $this->engineCurl = 'curl';
        $this->proxy = null;
        $this->timeout = 30;
        $this->followLocation = true;
        $this->verifySsl = true;

        return $this;
    }

    /**
     * Try to auto-detect curl-impersonate installation path.
     */
    private function findEnginePath(): string
    {
        $candidates = [
            '/usr/local/bin',
            '/usr/bin',
            '/opt/curl-impersonate/bin',
            dirname(__DIR__) . '/bin',
            getenv('HOME') . '/.local/bin',
        ];

        foreach ($candidates as $dir) {
            if (is_dir($dir) && glob($dir . '/curl_chrome*')) {
                return $dir;
            }
        }

        // Fallback: try which
        $which = trim((string) shell_exec('which curl-impersonate 2>/dev/null'));
        if ($which && file_exists($which)) {
            return dirname($which);
        }

        throw new \RuntimeException(
            "Cannot auto-detect curl-impersonate path. " .
            "Install from https://github.com/lwthiker/curl-impersonate/releases " .
            "or pass the path explicitly: ->setBrowser(BROWSER_CHROME, '/path/to/bin')"
        );
    }

    /**
     * Resolve binary name from browser preset.
     */
    private function resolveBinaryName(string $preset): string
    {
        $map = [
            'chrome116'     => 'curl_chrome116',
            'chrome120'     => 'curl_chrome120',
            'firefox102'    => 'curl_firefox102',
            'firefox117'    => 'curl_firefox117',
            'safari15_3'    => 'curl_safari15_3',
            'safari17_0'    => 'curl_safari17_0',
            'edge99'        => 'curl_edge99',
        ];

        if (!isset($map[$preset])) {
            throw new \InvalidArgumentException(
                "Unknown browser preset: {$preset}. Available: " . implode(', ', array_keys($map))
            );
        }

        return $map[$preset];
    }
}
