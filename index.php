<?php // Quick and dirty advent calendar picker 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set locale (Finland here)
date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, array('fi_FI.UTF-8','fi_FI@euro','fi_FI','finnish'));

$participants = file( 'participants.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // some names separated by newlines

$winners = json_decode( file_get_contents( 'winners.json' ), true );

if ( !$winners || !is_array( $winners ) ) {
    // first use, or a corrupt file, initiate to blank array
    $winners = [];
}

$today = date("Y-m-d");
$date_req = $_GET['date'] ?? $today;
$tomorrow = strtotime("+1 day", strtotime($date_req));
$yesterday = strtotime("-1 day", strtotime($date_req));

// Validate date
$tempDate = explode('-', $date_req);
// checkdate(month, day, year)
if ( ! checkdate($tempDate[1], $tempDate[2], $tempDate[0]) ) die('Date not found!');

// Check if a specific date was requested
if (!empty($date_req) && !empty($winners)) {
    if (array_key_exists($date_req, $winners)) {
        $todays_winners = $winners[ $date_req ];
    }
}

if ( date( 'm' ) === '12' && date('m', strtotime($date_req)) === '12') {
    if ( !array_key_exists( $date_req, $winners ) ) {
        shuffle( $participants );
        $winners[ $date_req ] = $participants;
        file_put_contents( 'winners.json', json_encode( $winners ) ); // god is more pleased
    }
    $todays_winners = $winners[ $date_req ];
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

    <a href="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . strtok($_SERVER['REQUEST_URI'], '?'). '?date=' . date('Y-m-d', $yesterday); ?>"><?php echo date("d-m-Y", $yesterday); ?></a>
    |
    <span><strong><?php echo date('d-m-Y', strtotime($date_req)) ?></strong></span> 
    |
    <a href="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . strtok($_SERVER['REQUEST_URI'], '?') . '?date=' . date('Y-m-d', $tomorrow); ?>"><?php echo date("d-m-Y", $tomorrow); ?></a>

    <?php if (date('m') !== '12' || date('m', strtotime($date_req)) !== '12' ) die("<h1>It's not December!</h1></body></html>"); ?>
    
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