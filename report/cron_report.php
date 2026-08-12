<?php
//error_reporting(0);
include 'includes/db_details.php';
//include 'includes/session.php';
//$start_date2=$_POST['start_date'];
// $end_date2=$_POST['end_date'];
$date1=date('Y-m-d',strtotime("-1 days"));
$kkl=0;
$impressions=$clicks=$revenue=$totalrevenue=$ecpm=$yourrev=0;


	$kkl=1;
	//$start_date2=$_POST['start_date'];
	//$end_date2=$_POST['end_date'];
	$query="delete from ".$db.".report where reportdate='".$date1."'  ";
	$result1 = $conn1->query($query);
	
	$query="select * from ".$db.".offers  ";
	$result1 = $conn1->query($query);
	$rows=mysqli_num_rows($result1);
	
	while($row1=mysqli_fetch_array($result1))
	{
		$offerid=$row1['offerid'];
		$revenueshare=$row1['revenueshare'];
		$databasename=$row1['databasename'];
		$offername=$row1['offername'];
	
	

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.platform.gamezop.com/v1/ad-revenue-data',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
		"start_date":"'.$date1.'",
		"end_date":"'.$date1.'",
		"property_id":"'.$offerid.'",
		"metrics":[
			"impressions",
			"clicks",
			"revenue",
			"total-revenue",
			"ctr",
			"ecpm"
		  ],
		  "breakdowns": [
			"property-id"
		  ]
		  }',
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer 8f222d9c-4207-4654-bf85-55e65ae6ed0c'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		//echo $response;exit;

			
			$kkr=json_decode($response,true);
			
			
			$res=$kkr['data']['ad_revenue'][0];
			if($kkr['success']=='true')
			{
				if(isset($res['impressions']))
				{
				$impressions=$res['impressions'];
				$clicks=$res['clicks'];
				$revenue=$res['revenue'] ;
				$totalrevenue=$res['total-revenue'];
				$ecpm=$res['ecpm'];
				}
				else{
					
					$impressions=$clicks=$revenue=$totalrevenue=$ecpm=$yourrev=0;
					
				}
				$yourrev=$revenueshare * $revenue;
				
			}
			else{
				$impressions=$clicks=$revenue=$totalrevenue=$ecpm=$yourrev=0;
				
			}
			
	
			$stmt1 = $conn1->prepare("INSERT INTO ".$db.".report (`reportdate`, `offerid`, `impressions`, `clicks`, `revenue`, `totalrevenue`, `ecpm`, `yourrevenue`) VALUES (?,?,?,?,?,?,?,?)");
			$stmt1->bind_param("ssssssss",$date1,$offerid,$impressions,$clicks,$revenue,$totalrevenue,$ecpm,$yourrev);
			$stmt1->execute();
	
	
	

	}

?>