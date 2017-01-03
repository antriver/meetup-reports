<?php

namespace Meetup;

use GuzzleHttp\Client;
use PDO;

abstract class AbstractDataSource
{
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var PDO
     */
    protected $db;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Meetup
     */
    protected $meetup;

    /**
     * Members constructor.
     *
     * @param Meetup $meetup
     * @param array  $config
     * @param Client $guzzle
     * @param PDO    $db
     */
    public function __construct(Meetup $meetup, array $config, Client $guzzle, PDO $db)
    {
        $this->guzzle = $guzzle;
        $this->db = $db;
        $this->config = $config;
        $this->meetup = $meetup;
    }

    abstract public function clear();

    abstract public function update();

    protected function request($endpoint, $data = [], $method = 'GET')
    {
        $uri = 'https://api.meetup.com'.$endpoint;

        $options = [

        ];

        if ($method === 'GET') {
            $options['query'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        $options['query']['key'] = $this->config['apiKey'];

        $response = $this->guzzle->request($method, $uri, $options);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        return json_decode((string)$response->getBody());
    }

    protected function insert($table, $keys, $dates, $data)
    {
        $values = [];
        foreach ($keys as $key) {
            if (!empty($data->{$key}) && in_array($key, $dates)) {
                $values[] = date('Y-m-d H:i:s', ($data->{$key} / 1000));
            } else {
                $values[] = empty($data->{$key}) ? null : $data->{$key};
            }
        }

        $keys[] = 'data';
        $values[] = json_encode($data);

        $placeholders = array_fill(0, count($keys), '?');
        $sql = "INSERT INTO {$table} (".implode(',', $keys).") VALUES(".implode(',', $placeholders).")";

        $sql .= " ON DUPLICATE KEY UPDATE ";
        foreach ($keys as $key) {
            $sql .= "`{$key}` = VALUES(`{$key}`),";
        }
        $sql = rtrim($sql, ',');

        $query = $this->db->prepare($sql);

        return $query->execute($values);
    }
}
