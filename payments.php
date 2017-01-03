<?php
require __DIR__.'/vendor/autoload.php';
$meetup = new \Meetup\Meetup();

$members = $meetup->members->all();
$payments = new \Meetup\MemberPayments($meetup->db);

include __DIR__.'/includes/header.php';

?>
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
            echo '<td><a href="'.$meetup->memberUrl($member).'" target="_blank">'.$member->name.'</a></td>';

            $total = $payments->getTotal($member->id);

            echo '<td class="total">';
            if ($total) {
                echo '&pound;'.number_format($total, 2);
            }
            echo '</td>';

            echo '<td> 
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="1">&pound;1</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="2">&pound;2</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn" data-amount="5">&pound;5</a>
                <a href="#" class="btn btn-primary btn-sm add-payment-btn">Other</a>
            </td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>

<?php
include __DIR__.'/includes/footer.php';
