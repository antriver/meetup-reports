<?php
/**
* @var \Meetup\Reporter $reporter
* @var \Meetup\Meetup $meetup
*/

$fee = $meetup->config['meetupFee'];
?>
<h2>Suggested Contributions</h2>

<p>If the £<?=$fee?> Meetup fee for the last 6 months were split between the top 50 users of the group in
    the last 6 months, each person should pay this much based on how much they RSVPd 'yes'.</p>

<?php
$totalRecentRsvps = $reporter->getRecentTotalYesRsvps();
$members = $reporter->getMembersRecentYesRsvps();

$totalRsvpsFromTopUsers = 0;
foreach ($members as $member) {
    $totalRsvpsFromTopUsers += $member->yesRsvps;
}
?>

<p>Total Yes RSVPs in last 6 months: <?=number_format($totalRecentRsvps)?></p>
<p>Total Yes RSVPs in last 6 months from the top 50 users: <?=number_format($totalRsvpsFromTopUsers)?></p>

<table class="table table-striped">
    <thead>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Yes RSVPs</th>
        <!--<th>% of Yes RSVPs</th>-->
        <th>% of Top 50 Member RSVPs</th>
        <th>Suggested Contribution</th>
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
        </tr>
    </tfoot>
</table>
