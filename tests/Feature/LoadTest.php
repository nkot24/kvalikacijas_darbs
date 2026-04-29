<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

class LoadTest extends TestCase
{
    private string $baseUrl = 'http://127.0.0.1:8000';
    private \PDO $pdo;
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        $this->pdo = new \PDO(
            'mysql:host=127.0.0.1;port=3307;dbname=order_management1;charset=utf8',
            'root',
            '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    protected function tearDown(): void
    {
        if ($this->createdUserIds) {
            $ids = implode(',', $this->createdUserIds);
            $this->pdo->exec("DELETE FROM users WHERE id IN ($ids)");
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_serve_100_concurrent_users(): void
    {
        if (!$this->isServerRunning()) {
            $this->markTestSkipped('Nepieciešams darbojošs serveris: php artisan serve');
        }

        // Izveido 100 lietotājus tieši MySQL datubāzē
        $users = [];
        $password = password_hash('password', PASSWORD_BCRYPT, ['cost' => 4]);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (name, role, password, remember_token, created_at, updated_at)
             VALUES (?, 'worker', ?, ?, NOW(), NOW())"
        );

        for ($i = 1; $i <= 100; $i++) {
            $name = 'loadtest_' . $i . '_' . uniqid();
            $stmt->execute([$name, $password, bin2hex(random_bytes(10))]);
            $id = (int) $this->pdo->lastInsertId();
            $users[] = ['id' => $id, 'name' => $name];
            $this->createdUserIds[] = $id;
        }

        // Secīgi pierakstās kā katrs lietotājs, lai iegūtu sesijas sīkfailu
        $cookieJars = [];
        foreach ($users as $user) {
            $jar = new CookieJar();
            $client = new Client([
                'base_uri'        => $this->baseUrl,
                'cookies'         => $jar,
                'allow_redirects' => true,
            ]);

            $loginPage = $client->get('/login');
            preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', (string) $loginPage->getBody(), $m);

            $client->post('/login', [
                'form_params' => [
                    '_token'   => $m[1] ?? '',
                    'name'     => $user['name'],
                    'password' => 'password',
                ],
            ]);

            $cookieJars[] = $jar;
        }

        // 100 vienlaicīgi pieprasījumi
        $success = 0;
        $failed  = 0;
        $start   = microtime(true);
        $baseUrl = $this->baseUrl;

        $requests = function () use ($cookieJars, $baseUrl) {
            foreach ($cookieJars as $jar) {
                $cookies = [];
                foreach ($jar as $cookie) {
                    $cookies[] = $cookie->getName() . '=' . $cookie->getValue();
                }
                yield new Request('GET', $baseUrl . '/clients', [
                    'Cookie' => implode('; ', $cookies),
                    'Accept' => 'text/html',
                ]);
            }
        };

        $pool = new Pool(new Client(), $requests(), [
            'concurrency' => 100,
            'fulfilled'   => function ($response) use (&$success) {
                if ($response->getStatusCode() === 200) {
                    $success++;
                }
            },
            'rejected'    => function () use (&$failed) {
                $failed++;
            },
        ]);

        $pool->promise()->wait();
        $elapsed = microtime(true) - $start;

        $avgMs = round(($elapsed / 100) * 1000);

        $this->assertEquals(100, $success, "Tikai {$success}/100 pieprasījumi veiksmīgi");
        $this->assertLessThan(30, $elapsed, "100 pieprasījumi aizņēma {$elapsed}s, limits ir 30s");
        $this->assertLessThan(1000, $avgMs, "Vidējais atbildes laiks {$avgMs}ms, limits ir 1000ms");
    }

    private function isServerRunning(): bool
    {
        try {
            (new Client(['timeout' => 2]))->get($this->baseUrl);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
