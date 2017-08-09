<?php

namespace Meetup;

use Carbon\Carbon;

class PaymentPeriod
{
    /**
     * @var float
     */
    private $fee;

    /**
     * @var Carbon
     */
    private $from;

    /**
     * @var
     */
    private $id;

    /**
     * @var Carbon
     */
    private $to;

    /**
     * @param $id
     * @param string $from
     * @param string $to
     * @param float|null $fee
     */
    public function __construct($id, $from, $to, $fee = null)
    {
        $this->id = $id;

        if (!empty($from)) {
            $this->from = new Carbon($from);
        }

        if (!empty($to)) {
            $this->to = new Carbon($to);
        }

        $this->fee = $fee;
    }

    /**
     * @return float
     */
    public function getFee()
    {
        return $this->fee;
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
