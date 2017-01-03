<?php

namespace Meetup;

use Carbon\Carbon;
use PDO;

class MemberPayments
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getTotal($memberId)
    {
        $query = $this->db->prepare('SELECT SUM(amount) FROM member_payments mp WHERE memberId = ?');
        $query->execute([$memberId]);

        return $query->fetchColumn();
    }

    public function addPayment($memberId, $amount)
    {
        $query = $this->db->prepare('INSERT INTO member_payments (memberId, amount, paidAt) VALUES (?, ?, ?)');
        $query->execute([
            $memberId,
            $amount,
            (new Carbon())->toDateTimeString()
        ]);
    }
}
