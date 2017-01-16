<?php
require __DIR__.'/vendor/autoload.php';
$meetup = new \Meetup\Meetup();
$reporter = new \Meetup\Reporter($meetup, $meetup->db);
$displayer = new \Meetup\ReportDisplayer($meetup);

$report = !empty($_GET['report']) ? $_GET['report'] : null;

include __DIR__.'/includes/header.php';

$reports = [
    'popular-meetups' => 'Most Popular Events',
    'unpopular-meetups' => 'Least Popular Events',
    'loneliest-meetups' => 'Loneliest Events',
    'most-yes-members' => 'Most Yes RSVPs',
    'recent-most-yes-members' => 'Most Yes RSVPs (Last 3 Months)',
    'most-no-members' => 'Most No RSVPs',
    'most-no-shows' => 'Most No-Shows',
    'fee-split' => 'Suggested Contributions',
    'payments' => 'Fee Contributors',
];

?>
    <ul class="nav nav-pills">
        <?php
        foreach ($reports as $key => $name) {
            echo '<li role="presentation" '.($report === $key ? 'class="active"' : '').'>';
            echo '<a href="/?report='.$key.'">'.$name.'</a>';
            echo '</li>';
        }
        ?>
    </ul>
<?php

switch ($report) {
    case 'popular-meetups':
        ?>
        <h2>Most Popular Meetups</h2>
        <p>100 events with the most <strong>"yes"</strong> RSVPs.</p>
        <?php
        $events = $reporter->getMostYesEvents();
        $displayer->showEvents($events);
        break;

    case 'unpopular-meetups':
        ?>
        <h2>Least Popular Meetups</h2>
        <p>100 events with the highest percentage of <strong>"no"</strong> RSVPs.</p>
        <?php
        $events = $reporter->getMostNoEvents();
        $displayer->showEvents($events);
        break;

    case 'loneliest-meetups':
        ?>
        <h2>Loneliest Meetups</h2>
        <p>100 events with the least <strong>"yes"</strong> RSVPs, which were not cancelled.</p>
        <?php
        $events = $reporter->getLeastYesEvents();
        $displayer->showEvents($events);
        break;

    case 'most-yes-members':
        ?>
        <h2>Members Who Have RSVPd Yes The Most</h2>
        <?php
        $members = $reporter->getMostYesMembers();
        $displayer->showMembers($members);
        break;

    case 'recent-most-yes-members':
        ?>
        <h2>Members Who Have RSVPd Yes The Most (Last 3 Months)</h2>
        <?php
        $members = $reporter->getRecentMostYesMembers();
        $displayer->showMembers($members);
        break;

    case 'most-no-members':
        ?>
        <h2>Members Who Have RSVPd No The Most</h2>
        <?php
        $members = $reporter->getMostNoMembers();
        $displayer->showMembers($members);
        break;

    case 'most-no-shows':
        ?>
        <h2>Members Who Have Not Turned Up The Most</h2>
        <?php
        $members = $reporter->getMostNoShows();
        $displayer->showMembers($members);
        break;

    case 'fee-split':
        include __DIR__.'/reports/fee-split.php';
        break;

    case 'payments':
        ?>
        <h2>Members Who Have Contributed To Fees</h2>
        <?php
        $members = $reporter->getFeeContributors();
        $payments = new \Meetup\MemberPayments($meetup->db);
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Total Contributions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($members as $member) {
                echo '<tr>';
                echo '<td><a href="'.$meetup->memberUrl($member).'" target="_blank">';
                echo $meetup->memberPhoto($member);
                echo $member->name.'</a></td>';

                echo '<td>'.$member->paidAt.'</td>';
                echo '<td>&pound;'.number_format($member->amount, 2).'</td>';

                $total = $payments->getTotal($member->id);
                echo '<td>&pound;'.number_format($total, 2).'</td>';
                echo '</tr>';
            }
            ?>
            </tbody>

        </table>
        <?php
        break;
}

include __DIR__.'/includes/footer.php';
