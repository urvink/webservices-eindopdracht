<?php
/**
 * Created by PhpStorm.
 * User: U. Kirindongo
 * Date: 12/26/13
 * Time: 1:10 PM
 * **************************************
 *
 */

/**
 * Check for request method
 * Accepted: GET, PUT, POST, OPTIONS, DELETE
 */
require_once('includes/config.php');
//Vars
$data='';
$output='';
$ID ='';

//Check which method is chosen
if(isset($_SERVER['REQUEST_METHOD'])){
	$output['type'] = $_GET['type'];
	switch($_SERVER['REQUEST_METHOD']){
		case "GET": //TODO: GET METHOD UPGRADE
			if(isset($_GET['page']) && isset($_GET['maker']) && isset($_GET['id'])){
				//Display phone details
				$output['data'] = performGetDetails(strtolower($_GET['page']), strtolower($_GET['maker']),
					$_GET['id'], $link);
			}elseif(isset($_GET['page']) && isset($_GET['id'])){
				switch($_GET['page']){
					case "os":
						$ID = "osID";
						if(isset($_GET['filter'])){
						switch($_GET['filter']){
							case "version": $filter = "osVersion";
											print(performOsFilter($_GET['page'], $_GET['id'],$filter,$link));
								break;
							case "name": $filter = "osName";
								break;
							case "code_name": $filter = "osCodeName";
								break;
							case "company": $filter = "company";
								break;
							default: header("HTTP/1.1 412 Invalid query value", true, 412);
							}
						}else{
							$output['data'] = performGetDetailsID($_GET['page'], $_GET['id'], $link);
						}
						break;
					case "phones":
						$ID = "phoneID";
						if(isset($_GET['query'])){
							switch($_GET['query']){
								case "maker": $filter = "maker";
									break;
								case "model": $filter = "model";
									break;
								case "code_name": $filter = "codeName";
									break;
								case "size": $filter =  "size";
									break;
								case "pixel_density": $filter = "pixelDensity";
									break;
								case "proc_clock": $filter =  "size";
									break;
								case "cores": $filter = "cores";
									break;
								case "proc_name": $filter = "procName";
									break;
								case "os": $filter = "os";
									break;
								case "color": $filter = "color";
									break;
								case "camera": $filter = "camera";
									break;
								case "desc": $filter = "desc";
									break;
								default: header("HTTP/1.1 412 Invalid query value", true, 412);
							}
						}else{
							$output['data'] = performGetDetailsID($_GET['page'], $_GET['id'], $link);
						}
						break;
					default: header("HTTP/1.1 400 Page not found", true, 400);
				}
				if(isset($_GET['query'])){
					$query = "SELECT `0756219`.`".$_GET['page']."`.`$filter` FROM `0756219`.`"
						.$_GET['page']."` WHERE $ID = '".$_GET['id']."'";

					$result = mysqli_fetch_assoc(mysqli_query($link, $query));
					header("Content-Type: text/html");
					print($result[''.$filter.'']);
					print(mysqli_error($link));
				}
			}
			elseif(isset($_GET['page'])){
				//List all phones
				$output['data'] = performGetAllInfo(strtolower($_GET['page']), $link);
			}else{
				header("header(HTTP/1.1 400 No Page found", false, 400);
			}

			if(isset($output['type'])){
				switch($type = strtolower($_GET['type'])){
					case "xml":
						switch($_GET['page']){
							case "os": convertOsToXml(count($output['data']),$output['data']);
								break;
							case "phones": convertPhonestToXml(count($output['data']),$output['data']);
								break;
						}
						break;
					case "json": isset($output['data'])? convertToJSon($output['data']) :
						header("HTTP/1.1 404 No Data found", true, 404);
						break;
					default: header("HTTP/1.1 415 Not a valid Media Type", true, 415);
				}
			}
			break;

		case "POST":
			if(isset($_POST)){
				$postData = $_POST;
				if(isset($_GET['page'])){
					if(isset($_GET['id']) == false){
						//create a new Resource
						switch($_GET['page']){
							case "os":
								$output = performPostOs($postData, $link);
								break;
							case "phones":
								$output = performPostPhones($postData, $link);
								break;
							default: //Implement INVALID RESOURCE
								header("HTTP/1.1 412 Invalid resource provided", true, 412);
						}
					}else{
						//Implement error BAD REQUEST (NO ID ALLOWED)
						header("HTTP/1.1 400 Bad Request (No ID allowed)", true, 400);
					}
				}else{
					//Implement error no PAGE set
					header("HTTP/1.1 404 Page not found", true, 404);

				}
			}else{
				//Error implementation for wrong REQUEST
				header("HTTP/1.1 409 Conflict in Request", true, 409);
			}
			break;

		case "PUT":
				$putData = json_decode(file_get_contents("php://input"), true);
				//print_r($putData); //Print data received from input fields
				switch ($_GET['page']){
					case "os": performOsPut($_GET['id'], $putData, $link);
						break;
					case "phones":
						performPhonesPut($_GET['page'], $_GET['id'], $putData, $link);
						break;
					default: //Implement error unknown PAGE
						header("HTTP/1.1 404 Page not found",true,404);
				}
			break;

		case "DELETE":
			echo "You wanted to DELETE";
			performDelete($_GET['page'], $_GET['id'], $link);
			break;

		case "OPTIONS": //RETURN SUPPORTED METHODS
			header("Allow: PUT, POST, GET, DELETE, OPTIONS");
			header("HTTP/1.1 200 You're available methods are displayed in the header", false, 200);
			break;

		default: header("HTTP/1.1 405 Unallowed Method", false, 405);
	}
}else{
	header("HTTP/1.1 405 Unallowed Method",true, 405);
}

/**
 * @param $ID
 */
function performDelete($page,$id, $db){
	$query = '';
	/**
	 * EXAMPLE
	 * -----------------------------------------
	 * perform delete action on given ID
	 * "DELETE FROM `0756219`.`phones` WHERE `phones`.`phoneID` = 7"
	 * "DELETE FROM `0756219`.`os` WHERE `os`.`osID` = 7"
	 */

	if($page == "os"){
		$query = "DELETE FROM `0756219`.`os` WHERE `os`.`osID` = $id";
	}elseif($page == "phones"){
		$query = "DELETE FROM `0756219`.`phones` WHERE `phones`.`phoneID` = $id";
	}else{
		header("HTTP/1.1 404 Page not found", true, 404);
	}

	if($result = mysqli_query($db, $query)){
		header("HTTP/1.1 200 Query excecuted succesfully",true,200);
	}else{
		die("Error: ".mysqli_error($db));
	}
}

/**
 * @param $data
 * @param $db
 * @return mixed
 */
function performPostOs($data, $db){
	//vars
	$query = '';
	$osCompany = '';
	$osName = '';
	$osVersion = '';
	$osCodeName = '';
	$error = '';

	//setting vars
	isset ($data['company'])?$osCompany = $data['company'] : $osCompany = null;
	isset ($data['osName'])?$osName = "'".$data['osName']."'" : $osName = null;
	isset ($data['osVersion'])?$osVersion = $data['osVersion']: $osVersion = null;
	isset ($data['osCodeName'])?$osCodeName = $data['osCodeName'] : $osCodeName = null;

	//perform POST on given data (Create new data)
	/**
	 * OS -INSERT:
	 * INSERT INTO `0756219`.`os` (`osID`, `company`, `osName`, `osVersion`, `osCodeName`)
	 * VALUES (NULL, 'wg', 'wgetr', '3', 'gwer');
	 * -------------------------------------------------
	 * PHONES - INSERT
	 * INSERT INTO `0756219`.`phones` (`phoneID`, `maker`, `model`, `codenName`, `size`, `pixelDensity`, `procClock`, `cores`, `procName`, `os`, `color`, `camera`, `image`, `desc`)
	 * VALUES (NULL, 'vrf', 'fvd', 'dcv', '4', '23', '4.11', '2', 'dsrybhryn', '2', 'jioj', '32', '', '');
	 */

	$query = "INSERT INTO `0756219`.`os` (`osID`, `company`, `osName`, `osVersion`, `osCodeName`)
 VALUES (NULL, '$osCompany', '$osName', '$osVersion', '$osCodeName')";

	mysqli_query($db, $query);
	$error = mysqli_error($db);

	if(isset($error)){
		print("Error: ".mysqli_error($db)."Executed query: ".$query);
	}

	return $data;
}

/**
 * @param $data
 * @param $db
 * @return mixed
 */
function performPostPhones($data, $db){
	/**
	 * Example
	 * ----------------------------------------------
	 * PHONES - INSERT
	 * INSERT INTO `0756219`.`phones` (`phoneID`, `maker`, `model`, `codenName`, `size`, `pixelDensity`, `procClock`, `cores`, `procName`, `os`, `color`, `camera`, `image`, `desc`)
	 * VALUES (NULL, 'vrf', 'fvd', 'dcv', '4', '23', '4.11', '2', 'dsrybhryn', '2', 'jioj', '32', '', '');
	 * ----------------------------------------------
	 */
	//$vars = "";
	$query = "";
	isset ($data['maker'])?$maker = $data['maker'] : $maker = '';
	isset ($data['model'])?$model = $data['model'] : $model = '';
	isset ($data['codeName'])?$codeName = $data['codeName'] : $codeName = '';
	isset ($data['size'])?$size = $data['size'] : $size = '';
	isset ($data['pixelDensity'])?$pixelDensity = $data['pixelDensity'] : $pixelDensity = '';
	isset ($data['procClock'])?$procClock = $data['procClock'] : $procClock = '';
	isset ($data['cores'])?$cores = $data['cores'] : $cores = '';
	isset ($data['procName'])?$procName = $data['procName'] : $procName = '';
	isset ($data['os'])?$os = $data['os'] : $os = '';
	isset ($data['color'])?$color = $data['color'] : $color = '';
	isset ($data['camera'])?$camera = $data['camera'] : $camera = '';
	isset ($data['image'])?$img = $data['image'] : $img = '';
	isset ($data['desc'])?$desc = $data['desc'] : $desc = '';

	$query = "INSERT INTO `0756219`.`phones` (`phoneID`, `maker`, `model`, `codeName`, `size`, `pixelDensity`, `procClock`, `cores`, `procName`, `os`, `color`, `camera`, `image`, `desc`)
	 VALUES (NULL, '$maker', '$model', '$codeName', '$size', '$pixelDensity', '$procClock', '$cores', '$procName',
	'$os', '$color', '$camera', '$img', '$desc')";

	mysqli_query($db, $query);
	$error = mysqli_error($db);
	if(isset($error)){
		print("Error: ".mysqli_error($db)."Executed query: ".$query);
	}
}

/**
 * @param $page
 * @param $id
 * @param $putData
 * @param $db
 * @return mixed
 */
function performOsPut($id, $putData, $db){
	//perform put on given data (Update existing data)
	$error ='';
	$query =
	"UPDATE  `0756219`.`os` SET  `company` = '".$putData['company']."',`osName` = '".$putData['osName']."',
	`osVersion` = '".$putData['osVersion']."', `osCodeName` = '".$putData['osCodeName']."' WHERE  `os`.`osID`='$id'";
	mysqli_query($db, $query);

}

/**
 * @param $page
 * @param $id
 * @param $data
 * @param $db
 */
function performPhonesPut($page, $id, $data, $db){
	/**
	 * perform put on given data (Update existing data)
	 */
	//vars
	$query = '';
	isset ($data[0]['maker'])?$maker = $data[0]['maker'] : $maker = '';
	isset ($data[0]['model'])?$model = $data[0]['model'] : $model = '';
	isset ($data[0]['codeName'])?$codeName = $data[0]['codeName'] : $codeName = '';
	isset ($data[0]['size'])?$size = $data[0]['size'] : $size = '';
	isset ($data[0]['pixelDensity'])?$pixelDensity = $data[0]['pixelDensity'] : $pixelDensity = '';
	isset ($data[0]['procClock'])?$procClock = $data[0]['procClock'] : $procClock = '';
	isset ($data[0]['cores'])?$cores = $data[0]['cores'] : $cores = '';
	isset ($data[0]['procName'])?$procName = $data[0]['procName'] : $procName = '';
	isset ($data[0]['os'])?$os = $data[0]['os'] : $os = '';
	isset ($data[0]['color'])?$color = $data[0]['color'] : $color = '';
	isset ($data[0]['camera'])?$camera = $data[0]['camera'] : $camera = '';
	isset ($data[0]['image'])?$img = $data[0]['image'] : $img = '';
	isset ($data[0]['desc'])?$desc = $data[0]['desc'] : $desc = '';
	$query =
		"UPDATE  `0756219`.`$page` SET  `maker`='$maker', `model` = '$model', `codeName` = '$codeName',
`size` = '$size', `pixelDensity` = '$pixelDensity', `procClock` = '$procClock', `cores` = '$cores',
`procName` = '$procName', `os` = '$os', `color` = '$color', `camera` = '$camera', `image` = '$img',
`desc` = '$desc' WHERE `phones`.`phoneID`=".$id;

	mysqli_query($db, $query);
//print_r($data);
	$error = mysqli_error($db);
	if(isset($error)){
		print_r("Error: ".mysqli_error($db)." \nQuery: ".$query);
	}
}

/**
 * @param $page
 * @param $id
 * @return mixed
 */
function createLinkSection($page,$id){
	$link['rel'] = "self";
	switch($_SERVER['HTTP_HOST']){
		case 'localhost':
			$link['href'] = "http://localhost/webservices/eindopdracht/$page/$id";
			break;
		default:
			$link['href'] = "http://".$_SERVER['HTTP_HOST']."/0756219/webservices/eindopdracht/$page/$id";
			break;
	}
	return $link;
}
/**
 * Converters SECTION
 * ------------------------
 * Based on the type requested one of these functions fire up.
 *
 * Input: (assoc) Array
 *
 ******************************************
 * @param $data
 * @return string
 */
function convertToJSon($data){
	header('Content-type: application/json');
	$jsonData= json_encode($data);
	print_r($jsonData);
}

/**
 * @param $data
 * ---------------
 * Input: (Assoc)Array
 *
 * Convert the array to XML-format
 *TODO: Implement XML Coversion OS
 */
function convertOsToXml($totaal, $data){
	header('Content-type: text/xml');
	//xml maken uit een php
	$doc = new DomDocument('1.0', 'utf-8');
	if($totaal>0){
		//Create root element
		$root_element = $doc->createElement('operating_systems');
		$doc->appendChild($root_element);
	}

	for($t=0;$t<$totaal; $t++){
	//Create os element
	$os_element = $doc->createElement('os');
	$root_element->appendChild($os_element);

	//Create id attribute
	$os_element->setAttribute('id', $data[$t]['osID']);

	//Create company element and assign value to company
	$companyElement = $doc->createElement('company');
	$os_element->appendChild($companyElement);

	$company_value = $doc->createTextNode($data[$t]['company']);
	$companyElement->appendChild($company_value);

	//Create osName element and assign value to osName
	$osNameElement = $doc->createElement('osName');
	$os_element->appendChild($osNameElement);

	$osName_value = $doc->createTextNode($data[$t]['osName']);
	$osNameElement->appendChild($osName_value);

	//Create osVersion element and assign value to osVersion
	$osVersionElement = $doc->createElement('version');
	$os_element->appendChild($osVersionElement);

	$version_value = $doc->createTextNode($data[$t]['osVersion']);
	$osVersionElement->appendChild($version_value);

	//Create osCodeName element and assign value to osCodeName
	$osCodeNameElement = $doc->createElement('code_name');
	$os_element->appendChild($osCodeNameElement);

	$osCodeName_value = $doc->createTextNode($data[$t]['osCodeName']);
	$osCodeNameElement->appendChild($osCodeName_value);

	//Create osLinkElement element
	$osLinkElement = $doc->createElement('link');
	$os_element->appendChild($osLinkElement);

	//Create osLinkRelElement element and assign value to osCodeName
	$osLinkRelElement = $doc->createElement('rel');
	$osLinkElement->appendChild($osLinkRelElement);

	$osLinkRel_value = $doc->createTextNode($data[$t]['link']['rel']);
	$osLinkRelElement->appendChild($osLinkRel_value);

	//Create osLinkRelElement element and assign value to osCodeName
	$osLinkHrefElement = $doc->createElement('href');
	$osLinkElement->appendChild($osLinkHrefElement);

	$osLinkHref_value = $doc->createTextNode($data[$t]['link']['href']);
	$osLinkHrefElement->appendChild($osLinkHref_value);
	}

	echo $doc->saveXML();
}

/**
 * @param $page
 * @param $data
 *
 * Implement XML Coversion Phones
 */
function convertPhonestToXml($totaal, $data){
	header('Content-type: text/xml');
	//Create xml from php
	$doc = new DomDocument('1.0', 'utf-8');
	if($totaal>0){
		//Create root element
		$root_element = $doc->createElement('phones_gallery');
		$doc->appendChild($root_element);
	}

	for($t=0;$t<$totaal; $t++){
	//phones element
	$phones_element = $doc->createElement('phones');
	$root_element->appendChild($phones_element);

	//Create id attribute
	$phones_element->setAttribute('id', $data[$t]['phoneID']);

	//Create maker node and assign maker value
	$makerElement = $doc->createElement('maker');
	$phones_element->appendChild($makerElement);

	$maker_value = $doc->createTextNode($data[$t]['maker']);
	$makerElement->appendChild($maker_value);

	//Create model node and assign mode value
	$modelElement = $doc->createElement('model');
	$phones_element->appendChild($modelElement);

	$model_value = $doc->createTextNode($data[$t]['model']);
	$modelElement->appendChild($model_value);

	//Create code name node and assign code name value
	$codeNameElement = $doc->createElement('code_name');
	$phones_element->appendChild($codeNameElement);

	$codeName_value = $doc->createTextNode($data[$t]['codeName']);
	$codeNameElement->appendChild($codeName_value);

	//Create size node and assign size value
	$sizeElement = $doc->createElement('size');
	$phones_element->appendChild($sizeElement);

	$size_value = $doc->createTextNode($data[$t]['size']);
	$sizeElement->appendChild($size_value);

	//Create pixelDensity node and assign pixelDensity value
	$pixelDensityElement = $doc->createElement('pixel_density');
	$phones_element->appendChild($pixelDensityElement);

	$pixelDensity_value = $doc->createTextNode($data[$t]['pixelDensity']);
	$pixelDensityElement->appendChild($pixelDensity_value);

	//Create procClock node and assign procClock value
	$procClockElement = $doc->createElement('proc_clock');
	$phones_element->appendChild($procClockElement);

	$procClock_value = $doc->createTextNode($data[$t]['procClock']);
	$procClockElement->appendChild($procClock_value);

	//Create cores node and assign cores value
	$coresElement = $doc->createElement('cores');
	$phones_element->appendChild($coresElement);

	$cores_value = $doc->createTextNode($data[$t]['cores']);
	$coresElement->appendChild($cores_value);

	//Create procName node and assign procName value
	$procNameElement = $doc->createElement('proc_name');
	$phones_element->appendChild($procNameElement);

	$procName_value = $doc->createTextNode($data[$t]['procName']);
	$procNameElement->appendChild($procName_value);

	//Create os node and assign os value
	$osElement = $doc->createElement('os');
	$phones_element->appendChild($osElement);

	$os_value = $doc->createTextNode($data[$t]['os']);
	$osElement->appendChild($os_value);

	//Create color node and assign color value
	$colorElement = $doc->createElement('color');
	$phones_element->appendChild($colorElement);

	$color_value = $doc->createTextNode($data[0]['color']);
	$colorElement->appendChild($color_value);

	//Create camera node and assign camera value
	$cameraElement = $doc->createElement('camera');
	$phones_element->appendChild($cameraElement);

	$camera_value = $doc->createTextNode($data[$t]['camera']);
	$cameraElement->appendChild($camera_value);

	//Create image node and assign image value
	$imageElement = $doc->createElement('image');
	$phones_element->appendChild($imageElement);

	$image_value = $doc->createTextNode($data[$t]['image']);
	$imageElement->appendChild($image_value);

	//Create desc node and assign desc value
	$descElement = $doc->createElement('description');
	$phones_element->appendChild($descElement);

	$desc_value = $doc->createTextNode($data[$t]['desc']);
	$descElement->appendChild($desc_value);

	//Create link node
	$linkElement = $doc->createElement('link');
	$phones_element->appendChild($linkElement);

	//Create rel node and assign rel value
	$relElement = $doc->createElement('rel');
	$linkElement->appendChild($relElement);

	$rel_value = $doc->createTextNode($data[$t]['link']['rel']);
	$relElement->appendChild($rel_value);

	//Create href node and assign href value
	$hrefElement = $doc->createElement('href');
	$linkElement->appendChild($hrefElement);

	$href_value = $doc->createTextNode($data[$t]['link']['href']);
	$hrefElement->appendChild($href_value);
	}

	echo $doc->saveXML();
}

/**
 * @param $page
 * @param $id
 * @param $db
 * @return array|null
 */
function performGetDetailsID($page, $id, $db){
	//vars
	$query = '';
	$data='';

	switch($page){
		case 'os':
			$query = "SELECT `0756219`.`$page`.* FROM `0756219`.`$page` WHERE `osID` = $id";
			break;
		case 'phones':
			$query = "SELECT `0756219`.`$page`.* FROM `0756219`.`$page` WHERE `phoneID` = $id";
			break;
		default: header("HTTP/1.1 404 Resource Not Found",true, 404);
	}

	if($result = mysqli_query($db, $query)){

		if(mysqli_num_rows($result)){
			$phones = mysqli_fetch_assoc($result);
			$phones['link'] = createLinkSection($page, $id);
			$data[] = $phones;
		}
		else{
			header("HTTP/1.1 404 Nothing found", true, 405);
			}
		}else{
			die('Errant query:  '.mysqli_error($db));
		}
	return $data;
	}

/**
 * @param $page
 * @param $maker
 * @param $deviceID
 * @param $db
 * @return array|null
 */
function performGetDetails($page, $maker, $deviceID, $db){
	$query = '';
	//query DB
	switch($page){
		case "os":
			$query = "Select `0756219`.`$page`.* from `0756219`.`$page` WHERE  `osID` = '$deviceID' AND `company` = '$maker'";
			break;
		case "phones":
			$query = "Select `0756219`.`$page`.* from `0756219`.`$page` WHERE  `phoneID` = '$deviceID' AND `maker` = '$maker'";
			break;

	}

	//TODO: Error handeling for no results returned
	if($result = mysqli_query($db, $query)){
		$phones = mysqli_fetch_assoc($result);
		$phones['link'] = createLinkSection($page, $deviceID);
		$data = $phones;
	}else{
		die('Errant query:  '.mysqli_error($db));
	}

	return $data;
}

/**
 * @param $page
 * @param $maker
 * @param $db
 * @return array|null
 */
function performGetMakers($page, $maker, $db){
	//query DB
	$query = "Select `0756219`.`$page`.* from `0756219`.`phones` WHERE `maker` like '%$maker%'";
	$result = mysqli_query($db, $query)or die('Errant query:  '.$query);

	while ($maker = mysqli_fetch_assoc($result)){
		$makers[] = $maker;
	}
	$data = $maker;
	return $data;
}

/**
 * @param $page
 * @param $db
 * @return array
 */
function performGetAllInfo($page, $db){
	$phones = array();
	//query DB
	$query = "Select `0756219`.`$page`.* from `0756219`.`$page`";
	$result = mysqli_query($db, $query)or die('Errant query:  '.$query);

	while($phone = mysqli_fetch_assoc($result)) {
			if($page == "os"){
				$phone['link'] = createLinkSection($page, $phone['osID']);
			}
			elseif($page == "phones"){
				$phone['link'] = createLinkSection($page, $phone['phoneID']);
			}
		$phones[] = $phone;
	}
	$data = $phones;
	return $data;
}

/**
 * @param $code
 */
function statuscode($code){
	switch($code){
		case '200' : "OK";
			break;
		case '201' : "Resource Created!";
			break;
		case '204' : "No result";
			break;
		case '400' : "Invalid Request";
			break;
		case '404' : "Nothing found";
			break;
		case '405' : "Method not supported";
			break;
		case '415' : "Representation not supported";
			break;
		case '500' : "The server fell flat on its face";
			break;
		case '503' : "The service not available";
			break;
		case '502' : "The gateway is misbehaving";
			break;
	}
}
mysqli_close($link);
