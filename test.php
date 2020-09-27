 <?php 

$tag=$_POST['tag'];
$tmp=$_POST['temp'];
$crt=$_POST['crt'];

$now = date("Y-m-d H:i:s");
$td = date('Y-m-d H:i:s',strtotime('+5 hour',strtotime($now)));


$servername = "localhost";
$username = "root";
$pass="";
$dbname="test";

define('LINE_API',"https://notify-api.line.me/api/notify");
$token = "xlUvP1ybPmnEorZIsWZ2J10u3T3B3o1T7rVF3VBQtZw"; //ใส่Token ที่copy เอาไว้
$text = 'Temp Abnormal';




$conn = new mysqli($servername, $username,$pass,$dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


//Insert in table
$sql = "INSERT INTO arduino (tag, temp,criteria, dateStamp) VALUES ('$tag','$tmp','$crt','$td')";
if ($conn->query($sql) === TRUE) {
  echo "New record created successfully";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}


//Insert with condition
$sqlget="SELECT count(1) as 'Count' FROM `arduino_cal` WHERE STATUS=1 and CONVERT(dateStamp,Date) = CURDATE()";
$result = mysqli_query($conn, $sqlget);

if (mysqli_num_rows($result) > 0) {
	$row = $result->fetch_assoc();
  	$ct= $row["Count"];
  	if($ct==0 and $tmp>$crt){
  		$sql1 = "INSERT INTO arduino_cal (tag,temp,criteria, dateStamp,status) VALUES ('$tag','$tmp','$crt','$td','1')";
		if ($conn->query($sql1) === TRUE) {
  			echo "New record created successfully";
  			//Line notification
  			$res = notify_message($text,$token);
  			
		} 
		else {
  				echo "Error: " . $sql1 . "<br>" . $conn->error;
		}
  	}
  	elseif ($ct==0 and $tmp<$crt){
  		$sql2 = "INSERT INTO arduino_cal (tag,temp,criteria, dateStamp,status) VALUES ('$tag','$tmp','$crt','$td','0')";
		if ($conn->query($sql2) === TRUE) {
  			echo "New record created successfully";
		} 
		else {
  				echo "Error: " . $sql2 . "<br>" . $conn->error;
		}

  	}
  	else{
  		$sql3 = "INSERT INTO arduino_cal (tag,temp,criteria, dateStamp,status) VALUES ('$tag','$tmp','$crt','$td','0')";
		if ($conn->query($sql3) === TRUE) {
  			echo "New record created successfully";
		} 
		else {
  				echo "Error: " . $sql3 . "<br>" . $conn->error;
		}
  	}

  }

else {
  
  echo "0 results";
}



$conn->close();

function notify_message($message,$token){
			 $queryData = array('message' => $message);
			 $queryData = http_build_query($queryData,'','&');
			 $headerOptions = array( 
			         'http'=>array(
			            'method'=>'POST',
			            'header'=> "Content-Type: application/x-www-form-urlencoded\r\n"
			                      ."Authorization: Bearer ".$token."\r\n"
			                      ."Content-Length: ".strlen($queryData)."\r\n",
			            'content' => $queryData
			         ),
			 );
			 $context = stream_context_create($headerOptions);
			 $result = file_get_contents(LINE_API,FALSE,$context);
			 $res = json_decode($result);
			 return $res;
			}



 ?>

