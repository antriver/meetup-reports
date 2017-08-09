<?php
require __DIR__.'/vendor/autoload.php';
$meetup = new \Meetup\Meetup();

$paymentPeriodId = !empty($_GET['paymentPeriodId']) ? (int) $_GET['paymentPeriodId'] : null;

$members = $meetup->members->all();

include __DIR__.'/includes/header.php';

echo $meetup->navTabs('add-payments');

echo $meetup->paymentPeriodTabs($paymentPeriodId, '/payments.php?paymentPeriodId=');

if ($paymentPeriodId) {
    $paymentPeriod = $meetup->payments->findPaymentPeriod($paymentPeriodId);
    if (!$paymentPeriod) {
        die('Invalid paymentPeriodId');
    }
    ?>
    <script>
        var paymentPeriodId = '<?=$paymentPeriod->getId()?>';
    </script>
    <style type="text/css">
        .add-payment-btn {
            padding: 5px 8px;
            margin-right: -1px;
        }
    </style>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Name</th>
            <th>Total Paid</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($members as $member) {
            echo '<tr data-id="'.$member->id.'">';

            echo '<td><a href="'.$meetup->memberUrl($member).'" target="_blank">';
            echo $meetup->memberPhoto($member);
            echo $member->name.'</a></td>';

            $total = $meetup->payments->getTotal($member->id, $paymentPeriod);

            echo '<td class="total">';
            if ($total) {
                echo '&pound;'.number_format($total, 2);
            }
            echo '</td>';

            echo '<td style="width: 188px;"> 
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="1">1</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="2">2</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="3">3</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="4">4</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="5">5</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn">&gt;</a>
            </td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>

    <?php
}
include __DIR__.'/includes/footer.php';
