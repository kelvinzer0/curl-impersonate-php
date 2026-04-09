<?php

declare(strict_types=1);

namespace CurlImpersonate\Tests;

use CurlImpersonate\CurlImpersonate;
use PHPUnit\Framework\TestCase;

class CurlImpersonateTest extends TestCase
{
    public function testSetoptUrl(): void
    {
        $curl = new CurlImpersonate();
        $result = $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $this->assertInstanceOf(CurlImpersonate::class, $result);
    }

    public function testSetoptMethod(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $curl->setopt(CurlImpersonate::OPT_METHOD, 'post');
        $cmd = $curl->buildCommand();
        $this->assertStringContainsString("'POST'", $cmd);
    }

    public function testBuildCommandWithoutUrlThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $curl = new CurlImpersonate();
        $curl->buildCommand();
    }

    public function testBuildCommandContainsUrl(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $cmd = $curl->buildCommand();
        $this->assertStringContainsString('https://example.com', $cmd);
    }

    public function testBuildCommandWithHeaders(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $curl->setopt(CurlImpersonate::OPT_HTTP_HEADERS, ['Accept: application/json']);
        $cmd = $curl->buildCommand();
        $this->assertStringContainsString('Accept: application/json', $cmd);
    }

    public function testBuildCommandWithProxy(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $curl->setopt(CurlImpersonate::OPT_PROXY, 'socks5://127.0.0.1:1080');
        $cmd = $curl->buildCommand();
        $this->assertStringContainsString('-x', $cmd);
        $this->assertStringContainsString('socks5://127.0.0.1:1080', $cmd);
    }

    public function testBuildCommandWithTimeout(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $curl->setopt(CurlImpersonate::OPT_TIMEOUT, 60);
        $cmd = $curl->buildCommand();
        $this->assertStringContainsString('--max-time 60', $cmd);
    }

    public function testReset(): void
    {
        $curl = new CurlImpersonate();
        $curl->setopt(CurlImpersonate::OPT_URL, 'https://example.com');
        $curl->setopt(CurlImpersonate::OPT_METHOD, 'POST');
        $result = $curl->reset();
        $this->assertInstanceOf(CurlImpersonate::class, $result);
    }

    public function testSetoptChaining(): void
    {
        $curl = new CurlImpersonate();
        $result = $curl
            ->setopt(CurlImpersonate::OPT_URL, 'https://example.com')
            ->setopt(CurlImpersonate::OPT_METHOD, 'GET')
            ->setopt(CurlImpersonate::OPT_TIMEOUT, 10);
        $this->assertInstanceOf(CurlImpersonate::class, $result);
    }

    public function testInvalidOptionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $curl = new CurlImpersonate();
        $curl->setopt(999, 'value');
    }
}
