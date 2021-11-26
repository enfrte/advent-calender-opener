<?php // Quick and dirty advent calendar picker 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set locale (Finland here)
date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, array('fi_FI.UTF-8','fi_FI@euro','fi_FI','finnish'));

$today = date("Y-m-d");

$participants = file_get_contents('participants.txt');
$participants_exp = explode(PHP_EOL, $participants);

require 'winners_db.php'; /* database - containes default <?php $winners = []; ?> */

if (date('m') == 12) {
    if (!in_array($today, array_keys($winners))) {
        shuffle($participants_exp);
        $winners[$today] = $participants_exp;
        $new_data = '$winners = ' . var_export($winners, true) . ';';
        file_put_contents('winners_db.php', '<?php ' . $new_data . ' ?>'); // may god forgive me
    }
    $todays_winners = $winners[$today];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advent calendar</title>
</head>
<body>

    <?php if (date('m') != 12) die("<h1>It's not December!</h1></body></html>"); ?>
    
    <h1>Who gets to open today's advent calendar?!?!?!?</h1>
    <h3>It's...</h3>
    <h1><?php echo $todays_winners[0] ?>!</h1>
    <p>If <?php echo $todays_winners[0] ?> is not here, then the order is...</p>
    <h3>
        <?php 
            for ($i=1; $i <= count($todays_winners) - 1; $i++) { 
                echo "<p>" . $todays_winners[$i] . "</p>";
            }
        ?>
    </h3>

</body>
</html>