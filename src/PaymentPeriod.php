<?php

namespace Meetup;

use Carbon\Carbon;

class PaymentPeriod
{
    /**
     * @var Carbon
     */
    private $from;

    private $id;

    /**
     * @var Carbon
     */
    private $to;

    /**
     * @param $id
     * @param string $from
     * @param string $to
     */
    public function __construct($id, $from, $to)
    {
        $this->id = $id;

        if (!empty($from)) {
            $this->from = new Carbon($from);
        }

        if (!empty($to)) {
            $this->to = new Carbon($to);
        }
    }

    /**
     * @return Carbon
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Carbon
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
