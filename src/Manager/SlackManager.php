<?php

namespace App\Manager;

use GuzzleHttp\Client;

class SlackManager
{

    /**
     * @var string
     */
    private $slackToken;

    /**
     * @var string
     */
    private $apiEndpoint = 'https://slack.com/api/';


    /**
     * SlackManager constructor.
     * @param string $slackToken
     */
    public function __construct(string $slackToken)
    {
        $this->slackToken = $slackToken;
        $this->guzzle = new Client(['timeout' => 1000]);
    }


    /**
     * @param $method
     * @param array $args
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function call($method, $args = array())
    {
        $body = array_merge($args, array('token' => $this->slackToken));

        return $this->guzzle->request(
            'POST',
            sprintf(
                '%s/%s',
                $this->apiEndpoint,
                $method
            ),
            array(
                'headers' => array(),
                'form_params' => $body
            )
        );
    }

    public function getSlackUsersList()
    {
        return $this->call('users.list');
    }
}