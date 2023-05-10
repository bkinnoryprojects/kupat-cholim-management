<?php

require('dbpdo.class.php');
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	//If the customer requested to withdraw a specific person
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $people = db()->fetchAll("
	SELECT *
	FROM personalinfo
	LEFT JOIN corona_infections ON personalinfo.ID =  corona_infections.personalinfoID
	WHERE IDCard = ?", [$id]
	);
//Selects all the people in the list
} else {
    $people = db()->fetchAll("
	SELECT *
	FROM personalinfo
	LEFT JOIN corona_infections ON personalinfo.ID =  corona_infections.personalinfoID");
}
//Add the person's vaccinations to the list

foreach ($people as &$person) {

    $corona_vaccinations = db()->fetchAll("
	SELECT *
	FROM corona_vaccinations
	WHERE PersonalInfoID = ?
	ORDER BY Date",[$person->ID]);
    $person->corona_vaccinations = $corona_vaccinations;
}

if (isset($id)) {
    if (empty($people)) {
        $emptyObject = new stdClass();
        echo json_encode( $emptyObject, JSON_PRETTY_PRINT);
	} else {
        echo json_encode($people[0], JSON_PRETTY_PRINT);
	}
} else if(isset($_GET['all'])) {
	//Prints all people
	echo json_encode($people, JSON_PRETTY_PRINT);
}
}

// Route for inserting a new record into PersonalInfo table
//Requires input of first name, last name and ID card
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['idcard'])) 
{

    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname']?? '';
    $idcard = $_POST['idcard']?? '';
    $address = $_POST['address']?? '';
    $dateofbirth = $_POST['dateofbirth']?? '';
    $telephone = $_POST['telephone']?? '';
    $mobilephone = $_POST['mobilephone']?? '';
	$sql = "INSERT INTO PersonalInfo (FirstName, LastName, IDCard, Address, DateOfBirth, Telephone, MobilePhone) VALUES (?, ?, ?, ?, ?, ?, ?)";
	
    if (db()->execute($sql, [$firstname, $lastname, $idcard, $address, $dateofbirth, $telephone, $mobilephone]) === false) {
		//If the values were not captured correctly, no more data will be entered into the other tables
		$response['status'] = 'ERROR';
		}
		else
		{
		$ALLresponse=[];	
		$ALLresponse[]=  ['status'=>'success','message'=>'PersonalInfo Record added successfully'];
		$pid_o = db()->fetch("SELECT ID FROM PersonalInfo WHERE  IDCard = ?", [$idcard] );
		$pid = $pid_o->ID;
// Checking if a value was entered for the date of vaccination and the company and if it was entered in the correct form of an array. And if the number of vaccinations is equal to the number of companies written. and that no more than 4 vaccinations have been administered
		if ( !isset($_POST['vdate']) || !is_array($_POST['vdate']) || !isset($_POST['vManufacturer']) || !is_array($_POST['vManufacturer']) || (count($_POST['vdate']) !== count($_POST['vManufacturer'])) || count($_POST['vdate'])>4 || count($_POST['vManufacturer'])>4){
			 $response['message5'] = 'No information has been entered into the table CoronaVirus';
			 echo json_encode($response, JSON_PRETTY_PRINT);
			}
			else{
				$vdate = $_POST['vdate'];
				$vManufacturer = $_POST['vManufacturer'];
				
				// Checks if the dates are correct. If the date is incorrect, the vaccine will not enter the database, but the other vaccines will
				foreach ($vdate as $index => &$date) {
				//Checks if the format is correct
				$pattern = '/^\d{4}-\d{2}-\d{2}$/';
				if(preg_match($pattern, $date )=== false)
				{
					echo "The date string is invalid.";
				}
				//Checks if the date is an existing date
				else{
					// Convert the date string to a Unix timestamp
					$timestampdate = strtotime($date);
					// Check if the timestamp is a valid date
					//or if it's a future date
					if ($timestampdate === false || $timestampdate > time()) {
						// Invalid date
						echo "The date string is invalid.";
					}
					else{ 
						$sql2 = "INSERT INTO corona_vaccinations (PersonalInfoID, Date, Manufacturer)
							VALUES (?, ?, ?)";
						$query = db()->execute($sql2, [$pid, $date, $vManufacturer[$index]]);
							if ($query === true) {
							$response['status2'] = 'success';
							$response['message2'] = 'CoronaVirus Record added successfully.';
						} else {
							$response['status2'] = 'error';
							$response['message2'] = 'SQL Error: ' . $query;
						}
					}
				}
				
			
					
				}
				
				
			}
		}
		
	//Table of corona infections
   
		if (!isset($_POST['PositiveResultDate'])|| !isset($_POST['RecoveryDate']) )
				{
					$response['message3'] = 'No information has been entered into the table 	corona_infections';
					echo json_encode($response);
		}else {
			$PositiveResultDate=$_POST['PositiveResultDate'];
			$RecoveryDate=$_POST['RecoveryDate'];
			//Checks if the format is correct
			$pattern = '/^\d{4}-\d{2}-\d{2}$/';
			 if(preg_match($pattern, $PositiveResultDate )=== false ||
				preg_match($pattern, $RecoveryDate) === false){
					echo "The date string is invalid.";
				}
			else{ // Convert the date string to a Unix timestamp
					$timestampPositiveResult = strtotime($PositiveResultDate);
					$timestampRecovery = strtotime($RecoveryDate);
					// Check if the timestamp is a valid date
					//Checks that the date of receiving the positive answer is not in the future. (Recovery can be in the future)
					if ($timestampPositiveResult === false || $timestampRecovery === false || $timestampPositiveResult > time()) {
						
						// Invalid date
						echo "The date string is invalid.";
					}
					else if($timestampPositiveResult >= $timestampRecovery){
						echo "The date of recovery is before or equal to the date of illness";
						
					}
					else{
							$sql = "INSERT INTO corona_infections (personalinfoID,PositiveResultDate, RecoveryDate) VALUES (?, ?, ?)";
							if (db()->execute($sql, [$pid ,$PositiveResultDate,$RecoveryDate]) === true) {
								$response['status'] = 'success';
								$response['message'] = 'corona_infections Record added successfully';
							}
							else{
								$response['message3'] = 'No information has been entered into the table corona_infections';
								echo json_encode($response);
							}
					}
					
			}
			
		}
			
	 echo json_encode($ALLresponse, JSON_PRETTY_PRINT);	
		
}


	

	//ETGAR




if(isset($_GET['notVaccinated'])){
$sqle = "SELECT COUNT(*) totalNonVaccinated
         FROM PersonalInfo
         WHERE ID NOT IN (SELECT PersonalInfoID FROM corona_vaccinations)";
$total = db()->fetch($sqle);

$x = ['notVaccinated'=> $total->totalNonVaccinated];
echo json_encode($x);
}










