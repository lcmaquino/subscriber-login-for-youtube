<?php
namespace Lcmaquino\SubscriberLoginForYouTube\Site;

class HttpClient
{
    /**
     * Do a GET http request.
     *
     * @param string $url
     * @param array $query
     * @return array|null
     */
    public function get($url = '', $query = [])
    {
        $url = $url . '?' . http_build_query($query);
        $response = wp_remote_get($url);

        return $this->responseToJson($response);
    }

    /**
     * Do a POST http request.
     *
     * @param string $url
     * @param array $query
     * @return array|null
     */
    public function post($url = '', $query = [])
    {
        $args = array(
            'body' => $query,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Content-Length' => strlen(http_build_query($query)),
            )
        );
        $response = wp_remote_post($url, $args);

        return $this->responseToJson($response);
    }

    /**
     * Get the response body and return as an array.
     *
     * @param string $protocol
     * @return array|null
     */
    private function responseToJson($response)
    {
        $rawData = wp_remote_retrieve_body($response);
        $jsonData = empty($rawData) ? null : json_decode($rawData, true);
        
        return $jsonData;
    }
}
