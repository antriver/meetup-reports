$(document).on('click', '.add-payment-btn', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var $row = $btn.closest('tr');

    var memberId = $row.attr('data-id');

    var amount = $btn.attr('data-amount');
    if (!amount) {
        amount = prompt("Enter an amount");
        if (!amount) {
            return false;
        }
    }

    console.log('click', memberId, amount);

    $.post(
        '/ajax/add-payment.php',
        {
            memberId: memberId,
            amount: amount
        },
        function (response) {
            if (response.total) {
                var $totalCell = $row.find('.total');
                $totalCell.html('&pound;' + response.total);
            }
        }
    );
});
