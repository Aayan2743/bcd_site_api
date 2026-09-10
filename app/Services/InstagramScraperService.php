<?php
namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;

class InstagramScraperService
{
    public function scrape(string $url): array
    {
        $username = $this->extractUsername($url);

        if (! $username) {
            throw new \Exception('Invalid Instagram profile URL.');
        }

        $profileUrl = 'https://www.instagram.com/' . $username . '/';

        $client = Client::createChromeClient();

        $client->request('GET', $profileUrl);

        // Wait for Instagram page to render
        $client->waitFor('body', 10);

        // Give JS a little time to populate the page
        usleep(2000000);

        $crawler = $client->getCrawler();

        /*
        |--------------------------------------------------------------------------
        | Username
        |--------------------------------------------------------------------------
        */

        $profileUsername = $username;

        /*
        |--------------------------------------------------------------------------
        | Profile Image
        |--------------------------------------------------------------------------
        */

        $profileImage = null;

        $ogImage = $crawler->filter(
            'meta[property="og:image"]'
        );

        if ($ogImage->count()) {
            $profileImage = $ogImage->attr('content');
        }

        /*
        |--------------------------------------------------------------------------
        | Meta Description
        |--------------------------------------------------------------------------
        */

        $description = null;

        $metaDescription = $crawler->filter(
            'meta[property="og:description"]'
        );

        if ($metaDescription->count()) {
            $description = $metaDescription->attr('content');
        }

        /*
        |--------------------------------------------------------------------------
        | Page Text
        |--------------------------------------------------------------------------
        */

        $bodyText = $crawler->filter('body')->text();

        /*
        |--------------------------------------------------------------------------
        | Followers
        |--------------------------------------------------------------------------
        */

        $followers = null;

        if (
            preg_match(
                '/([\d,.]+(?:\s*[KMB])?)\s+followers/i',
                $bodyText,
                $matches
            )
        ) {
            $followers = $this->convertNumber(
                $matches[1]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Following
        |--------------------------------------------------------------------------
        */

        $following = null;

        if (
            preg_match(
                '/([\d,.]+(?:\s*[KMB])?)\s+following/i',
                $bodyText,
                $matches
            )
        ) {
            $following = $this->convertNumber(
                $matches[1]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        */

        $name = null;

        /*
         * Try profile heading / accessible elements first.
         */
        $crawler->filter('h1')->each(
            function (Crawler $node) use (&$name) {

                if (! $name) {
                    $text = trim($node->text());

                    if ($text !== '') {
                        $name = $text;
                    }
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return [
            'username'      => $profileUsername,
            'profile_url'   => $profileUrl,
            'name'          => $name,
            'followers'     => $followers,
            'following'     => $following,
            'profile_image' => $profileImage,
            'description'   => $description,
        ];
    }

    private function extractUsername(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower(
            parse_url($url, PHP_URL_HOST)
        );

        if (
            $host !== 'instagram.com' &&
            $host !== 'www.instagram.com'
        ) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! $path) {
            return null;
        }

        $username = trim($path, '/');

        /*
         * Only first URL segment
         */
        $username = explode('/', $username)[0];

        if (! $username) {
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
