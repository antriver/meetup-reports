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

    public function getTotal($memberId, PaymentPeriod $paymentPeriod = null)
    {
        $params = [$memberId];
        $sql = 'SELECT SUM(amount) FROM member_payments mp WHERE memberId = ?';
        if ($paymentPeriod) {
            $sql .= ' AND paymentPeriodId = ?';
            $params[] = $paymentPeriod->getId();
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchColumn();
    }

    public function addPayment($memberId, $amount, PaymentPeriod $paymentPeriod)
    {
        $query = $this->db->prepare(
            'INSERT INTO member_payments (memberId, amount, paidAt, paymentPeriodId) VALUES (?, ?, ?, ?)'
        );
        $query->execute(
            [
                $memberId,
                $amount,
                (new Carbon())->toDateTimeString(),
                $paymentPeriod->getId()
            ]
        );
    }

    /**
     * @return PaymentPeriod[]
     */
    public function getPaymentPeriods()
    {
        $query = $this->db->prepare('SELECT * FROM payment_periods ORDER BY `from`');
        $query->execute();

        $results = [];

        foreach ($query->fetchAll() as $result) {
            $results[] = new PaymentPeriod($result->id, $result->from, $result->to, $result->fee);
        }

        return $results;
    }

    public function findPaymentPeriod($id)
    {
        $query = $this->db->prepare('SELECT * FROM payment_periods WHERE id = ?');
        $query->execute([$id]);

        if ($result = $query->fetch()) {
            return new PaymentPeriod($result->id, $result->from, $result->to, $result->fee);
        }

        return null;
    }

    public function findPreviousPaymentPeriod(PaymentPeriod $paymentPeriod)
    {
        $sql = "select * from payment_periods where `to` <= ? order by `to` desc limit 1";

        $query = $this->db->prepare($sql);
        $query->execute([$paymentPeriod->getFrom()]);

        if ($result = $query->fetch()) {
            return new PaymentPeriod($result->id, $result->from, $result->to, $result->fee);
        }

        return null;
    }
}
