<?php

namespace Meetup;

use GuzzleHttp\Client;
use PDO;

class Meetup
{
    /**
     * @var array
     */
    public $config;

    /**
     * @var Client
     */
    public $guzzle;

    /**
     * @var PDO
     */
    public $db;

    /**
     * @var Events
     */
    public $events;

    /**
     * @var Members
     */
    public $members;

    /**
     * @var MemberPayments
     */
    public $payments;

    /**
     * @var Rsvps
     */
    public $rsvps;

    public function __construct()
    {
        $this->config = require dirname(__DIR__).'/config.php';
        $this->guzzle = new Client();
        $this->db = new PDO(
            "mysql:host={$this->config['dbHost']};dbname={$this->config['dbName']}",
            $this->config['dbUser'],
            $this->config['dbPass']
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $this->events = new Events($this, $this->config, $this->guzzle, $this->db);
        $this->members = new Members($this, $this->config, $this->guzzle, $this->db);
        $this->payments = new MemberPayments($this->db);
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

    public function memberPhoto($member)
    {
        $img = null;
        if ($member->data) {
            $data = json_decode($member->data);
            if (!empty($data->photo)) {
                $img = $data->photo->thumb_link;
            }
        }

        if ($img) {
            return '<img src="'.$img.'" class="user-img"/> ';
        }

        return '';
    }

    public function navTabs($selected)
    {
        $reports = [
            'popular-meetups' => ['Most Popular Events', '/?report=popular-meetups'],
            'unpopular-meetups' => ['Least Popular Events', '/?report=unpopular-meetups'],
            'loneliest-meetups' => ['Loneliest Events', '/?report=loneliest-meetups'],
            'most-yes-members' => ['Most Yes RSVPs', '/?report=most-yes-members'],
            'recent-most-yes-members' => ['Most Yes RSVPs (Last 3 Months)', '/?report=recent-most-yes-members'],
            'most-no-members' => ['Most No RSVPs', '/?report=most-no-members'],
            'most-no-shows' => ['Most No-Shows', '/?report=most-no-shows'],
            'answers' => ['Answers', '/?report=answers'],
            'fee-split' => ['Suggested Payments', '/?report=fee-split'],
            'payments' => ['Payments Received', '/?report=payments'],
            'add-payments' => ['Add Payments', '/payments.php'],
        ];

        $r = '<ul class="nav nav-pills">';
        foreach ($reports as $name => $data) {
            $r .= '<li role="presentation" '.($selected === $name ? 'class="active"' : '').'>';
            $r .= '<a href="'.$data[1].'">'.$data[0].'</a>';
            $r .= '</li>';
        }
        $r .= '</ul>';

        return $r;
    }

    public function paymentPeriodTabs($selectedId, $url)
    {
        $r = '<h3>Payment Period</h3>';

        $r .= '<ul class="nav nav-pills">';

        foreach ($this->payments->getPaymentPeriods() as $paymentPeriod) {
            $r .= '<li role="presentation" '.($selectedId == $paymentPeriod->getId() ? 'class="active"' : '').'>';

            $r .= '<a href="'.$url.$paymentPeriod->getId().'">';

            $r .= ($paymentPeriod->getFrom() ? $paymentPeriod->getFrom()->format('M jS y') : '');
            $r .= ' - ';
            $r .= ($paymentPeriod->getTo() ? $paymentPeriod->getTo()->format('M jS y') : '');
            $r .= '</a>';
            $r .= '</li>';
        }

        $r .= '</ul>';

        return $r;
    }
}
