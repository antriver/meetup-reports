<?php
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$meetup = new \Meetup\Meetup();
$reporter = new \Meetup\Reporter($meetup, $meetup->db);
$displayer = new \Meetup\ReportDisplayer($meetup);

$report = !empty($_GET['report']) ? $_GET['report'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
    <style type="text/css">
        .container {
            padding: 30px;
        }
    </style>
</head>
<body>
<div class="container">

<?php

$reports = [
    'popular-meetups' => 'Most Popular Events',
    'unpopular-meetups' => 'Least Popular Events',
    'loneliest-meetups' => 'Loneliest Events',
    'most-yes-members' => 'Most Yes RSVPs',
    'most-no-members' => 'Most No RSVPs',
    'most-no-shows' => 'Most No-Shows',
];

?>
<ul class="nav nav-pills">
    <?php
    foreach ($reports as $key => $name) {
        echo '<li role="presentation" '.($report === $key ? 'class="active"' : '').'>';
            echo '<a href="/?report=' . $key . '">'.$name.'</a>';
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
}
?>
</div>
</body>
</html>
