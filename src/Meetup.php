<?php

namespace Meetup;

use GuzzleHttp\Client;
use PDO;

class Meetup
{
    /**
     * @var mixed
     */
    private $config;

    /**
     * @var Client
     */
    public $guzzle;

    /**
     * @var PDO
     */
    public $db;

    public function __construct()
    {
        $this->config = require dirname(__DIR__).'/config.php';
        $this->guzzle = new Client();
        $this->db = new PDO("mysql:host=127.0.0.1;dbname=meetup", 'root', 'root');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $this->events = new Events($this, $this->config, $this->guzzle, $this->db);
        $this->members = new Members($this, $this->config, $this->guzzle, $this->db);
        $this->rsvps = new Rsvps($this, $this->config, $this->guzzle, $this->db);
    }

    public function eventUrl($event)
    {
        return 'https://www.meetup.com/'.$this->config['groupUrl'].'/events/'.$event->id;
    }

    public function memberUrl($member)
    {
        return 'https://www.meetup.com/'.$this->config['groupUrl'].'/members/'.$member->id;
    }
}
