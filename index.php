<?php

require('dbpdo.class.php');
db('localhost', 'root', '1234', 'kupat_cholim', 'utf8mb4');

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
//****************** POST****************************************
$ALLresponse=[];
// Route for inserting a new record into PersonalInfo table
//Requires input of first name, last name and ID card
if ($_SERVER['REQUEST_METHOD'] == 'POST' )
	{
		if(!isset($_POST['firstname'])  || !isset($_POST['lastname']) || !isset($_POST['idcard'])) 
		{
			$ALLresponse[]= ['status'=>'error','message'=>'Basic values are missing for entering the person information into the database'];
	
		}
		else
		{
				


			$firstname = $_POST['firstname'] ?? '';
			$lastname = $_POST['lastname']?? '';
			$idcard = $_POST['idcard']?? '';
			$address = $_POST['address']?? '';
			$dateofbirth = $_POST['dateofbirth']?? '';
			$telephone = $_POST['telephone']?? '';
			$mobilephone = $_POST['mobilephone']?? '';
			$sql = "INSERT INTO PersonalInfo (FirstName, LastName, IDCard, Address, DateOfBirth, Telephone, MobilePhone) VALUES (?, ?, ?, ?, ?, ?, ?)";
			
			if (db()->execute($sql, [$firstname, $lastname, $idcard, $address, $dateofbirth, $telephone, $mobilephone]) === false)
				{
				//If the values were not captured correctly, no more data will be entered into the other tables
				$ALLresponse[] =  ['status'=>'error','message'=>'Data entry of personal information into the data base failed'];
				
				}
			else
				{
					$ALLresponse[]=  ['status'=>'success','message'=>'PersonalInfo Record added successfully'];
					$pid_o = db()->fetch("SELECT ID FROM PersonalInfo WHERE  IDCard = ?", [$idcard] );
					$pid = $pid_o->ID;
					// Checking if a value was entered for the date of vaccination and the company and if it was entered in the correct form of an array. And if the number of vaccinations is equal to the number of companies written. and that no more than 4 vaccinations have been administered
					if ( !isset($_POST['vdate']) || !is_array($_POST['vdate']) || !isset($_POST['vManufacturer']) || !is_array($_POST['vManufacturer']) || (count($_POST['vdate']) !== count($_POST['vManufacturer'])) || count($_POST['vdate'])>4 || count($_POST['vManufacturer'])>4){
						
					$ALLresponse[]=  ['status'=>'','message'=>'No information has been entered into the table corona_vaccinations'];
				
					}
					else
					{
							$vdate = $_POST['vdate'];
							$vManufacturer = $_POST['vManufacturer'];
							
							// Checks if the dates are correct. If the date is incorrect, the vaccine will not enter the database, but the other vaccines will
							foreach ($vdate as $index => &$date) {
							//Checks if the format is correct
							$pattern = '/^\d{4}-\d{2}-\d{2}$/';
							if(preg_match($pattern, $date )=== false)
							{
								$ALLresponse[]=  ['status'=>'error','message'=>'Entering a date not according to the format in the vaccination date'];
								
							}
							//Checks if the date is an existing date
							else
							{
								// Convert the date string to a Unix timestamp
								$timestampdate = strtotime($date);
								// Check if the timestamp is a valid date
								//or if it's a future date
								if ($timestampdate === false || $timestampdate > time()) {
									// Invalid date
								$ALLresponse[]=  ['status'=>'error','message'=>'Entering a date that does not exist or did not exist yet'];

								}
								else{ 
									$sql2 = "INSERT INTO corona_vaccinations (PersonalInfoID, Date, Manufacturer)
										VALUES (?, ?, ?)";
									$query = db()->execute($sql2, [$pid, $date, $vManufacturer[$index]]);
										if ($query === true) {
											$ALLresponse[]=  ['status'=>'success','message'=>'corona_vaccinations Record added successfully.'];

									} else {
										 $ALLresponse[]=  ['status'=>'error','message'=>'SQL Error: ' . $query];
									}
								}
							}
						
					
							
						}
						
						
					}
				}
				
			//Table of corona infections
		   
				if (!isset($_POST['PositiveResultDate'])|| !isset($_POST['RecoveryDate']) )
						{
							$ALLresponse[]=  ['status'=>'','message'=>'No information has been entered into the table  corona_infections'];

				}else {
					$PositiveResultDate=$_POST['PositiveResultDate'];
					$RecoveryDate=$_POST['RecoveryDate'];
					//Checks if the format is correct
					$pattern = '/^\d{4}-\d{2}-\d{2}$/';
					 if(preg_match($pattern, $PositiveResultDate )=== false ||
						preg_match($pattern, $RecoveryDate) === false){
							$ALLresponse[]=  ['status'=>'error','message'=>'The date format in the PositiveResultDate or in RecoveryDate data is incorrect'];

						}
					else{ // Convert the date string to a Unix timestamp
							$timestampPositiveResult = strtotime($PositiveResultDate);
							$timestampRecovery = strtotime($RecoveryDate);
							// Check if the timestamp is a valid date
							//Checks that the date of receiving the positive answer is not in the future. (Recovery can be in the future)
							if ($timestampPositiveResult === false || $timestampRecovery === false || $timestampPositiveResult > time()) {
								
								// Invalid date
								$ALLresponse[]=  ['status'=>'error','message'=>'The date  in the PositiveResultDate or in RecoveryDate data is incorrect'];

							}
							else if($timestampPositiveResult >= $timestampRecovery){
								$ALLresponse[]=  ['status'=>'error','message'=>'"The date of recovery is before or equal to the date of illness'];

							}
							else{
									$sql = "INSERT INTO corona_infections (personalinfoID,PositiveResultDate, RecoveryDate) VALUES (?, ?, ?)";
									if (db()->execute($sql, [$pid ,$PositiveResultDate,$RecoveryDate]) === true) {
										$ALLresponse[]=  ['status'=>'success','message'=>'corona_infections Record added successfully'];

									}
									else{
										$ALLresponse[]=  ['status'=>'','No information has been entered into the table corona_infections'];

									}
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










