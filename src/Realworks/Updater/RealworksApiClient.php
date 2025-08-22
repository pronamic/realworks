<?php namespace Realworks\Updater;

use RuntimeException;

class RealworksApiClient
{
    const API_URL = 'https://api.realworks.nl/wonen/v1/objecten';

    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Fetches all objects from the Realworks API.
     * Note: The current implementation only fetches the first page of results (100 items).
     * Proper pagination handling should be added in the future.
     *
     * @return array The API response data.
     * @throws RuntimeException If the API request fails.
     */
    public function fetchObjects()
    {
        $url = static::API_URL . '?aantal=100';
        $token = 'rwauth ' . $this->apiKey;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Authorization: ' . $token, 'Content-Type: application/json'],
            CURLOPT_FOLLOWLOCATION => true
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new RuntimeException("cURL Fout: " . $error);
        }

        if ($http_code >= 400) {
            $responseData = json_decode($response, true);
            $errorMessage = isset($responseData['meldingen'][0]) ? $responseData['meldingen'][0] : $response;
            throw new RuntimeException("API Fout (Status " . $http_code . "): " . $errorMessage);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to decode JSON response from API.");
        }

        return $data;
    }

    /**
     * Creates a resized image URL using the Realworks image service.
     *
     * @param string $originalUrl The original URL of the image.
     * @param int $width The desired width.
     * @param int $height The desired height.
     * @return string The new URL with resizing parameters.
     */
    public function createImageUrl($originalUrl, $width, $height)
    {
        if (!$originalUrl) {
            return "https://via.beheer.eu/img/{$width}x{$height}/fff/000.png?text=Geen+foto";
        }

        $path_info = pathinfo(parse_url($originalUrl, PHP_URL_PATH));
        $extension = strtolower($path_info['extension'] ?? '');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return $originalUrl;
        }

        $url_parts = parse_url($originalUrl);
        parse_str($url_parts['query'] ?? '', $query_params);

        $query_params['width'] = $width;
        $query_params['height'] = $height;
        $query_params['resize'] = 1;

        $scheme = $url_parts['scheme'] ?? 'https';
        $host = $url_parts['host'] ?? '';
        $path = $url_parts['path'] ?? '';

        return $scheme . '://' . $host . $path . '?' . http_build_query($query_params);
    }
}
