<?php
/**
 * @var \Meetup\Reporter $reporter
 * @var \Meetup\Meetup $meetup
 * @var \Meetup\PaymentPeriod $paymentPeriod
 */

$fee = $paymentPeriod->getFee();
$dateFrom = $paymentPeriod->getFrom();
$dateTo = $paymentPeriod->getTo();

$previousPeriod = $meetup->payments->findPreviousPaymentPeriod($paymentPeriod);
if (!$previousPeriod) {
    die("Can't calculate for this period.");
}
?>
<h2>Suggested Contributions</h2>

<p>If the £<?=$fee?> Meetup fee for the period <strong><?=($dateFrom ? $dateFrom->format('M jS y') : '')?></strong> to
    <strong><?=($dateTo ? $dateTo->format('M jS y') : '')?></strong> were split based on the top 50 users in the <em>previous period</em>
    (<strong><?=($previousPeriod->getFrom() ? $previousPeriod->getFrom()->format('M jS y') : '')?></strong> to
    <strong><?=($previousPeriod->getTo() ? $previousPeriod->getTo()->format('M jS y') : '')?></strong>),
    each person should pay this much based on how much they RSVPd 'yes'.</p>

<?php
$totalRecentRsvps = $reporter->getTotalYesRsvps($previousPeriod);
$members = $reporter->getMembersYesRsvps($previousPeriod);

$totalRsvpsFromTopUsers = 0;
foreach ($members as $member) {
    $totalRsvpsFromTopUsers += $member->yesRsvps;
}
?>

<p>Total Yes RSVPs in previous period: <?=number_format($totalRecentRsvps)?></p>
<p>Total Yes RSVPs in previous period from these top 50 users: <?=number_format($totalRsvpsFromTopUsers)?></p>

<table class="table table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Yes RSVPs</th>
        <!--<th>% of Yes RSVPs</th>-->
        <th>% of Top 50 Member RSVPs</th>
        <th>Suggested Contribution</th>
        <th>Actual Contribution</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $totalPercent = 0;
    $totalTopPercent = 0;
    $totalFees = 0;

    foreach ($members as $i => $member) {
        echo '<tr>';

            echo '<td>'.($i + 1).'</td>';

            echo '<td><a href="'.$meetup->memberUrl($member).'" target="_blank">';
            echo $meetup->memberPhoto($member);
            echo $member->name.'</a></td>';

            echo '<td>'.number_format($member->yesRsvps).'</td>';

            /*$percent = $member->yesRsvps / $totalRecentRsvps * 100;
            $totalPercent += $percent;
            echo '<td>'.$percent.'%</td>';*/

            $topPercent = round($member->yesRsvps / $totalRsvpsFromTopUsers * 100, 2);
            $totalTopPercent += $topPercent;
            echo '<td>'.$topPercent.'%</td>';

            $memberFee = ceil($fee / 100 * $topPercent);
            echo '<td>&pound;'.$memberFee.'</td>';
            $totalFees += $memberFee;

            $actualPaid = $meetup->payments->getTotal($member->id, $paymentPeriod);
            $actualClass = $actualPaid >= $memberFee ? 'text-success' : 'text-danger';
            echo '<td class="'.$actualClass.'"><strong>'.($actualPaid ? '&pound;'.$actualPaid : '').'</strong></td>';

        echo '</tr>';
    }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th><?=number_format($totalRsvpsFromTopUsers)?></th>
            <!--<th><?=number_format($totalPercent)?>%</th>-->
            <th><?=number_format($totalTopPercent)?>%</th>
            <th>&pound;<?=$totalFees?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
