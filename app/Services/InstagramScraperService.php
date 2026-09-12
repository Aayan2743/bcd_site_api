<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

class InstagramScraperService
{
  public function scrape(string $url): array
    {
        $username = $this->extractUsername($url);

        if (!$username) {
            throw new \Exception('Invalid Instagram profile URL.');
        }

        $profileUrl = 'https://www.instagram.com/' . $username . '/';

        $client = Client::createChromeClient();

        $client->request('GET', $profileUrl);

        $client->waitFor('body', 10);

        sleep(3);

        $this->closePopup($client);

        sleep(2);

        $crawler = $client->getCrawler();

        $data = [
            'username' => $username,
            'profile_url' => $profileUrl,
            'name' => $this->getName($crawler),
            'followers' => $this->getFollowers($crawler),
            'following' => $this->getFollowing($crawler),
            'profile_image' => $this->getMetaContent($crawler, 'og:image'),
            'description' => $this->getMetaContent($crawler, 'og:description'),
        ];

        $client->quit();

        return $data;
    }

    private function extractUsername(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST));

        if (!in_array($host, [
            'instagram.com',
            'www.instagram.com',
        ])) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            return null;
        }

        $username = explode('/', trim($path, '/'))[0] ?? null;

        if (!$username) {
            return null;
        }

        $blocked = [
            'accounts',
            'explore',
            'direct',
            'reels',
            'reel',
            'stories',
            'p',
            'about',
            'developer',
        ];

        if (in_array(strtolower($username), $blocked)) {
            return null;
        }

        return $username;
    }

    private function closePopup(Client $client): void
    {
        try {
            $client->executeScript("
                const buttons = document.querySelectorAll('button');

                for (const button of buttons) {
                    const aria = button.getAttribute('aria-label');

                    if (aria && aria.toLowerCase() === 'close') {
                        button.click();
                        break;
                    }
                }
            ");

            sleep(1);
        } catch (\Throwable $e) {
        }
    }

    private function getName(Crawler $crawler): ?string
    {
        try {
            $name = null;

            $crawler->filter('h1')->each(
                function (Crawler $node) use (&$name) {
                    if ($name) {
                        return;
                    }

                    $text = trim($node->text());

                    if ($text !== '') {
                        $name = $text;
                    }
                }
            );

            return $name;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getFollowers(Crawler $crawler): int
    {
        try {
            $followers = null;

            $crawler->filter('span[title]')->each(
                function (Crawler $node) use (&$followers) {
                    if ($followers !== null) {
                        return;
                    }

                    $title = trim((string) $node->attr('title'));
                    $text = trim($node->text());

                    if ($title !== '' && preg_match('/^[\d,.]+(?:\s*[KMB])?$/i', $title)) {
                        $followers = $this->convertNumber($title);
                        return;
                    }

                    if ($text !== '' && preg_match('/^[\d,.]+(?:\s*[KMB])?$/i', $text)) {
                        $followers = $this->convertNumber($text);
                    }
                }
            );

            return $followers ?? 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getFollowing(Crawler $crawler): int
    {
        try {
            $bodyText = $crawler->filter('body')->text();

            if (preg_match(
                '/([\d,.]+(?:\s*[KMB])?)\s+following/i',
                $bodyText,
                $matches
            )) {
                return $this->convertNumber($matches[1]);
            }

            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function getMetaContent(
        Crawler $crawler,
        string $property
    ): ?string {
        try {
            $node = $crawler->filter(
                'meta[property="' . $property . '"]'
            );

            if ($node->count()) {
                return $node->attr('content');
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function convertNumber(string $value): int
    {
        $value = strtoupper(
            str_replace(',', '', trim($value))
        );

        if (str_ends_with($value, 'K')) {
            return (int) round(
                (float) rtrim($value, 'K') * 1000
            );
        }

        if (str_ends_with($value, 'M')) {
            return (int) round(
                (float) rtrim($value, 'M') * 1000000
            );
        }

        if (str_ends_with($value, 'B')) {
            return (int) round(
                (float) rtrim($value, 'B') * 1000000000
            );
        }

        return (int) $value;
    }
}