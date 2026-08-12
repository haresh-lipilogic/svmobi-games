<?php
error_reporting(0);
include 'includes/db_details.php';
include 'includes/session.php';
//$start_date2=$_POST['start_date'];
// $end_date2=$_POST['end_date'];
$date1=date("Y-m-d");
$kkl=0;
$impressions=$clicks=$revenue=$totalrevenue=$ecpm=$yourrev=0;
$userid=$_SESSION['svmobiadmin'];

 $query="select * from ".$db.".login_username WHERE userid='".$userid."' ";
 $result1 = $conn1->query($query);
						$rows=mysqli_num_rows($result1);
						
						while($row1=mysqli_fetch_array($result1))
						{
							$access=$row1['access'];
							$userid5=$row1['userid'];
							
						}
						//echo $access;
						$kk=json_decode($access,true);
						$for=$kk['for'];
						$access=$kk['access'];
						$active=$kk['active'];
						
if($active==1)
{
	$query="select * from ".$db.".offers WHERE offerid='".$for."' ";
	$result1 = $conn1->query($query);
						$rows=mysqli_num_rows($result1);
						
						while($row1=mysqli_fetch_array($result1))
						{
							$revenueshare=$row1['revenueshare'];
							$databasename=$row1['databasename'];
							$offername=$row1['offername'];
						}
	
}
else{
	
	header("location:login.php");
}
if(isset($_POST['start_date']))
{
	$kkl=1;
	$start_date2=$_POST['start_date'];
	$end_date2=$_POST['end_date'];
	
	
	
	

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
"start_date":"'.$start_date2.'",
"end_date":"'.$end_date2.'",
"property_id":"'.$for.'",
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
//echo "response=".$response;exit;

	
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
		$yourrev=$revenueshare * $totalrevenue;
		
	}
	else{
		$impressions=$clicks=$revenue=$totalrevenue=$ecpm=$yourrev=0;
		
	}
	
	
	
	
	
	
}
$reportdate=date('Y-m-d',strtotime("-5 days"));


 $query="select * from ".$db.".report WHERE reportdate>='".$reportdate."' and offerid='".$for."' order by reportdate asc  ";
	$result1 = $conn1->query($query);
	$result2 = $conn1->query($query);
						$rows=mysqli_num_rows($result2);
						$ik=1;
						while($row12=mysqli_fetch_array($result2))
						{
							$reportdate1[$ik]=$row12['reportdate'];
							$totalrevenue1[$ik]=$row12['totalrevenue'];
							$impressions1[$ik]=$row12['impressions'];
							$ik++;
						}
						
						//print_r($impressions1);exit;
		$query="select * from ".$db.".report WHERE reportdate>='".$start_date2."' and  reportdate<='".$end_date2."' and offerid='".$for."' order by reportdate asc  ";
	$result1 = $conn1->query($query);				


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Svmobi Dashboard </title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/table2excel@1.1.4/dist/table2excel.min.js"></script>
  <style>
    :root {
      --sidebar-bg: #1e3a8a;
      --sidebar-active: #2563eb;
      --bg: #f8fafc;
      --card-bg: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --radius: 14px;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      height: 100vh;
      background: var(--bg);
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: var(--sidebar-bg);
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px 0;
    }

    .sidebar h2 {
      color: #fff;
      margin: 0 20px 24px;
      font-size: 18px;
    }

    .nav {
      display: flex;
      flex-direction: column;
    }

    .nav a {
      padding: 14px 20px;
      text-decoration: none;
      color: #cbd5e1;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav a.active {
      background: var(--sidebar-active);
      color: white;
      border-radius: 0 var(--radius) var(--radius) 0;
    }

    .logout {
      padding: 14px 20px;
      text-decoration: none;
      color: #f87171;
      font-size: 15px;
      display: block;
    }

    /* Main */
    .main {
      flex: 1;
      background: var(--bg);
      padding: 24px;
      overflow-y: auto;
    }

    .main h1 {
      margin: 0 0 20px;
      font-size: 22px;
      font-weight: 600;
    }

    .filters {
      display: flex;
      gap: 12px;
      margin-bottom: 20px;
    }

    .filters select {
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
      border-radius: var(--radius);
      background: white;
      font-size: 14px;
      color: var(--muted);
    }

    /* Stats */
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 16px;
      text-align: center;
    }

    .stat-card h3 {
      margin: 0;
      font-size: 14px;
      color: var(--muted);
    }

    .stat-card p {
      margin: 6px 0 0;
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
    }

    /* Chart */
    .chart {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 20px;
      margin-bottom: 24px;
    }

    /* Tables */
    .bottom {
     // display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
    }

    .table, .summary {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 8px;
      border-bottom: 1px solid #e2e8f0;
      font-size: 14px;
      text-align: left;
    }

    th {
      color: var(--muted);
      font-weight: 500;
    }

    /* Circle chart */
    .circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background: conic-gradient(#2563eb 70%, #e2e8f0 30%);
      margin: 0 auto 10px;
    }

    .summary p {
      text-align: center;
      font-size: 14px;
      color: var(--muted);
    }

    @media (max-width: 900px) {
      .bottom {
        grid-template-columns: 1fr;
      }
    }
	
	table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #D6EEEE;
}
  </style>
</head>
<body>
  <aside class="sidebar">
    <div>
	<image src="images/logo2.png">
      <h2>Dashboard</h2>
      <nav class="nav">
        <a href="#" class="active">Revenue Metrics</a>
      <!--  <a href="#">Payments</a>-->
      </nav>
    </div>
 <a href="logout.php" class="logout">Logout</a>
  </aside>

  <main class="main">
    <h1>Report</h1>
    <!--<div class="filters">
      <select><option>Filter 1</option></select>
      <select><option>Filter 2</option></select>
      <select><option>Filter 3</option></select>
      <select><option>Filter 4</option></select>
    </div>-->
	<div class="filters">
	 <form class="form-horizontal form-label-left input_mask" name="formname" id="formname" method="post">
	 Start Date
	<input class="stat-card" name="start_date"  value="<?php if($kkl>0){echo $start_date2;}else{echo $date1;}?>"  type="date" style="background:#e7f1d6">
	
	&nbsp;
	&nbsp;
	&nbsp;
	
	 End Date
	<input class="stat-card" name="end_date" value="<?php if($kkl>0){echo $end_date2;}else{echo $date1;}?>" type="date" style="background:#e7f1d6">
	
	
	&nbsp;
	&nbsp;
	&nbsp;
	
	 
	  <input type="submit" name="submit" class="stat-card" style="background:#e7f1d6">
	
	 
	</form>
	</div>
    <div class="stats">
      <div class="stat-card"><h3>Your Revenue</h3><p><?php echo number_format($yourrev,2);?></p></div>
      <div class="stat-card"><h3>Total Revenue</h3><p><?php echo number_format($totalrevenue,2);?></p></div>
      <div class="stat-card"><h3>Ad Impressions</h3><p><?php echo $impressions;?></p></div>
      <div class="stat-card"><h3>eCPM</h3><p><?php echo number_format($ecpm,2);?></p></div>
      <div class="stat-card"><h3>Clicks</h3><p><?php echo $clicks;?></p></div>
      <!--<div class="stat-card"><h3>CTR</h3><p>1.74%</p></div>-->
    </div>

    <div class="chart">
      <canvas id="lineChart"></canvas>
    </div>

    <div class="bottom">
	<button onclick="downloadTableAsCSV()">Download CSV</button>
      <div class="table">
        <table  id="dataTable">
          <thead>
            <tr><th>Date</th><th>Your Revenue</th><th>Total revenue</th><th>Ad Impressions</th><th>eCPM</th><th>Clicks</th></tr>
          </thead>
          <tbody>
		  <?php
		  while($row1=mysqli_fetch_array($result1))
			{
				
				
				echo "<tr><td>".$row1['reportdate']."</td><td>".number_format($row1['totalrevenue']*$revenueshare,2)."</td><td>".number_format($row1['totalrevenue'],2)."</td><td>".$row1['impressions']."</td><td>".number_format($row1['ecpm'],2)."</td><td>".$row1['clicks']."</td></tr>"	;	
		  
		 
		
			}
		?>
          </tbody>
        </table>
      </div>
      <!--<div class="summary">
        <div class="circle"></div>
        <p>CTR Distribution</p>
      </div>-->
    </div>
	<br><br>
	<div style="text-align:right;text-align-last: right;">
	 <label >Powered & Developed by SVMobi. All Rights Reserved</label>
	</div>
  </main>
<script>
function downloadTableAsCSV() {
  const table = document.getElementById("dataTable");
  let csv = [];

  // Loop through rows
  for (let row of table.rows) {
    let rowData = [];
    for (let cell of row.cells) {
      // Escape quotes and commas
      let text = cell.innerText.replace(/"/g, '""');
      rowData.push('"' + text + '"');
    }
    csv.push(rowData.join(","));
  }

  // Create CSV blob
  let csvContent = csv.join("\n");
  let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });

  // Create hidden download link
  let link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "table_data.csv";

  // Trigger download
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
  <script>
    const ctx = document.getElementById('lineChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
       // labels: ['7 Aug', '8 Aug', '9 Aug', '10 Aug', '11 Aug'],
        labels: ['<?php echo $reportdate1[1]; ?>','<?php echo $reportdate1[2]; ?>','<?php echo $reportdate1[3]; ?>','<?php echo $reportdate1[4]; ?>','<?php echo $reportdate1[5]; ?>'],
        datasets: [{
          label: 'Revenue',
          //data: [0.100, 0.310, 0.105, 0.1300, 0.320],
          data: ['<?php echo $totalrevenue1[1]; ?>','<?php echo $totalrevenue1[2]; ?>','<?php echo $totalrevenue1[3]; ?>','<?php echo $totalrevenue1[4]; ?>','<?php echo $totalrevenue1[5]; ?>'],
          borderColor: '#fbbf24',
          backgroundColor: 'rgba(251,191,36,0.2)',
          fill: true,
          tension: 0.5
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: false }
        }
      }
    });
  </script>
  
 
</body>
</html>