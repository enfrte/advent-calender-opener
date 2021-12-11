<?php // Quick and dirty advent calendar picker 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set locale (Finland here)
date_default_timezone_set('Europe/Helsinki');
setlocale(LC_ALL, array('fi_FI.UTF-8','fi_FI@euro','fi_FI','finnish'));

$participants = file( 'participants.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // some names separated by newlines
$winners = getWinners();

$today = date("Y-m-d");
$Ymd_date_req = $_GET['date'] ?? $today; 
validateDate($Ymd_date_req);

$date_ts = strtotime(date($Ymd_date_req . " H:i:s")); // ts = timestamp

checkDatePresentOrPast($date_ts);
checkDateInRange($date_ts);

$todays_winners = assignWinners($date_ts, $winners, $participants);

// Navigation dates
$tomorrow = strtotime("+1 day", $date_ts);
$yesterday = strtotime("-1 day", $date_ts);

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

	<?php if (checkDateInRange($yesterday)) { ?>
		<a href="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . strtok($_SERVER['REQUEST_URI'], '?'). '?date=' . date('Y-m-d', $yesterday); ?>">
			<?php echo date("d-m-Y", $yesterday); ?> 
		</a>
	<?php } ?>
	|
	<span><strong><?php echo date('d-m-Y', $date_ts) ?></strong></span> 
	|
	<?php if (checkDateInRange($tomorrow)) { ?>
		<a href="<?php echo 'http://' . $_SERVER['SERVER_NAME'] . strtok($_SERVER['REQUEST_URI'], '?') . '?date=' . date('Y-m-d', $tomorrow); ?>">
			<?php echo date("d-m-Y", $tomorrow); ?>
		</a>
	<?php } ?>

	
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


<?php 

// Validates a date
function validateDate($date) {
	$date_split = explode(" ",$date); // "Y-m-d H:i:s"
	$Ymd_date = $date_split[0];
	$Ymd_exp = explode('-', $Ymd_date);

	// checkdate(month, day, year)
	if ( ! checkdate($Ymd_exp[1], $Ymd_exp[2], $Ymd_exp[0]) ) {
		die('Date not found!');
	}
}

// Validate range of calendar days (1st - 25th Dec) for this year
function checkDateInRange($date_ts)
{
	$year = date('Y');
	$first_calendar_day_ts = strtotime('1st December ' . $year . '+0 hours 0 minutes 0 seconds');
	$last_calendar_day_ts = strtotime('25th December ' . $year . '+24 hours 0 minutes 0 seconds');

	if ($first_calendar_day_ts > $date_ts) {
		die("It's not December!");            
	}
	if ($last_calendar_day_ts < $date_ts) {
		die("It's after the 25th!");            
	}
	return true;
}

// We shouldn't peek ahead :P Check if requested date is in the future.
function checkDatePresentOrPast($date_ts)
{
	$today_ts = strtotime(date("Y-m-d") . '+24 hours 0 minutes 0 seconds');

	// Check if date_req not in future - We can only open on the day or view past winners
	if ($date_ts > $today_ts) {
		die("No time travel allowed!");            
	}
}

function getWinners()
{
	$winners = json_decode( file_get_contents( 'winners.json' ), true );
	if ( !$winners || !is_array( $winners ) ) {
		// first use, or a corrupt file, initiate to blank array
		$winners = [];
	}
	return $winners;
}

function assignWinners($date_ts, $winners, $participants)
{
	$Ymd_date = date('Y-m-d', $date_ts);
	// Check if a specific date was requested and check the winners log
	if (!empty($Ymd_date) && !empty($winners)) {
		if (array_key_exists($Ymd_date, $winners)) {
			return $winners[ $Ymd_date ];
		}
	}
	
	// If no winners, assign a new winner
	if ( !array_key_exists( $Ymd_date, $winners ) ) {
		shuffle( $participants );
		$winners[ $Ymd_date ] = $participants;
		file_put_contents( 'winners.json', json_encode( $winners ) );
		return $winners[ $Ymd_date ];
	}

	die("Couldn't find or assign winners :(");  
}

?>