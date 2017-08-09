<?php
require __DIR__.'/vendor/autoload.php';
$meetup = new \Meetup\Meetup();
$reporter = new \Meetup\Reporter($meetup, $meetup->db);
$displayer = new \Meetup\ReportDisplayer($meetup);

$paymentPeriodId = !empty($_GET['paymentPeriodId']) ? (int) $_GET['paymentPeriodId'] : null;
if (!empty($paymentPeriodId)) {
    $paymentPeriod = $meetup->payments->findPaymentPeriod($paymentPeriodId);
}

$report = !empty($_GET['report']) ? $_GET['report'] : null;

include __DIR__.'/includes/header.php';

echo $meetup->navTabs($report);

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
        echo $meetup->paymentPeriodTabs($paymentPeriodId, '/?report=fee-split&paymentPeriodId=');
        if ($paymentPeriodId) {
            include __DIR__.'/reports/fee-split.php';
        }
        break;

    case 'payments':
        echo $meetup->paymentPeriodTabs($paymentPeriodId, '/?report=payments&paymentPeriodId=');
        if (!empty($paymentPeriod)) {
            ?>
            <h2>Members Who Have Contributed To Fees</h2>
            <?php
            $members = $reporter->getFeeContributors($paymentPeriod);
            ?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Total Contributions In Period</th>
                    <th>Total Contributions All Time</th>
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

                    $total = $meetup->payments->getTotal($member->id, $paymentPeriod);
                    echo '<td>&pound;'.number_format($total, 2).'</td>';

                    $total = $meetup->payments->getTotal($member->id);
                    echo '<td>&pound;'.number_format($total, 2).'</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>

            </table>
            <?php
        }
        break;


    case 'answers':
        ?>
        <h2>Profile Question Answers</h2>
        <?php
        $members = $reporter->getAllMembers();
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th style="width:160px;">Name</th>
                <th>Answers</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($members as $member) {
                $memberData = json_decode($member->data);
                echo '<tr>';
                echo '<td><a href="'.$meetup->memberUrl($member).'" target="_blank">';
                echo $meetup->memberPhoto($member);
                echo $member->name.'</a></td>';

                $questions = [];
                foreach ($memberData->group_profile->answers as $answer) {
                    $questions[] = [
                        'q' => $answer->question,
                        'a' => $answer->answer
                    ];
                }
                ?>
                    <td>
                        <table width="100%">
                            <tr>
                                <?php
                                foreach ($questions as $q) {
                                    echo '<th width="50%">'.$q['q'].'</th>';
                                }
                                ?>
                            </tr>
                            <tr>
                                <?php
                                foreach ($questions as $q) {
                                    echo '<td>'.$q['a'].'</td>';
                                }
                                ?>
                            </tr>
                        </table>
                    </td>
                <?php
                echo '</tr>';
            }
            ?>
            </tbody>

        </table>
        <?php
        break;
}

include __DIR__.'/includes/footer.php';
