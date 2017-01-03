<?php

require dirname(__DIR__).'/vendor/autoload.php';

$meetup = new \Meetup\Meetup();
$payments = new \Meetup\MemberPayments($meetup->db);

$memberId = !empty($_POST['memberId']) ? $_POST['memberId'] : null;
$amount = !empty($_POST['amount']) ? $_POST['amount'] : null;

if (!$memberId || !$amount) {
    die();
}

$payments->addPayment($memberId, $amount);

$total = $payments->getTotal($memberId);

if ($total) {
    $total = number_format($total, 2);
}

$response = [
    'total' => $total
];

header('Content-Type: application/json');
echo json_encode($response);
