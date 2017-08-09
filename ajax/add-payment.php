<?php

require dirname(__DIR__).'/vendor/autoload.php';

$meetup = new \Meetup\Meetup();

$memberId = !empty($_POST['memberId']) ? $_POST['memberId'] : null;
$amount = !empty($_POST['amount']) ? $_POST['amount'] : null;
$paymentPeriodId = !empty($_POST['paymentPeriodId']) ? (int) $_POST['paymentPeriodId'] : null;

$paymentPeriod = $meetup->payments->findPaymentPeriod($paymentPeriodId);

if (!$memberId || !$amount || !$paymentPeriod) {
    die();
}

$meetup->payments->addPayment($memberId, $amount, $paymentPeriod);

$total = $meetup->payments->getTotal($memberId, $paymentPeriod);

if ($total) {
    $total = number_format($total, 2);
}

$response = [
    'total' => $total,
];

header('Content-Type: application/json');
echo json_encode($response);
