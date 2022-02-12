<?php

declare(strict_types=1);

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class HttpCheckTest extends TestCase {
    private Client $client;
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $dotenv = \Dotenv\Dotenv::createMutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->client = new Client();
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @dataProvider urlProvider
     */
    public function test_HTTPステータスが200(string $url)
    {
        if($_ENV['TEST_ENV'] === 'production') {
            sleep(1);
        }
        try {
            $response = $this->client->request('GET', $url);
            $this->assertEquals(200, $response->getStatusCode());
        } catch (ClientException $e) {
            $this->fail("レスポンスエラー。url: '$url', statusCode: " . $e->getCode());
        } catch (GuzzleException) {
            $this->fail("通信エラー。url: '$url'");
        }
    }

    public function urlProvider(): array
    {
        $env = $_ENV['TEST_ENV'];
        if ($env === 'production' || $env === 'local') {
            if ($env === 'production') {
                $url = 'https://example.com';
            } else {
                $url = $_ENV['TEST_LOCAL_URL'];
            }
            $stagePaths = [];
        } else if ($env === 'staging') {
            $url = 'https://example.com';
            $stagePaths = [];
        } else {
            return []; // skip test.
        }
        $basePaths = [
            '/',
            '/hoge',
            '/fuga',
        ];
        $paths = [...$stagePaths, ...$basePaths];
        return array_map(fn($path) => [$url . $path], $paths);
    }
}
