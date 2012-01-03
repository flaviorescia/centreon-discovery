<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
/*
 * {DESCRIPTION}
 *
 * PHP version 5
 *
 * @package {PACKAGE_NAME}
 * @version $Id: 0.6
 * @copyright (c) 2007-2009 Centreon
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
require_once './modules/Discovery/include/DB-Func.php';
require_once './modules/Discovery/include/common.php';
 ?>

<html lang="fr">
	<head>
		<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
		<title>Discovery</title>
		<link href="./Themes/Centreon-2/style.css" />
		<meta content="Service Auto Discovery - Subnet Declaration Page" name="Loic JEZEQUEL & Alexis DONADIO">
		<!-- Fonctions JavaScript -->
        <script type="text/javascript" src="./modules/Discovery/pages/JS-Func.js"></script>
		<script type="text/javascript" src="./modules/Discovery/include/jquery-1.6.4.min.js"></script>
		<script type="text/javascript" src="./modules/Discovery/include/script.js"></script>
			
	</head>
	<body style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); height: 158px;" alink="#ff6600" link="#ff6600" vlink="#ff6600">  
		<span class="" style="font-family: Candara;  font-weight: bold;">
			<br>
			<?php

			if (!isset ($oreon)) {
				exit ();
			}
			?>
			
			
			<br>
			<table width="100%">
				<tr>
					<td width="30%"><img alt="CentESIEEon" src="./modules/Discovery/include/images/img_CentrESIEEon.png" /></td>
					<td width="40%"><h1 ALIGN=CENTER>IP range(s) to scan</h1></td>
					<td width="30%"></td>					
				</tr>
			</table>
			<br><br>
			<div id="xmlhttp"></div>

			<?php

        		$submitValue = "Save configuration";
				$resetValue = "Reset to defaults";
				$addValue = "Add";
				$delValue = "del";
				$error=0;
				$res = array();
				$listIp = array();
				$listMask = array();
				$listCidr = array();
				
				/*
				 * {Display input form}
				 *
				 * @param	int	$error	error number
				 * @throws Exception Description
				 * @return	void
				 */
				
				function doInput($error) {
				
					global $addValue, $remove_value;
					echo ' <form method="post">',"\n";
					echo '     <center>',"\n";
					echo '     <table>',"\n";
					echo '         <tr>';
					echo '             <td><h3>New IP range (16 max.): </h3></td>',"\n";
					echo '             <td>&nbsp;<input type="text" name="net" value=""></td>',"\n";
					echo '             <td>&nbsp;<input type="submit" title="Add to List" name="submit" value="Add" onClick="self.location=\'main.php?p=61201\'"></td>',"\n";
					echo '         </tr>',"\n";
					echo '         <tr><td>&nbsp;</td></tr>',"\n";
					echo '         <tr>',"\n";
					echo '             <td rawspan=2><center><i>Examples:</i></center></td>',"\n";
					echo '             <td><i>10.14.12.0 &nbsp; 255.255.255.254</i></td>',"\n";
					echo '         </tr>',"\n";
					echo '         <tr>',"\n";
					echo '             <td></td>',"\n";
					echo '             <td><i>192.168.0.0/24</i></td>',"\n";
					echo '         </tr>',"\n";
					echo '         <tr><td>&nbsp;</td></tr>',"\n";
					echo '     </table>',"\n";
					echo '     </center>',"\n";
					echo ' </form>',"\n";
					if ($error==1){
						echo "<CENTER><b><font size=\"3px\" color=\"red\">ERROR</b> : Error in your entry.</font></CENTER>\n";
					}
					if ($error==2){
						echo "<CENTER><b><font size=\"3px\" color=\"red\">ERROR</b> : Subnet already in the list.</font></CENTER>\n";
					}
					if($error==3){
						echo "<CENTER><b><font size=\"3px\" color=\"red\">ERROR</b> : The maximum number of subnets has been reached.</font></CENTER>\n";
					}
				}
				
				/*
				 * {Display array of IP, mask, poller, CIDR mask and options about each entry}
				 *
				 * {Display each input data in an array}
				 *
				 */
				
				function doFormTab(){
					global $res, $error;
					
					//Appel le script python avec le paramètre STATUS_POLLER, cela permet de vérifier si les pollers sont actifs.
					if (file_exists("./modules/Discovery/include/agent/DiscoveryAgent_central.py")) {
						shell_exec('python ./modules/Discovery/include/agent/DiscoveryAgent_central.py STATUS_POLLER > /dev/null 2>&1 &');
						//exec('python ./modules/Discovery/include/agent/DiscoveryAgent_central.py STATUS_POLLER > /dev/null 2>&1 &');
					}
					else { echo 'Script Python not found...<br><br>'; }
					
							
					$i=0;
                    echo ' <form method="post">'."\n";
					echo '    <table class="ListTable">'," \n ";
					echo '         <tr class="ListHeader">'," \n ";
					echo '             <td class="ListColHeaderCenter"><input id="SelectAll" type="checkbox" name="scanall" title="Check if you want to select all ranges" onClick="select_all(\'RangeToScan\');"></td>'," \n ";
					echo '             <td class="ListColHeaderCenter">Network Address</td>'," \n ";
					echo '             <td class="ListColHeaderCenter">Subnet Mask</td>'," \n ";
					echo '             <td class="ListColHeaderCenter">CIDR</td>'," \n ";
					echo '             <td class="ListColHeaderCenter">Poller</td>'," \n ";
					echo '             <td class="ListColHeaderCenter">Status of Discovery-Agent</td>'," \n ";
                    echo '             <td class="ListColHeaderCenter"><input type="submit" title="Delete all from list" name="ClearAll" value=" Clear All " onClick="self.location=\'./main.php?p=61201\'"></td>'," \n";
					echo '             <td class="ListColHeaderCenter">Options</td>'," \n ";
					echo '         </tr>'," \n ";
					echo ' </form>',"\n";
					
					echo '<br><form method="post" action="main.php" onSubmit="return valider_form(this);">',"\n";	
					
					//On récupere les valeurs de la configuration par défaut
					$default_conf = findDefaultConfig();
					
					//On selectionne tous les élèments de la table mod_discovery_rangeip ainsi que le poller correspondant et on affiche
                    $sql = mysql_query("SELECT S.id,plage,masque,cidr,nagios_server_id,name,ns_ip_address FROM mod_discovery_rangeip S, nagios_server N WHERE S.id!=0 AND nagios_server_id=N.id ORDER BY S.id ASC;");
                    while($data= mysql_fetch_array($sql,MYSQL_ASSOC)){
						echo ' <tr class="list_one">'," \n ";
						echo ' <td class="ListColCenter"><input type="checkbox" checked="yes" class="RangeToScan" id="RangeToScan'.$i.'" name="RangeToScan'.$i.'" value="true" title="Check if you want to scan this range"></td>'."\n ";
						echo ' <td class="ListColCenter" onClick="afficher_cacher(\'td_toggle'.$i.'\',\'td_toggle\');">'.$data["plage"].'</td>'."\n ";
						echo ' <td class="ListColCenter" onClick="afficher_cacher(\'td_toggle'.$i.'\',\'td_toggle\');">'.$data["masque"].'</td>'."\n ";
						echo ' <td class="ListColCenter" onClick="afficher_cacher(\'td_toggle'.$i.'\',\'td_toggle\');">/'.$data["cidr"].'</td>'."\n ";
						//echo ' <td class="ListColCenter">'.$data["name"].' ('.$data["ns_ip_address"].')</td>'."\n ";
						echo ' <td class="ListColCenter">'.doDropDownList($data["id"]).'</td>'."\n ";
						echo ' <td class="ListColCenter" onClick="afficher_cacher(\'td_toggle'.$i.'\',\'td_toggle\');"><div id="status'.$i.'" class="status"><p id="'.$data["id"].'" style="display:none"></p><img style="border:none" type="image" src="./modules/Discovery/include/images/loading.gif" title="Loading..."></div></td>'."\n ";
                        echo ' <td class="ListColCenter"><input style="border:none" type="image" src="./modules/Discovery/include/images/delete16x16.png" title="Delete one from list" name="clear'.$data["id"].'" value="'.$data["id"].'" onClick="self.location=\'./main.php?p=61201\'"></td>',"\n ";
						echo ' <td class="ListColCenter" onClick="afficher_cacher(\'td_toggle'.$i.'\',\'td_toggle\');"><img style="border:none" type="image" src="./modules/Discovery/include/images/options.jpg" title="Options"></td></tr>'."\n ";
						echo ' <tr class="list_one" id="tr'.$i.'">'," \n ";
						echo ' 		<td></td>
									<td colspan="6">
										<div style="display:none" id="td_toggle'.$i.'" class="td_toggle">
										<input type="hidden" name="id'.$i.'" value="'.$data["id"].'">
										<table>
											<td>												
												Ping count :<br>
												Wait :<br>
											</td>
											<td>
												<input type="text" name="ping_count'.$i.'" tabindex="2" value="'.$default_conf["ping_count"].'" title="Enter the number of ICMP ECHO packets to send"><br>
												<input type="text" name="ping_wait'.$i.'" tabindex="3" value="'.$default_conf["ping_wait"].'" title="Enter the number of milliseconds to wait for response"><br>
											</td>
											<td>
												SNMP :<br>
												Method :<br>
												Hostname OID :<br>
												OS OID :<br>
												Version :<br>
												Community :<br>
											</td>
											<td><br>'."\n ";
						if ($default_conf["snmp_method"] == "get"){
							echo '				<select name="snmp_method'.$i.'" tabindex="7" title="Choose the SNMP method to use"><option value="get" selected="selected">Get</option><option value="walk">Walk</option></select><br>'."\n ";
						}else{
							echo '				<select name="snmp_method'.$i.'" tabindex="7" title="Choose the SNMP method to use"><option value="get">Get</option><option selected="selected" value="walk">Walk</option></select><br>'."\n ";								
						}
						echo '					<input type="text" name="oid_hostname'.$i.'" tabindex="8" value="'.$default_conf["oid_hostname"].'" title="Exemple: .1.3.6.1.4.1.5518.1.5.47"><br>
												<input type="text" name="oid_os'.$i.'" tabindex="9" value="'.$default_conf["oid_os"].'" title="Exemple: .1.3.6.1.4.1.5518.1.5.47"><br>'."\n ";	
						if ($default_conf["snmp_version"] == "2"){
							echo '				<select name="snmp_version'.$i.'" tabindex="10" title="Choose the SNMP version to use"><option value="1">v1</option><option selected="selected" value="2">v2c</option></select><br>'."\n ";
						}else{
							echo '				<select name="snmp_version'.$i.'" tabindex="10" title="Choose the SNMP version to use"><option selected="selected" value="1">v1</option><option value="2">v2c</option></select><br>'."\n ";								
						}	
						echo '					<input type="text" name="snmp_community'.$i.'" tabindex="11" value="'.$default_conf["snmp_community"].'" title="Enter your SNMP community">
											</td>
										</table>
									</div>
								</td>
							</tr>'."\n ";
						$i++;
					}
					echo '    </table>'," \n ";
					echo ' <br>'," \n ";
					echo ' <p align="center"><input type="hidden" name="p" value="61202"><input type="submit" value="Scan"></p></form>',"\n";					
				}

				
				/*
				 * {convert an IPv4 address to it's binary format}
				 *
				 * @param	string	$ip_addr ip or mask
				 * @return	string $res 
				 */
				 
				function ip2bin ($ip_addr){
					$ip = explode(".",$ip_addr);
					for ($i=0;$i<4;$i++){
						$ip[$i] = decbin($ip[$i]);
						$strlength = strlen($ip[$i]);
						while ($strlength < 8){
							$ip[$i] = substr_replace(($ip[$i]),"0",0,0);
							$strlength++;
						}
						$res.=$ip[$i];
					}
					return $res;
				}	
				
				/*
				 * {compare too IPv4 addresses in binary format, only }
				 *
				 * @param	string	$poller_addr ip address
				 * @param	string 	$plage_addr ip address
				 * @param	int		$cidr 
				 * @return	boolean	0 if strings are identics, else 1 or -1 
				 */		
				 
				function isPoller ($poller_addr,$plage_addr,$cidr){
					$poller_addr = ip2bin($poller_addr);
					$plage_addr = ip2bin($plage_addr);
					return strncasecmp($poller_addr,$plage_addr,$cidr);
				}
				
				/*
				 * {search in database if there a poller for a range of ip address }
				 *
				 * @param	string 	$plage_addr ip address
				 * @param	int		$cidr 
				 * @return	string	Localhost if there isn't poller, name of the poller whith ip address if existing 
				 */	
				 
				function findPoller ($plage_addr,$cidr){
					$sql = mysql_query("SELECT id,name,ns_ip_address FROM nagios_server WHERE ns_activate=1;");					
					while($poller= mysql_fetch_array($sql,MYSQL_ASSOC)){					
						if (isPoller($poller["ns_ip_address"],$plage_addr,$cidr) == 0){
							$result = array ("poller_id" => $poller["id"], "poller_name" => $poller["name"], "poller_ip" => $poller["ns_ip_address"]);	
							return $result;
						}
					}
					$result = array ("poller_id" => "1", "poller_name" => "Localhost", "poller_ip" => "127.0.0.1");
					return $result;
				}
				
				/*
				 * {Find default configuration for a scan}
				 *
				 */
				function findDefaultConfig (){
					$sql = mysql_query("SELECT * FROM mod_discovery_rangeip WHERE id=0;");					
					while ($result = mysql_fetch_array($sql,MYSQL_ASSOC)){			
						return $result;
					}
				}
				
				/*
				 * {Do the drop-down list of poller}
				 *
				 */
				function doDropDownList ($id){
					$sql = mysql_query("SELECT * FROM nagios_server;");
					$sql2 = mysql_query("SELECT nagios_server_id FROM mod_discovery_rangeip WHERE id='$id';");	
					$list='<select id="poller'.$id.'" title="Choose the Discovery-Agent to use">';
					while ($result = mysql_fetch_array($sql,MYSQL_ASSOC)){
						$list.='<option value="'.$result["id"].'"';
						if (mysql_result($sql2,0) == $result["id"]){
							$list.=' selected="selected"';					
						}
						$list.='>'.$result["name"].' ('.$result["ns_ip_address"].')</option>';
					}
					$list.='</select>  <img style="border:none" type="image" src="./modules/Discovery/include/images/check.png" onClick="request(\'./modules/Discovery/include/update.php\',\'xmlhttp\',\'poller'.$id.'\',\''.$id.'\');" title="Check poller status">';
					return $list;
				}
					
				/*
				 * {Validate IPv4 adress format}
				 *
				 * {verify if each Byte is lower than 255, if the address is a group of 4 Bytes separated by a dot}
				 *
				 * @param	string	$ip_addr	IP address or mask
				 * @return	boolean
				 */

				function validateIpAddress($ip_addr) {
					return preg_match("/^(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[1-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])){3}$/",$ip_addr);
				}

                                /*
				 * {Validate IPv4 mask}
				 *
				 * {verify if the mask is an valid IPv4 mask}
				 *
				 * @param	string	$mask	IPv4 mask
				 * @return	boolean
				 */

				function validateMask($mask) {
					return preg_match("/^(((128|192|224|240|248|252|254|255)(\.0){3})|(255\.(128|192|224|240|248|252|254|255)(\.0){2})|((255\.){2}(128|192|224|240|248|252|254|255)\.0)|((255\.){3}(128|192|224|240|248|252|254)))$/",$mask);
				}
				
				/*
				 * {Validate a CIDR mask format}
				 *
				 * {verify if the CIDR mask is a number lower than 32}
				 *
				 * @param	int	$cidr	cidr mask value
				 * @return	boolean
				 */
				
				function validateCidr($cidr) {
					return preg_match("/^([1-9]|[1-2][0-9]|31)$/",$cidr);
				}
				
				/*
				 * {convert an IPv4 mask to it's CIDR format}
				 *
				 * @param	string	$mask mask value
				 * @return	int	CIDR value
				 */
				
				function maskToCidr($mask){
					$long = ip2long($mask);
					$base = ip2long('255.255.255.255');
					return 32-log(($long ^ $base)+1,2);

					/* xor-ing will give you the inverse mask,
					log base 2 of that +1 will return the number
					of bits that are off in the mask and subtracting
					from 32 gets you the cidr notation */
				}
				
				/*
				 * {convert an IPv4 CIDR mask to it's long format}
				 *
				 * @param int	$cidr	cidr value
				 * @throws Exception Description
				 * @return	string	mask value
				 */
				
				function cidrToMask($cidr){
					$mask=array();
					$mask[1]=0;
					$mask[2]=0;
					$mask[3]=0;
					switch($cidr){
						case(($cidr>0)&&($cidr<=8)):
							$mask[0]=maskByte($cidr);
							break;
						case(($cidr>8)&&($cidr<=16)):
							$mask[0]=255;
							$mask[1]=maskByte($cidr-8);
							break;
						case(($cidr>16)&&($cidr<=24)):
							$mask[0]=255;
							$mask[1]=255;
							$mask[2]=maskByte($cidr-16);
							break;
						case(($cidr>24)&&($cidr<=31)):
							$mask[0]=255;
							$mask[1]=255;
							$mask[2]=255;
							$mask[3]=maskByte($cidr-24);
							break;
						default:
							echo "Erreur";
					}
					return $mask[0].'.'.$mask[1].'.'.$mask[2].'.'.$mask[3];
				}

				/*
				 * {DESCRIPTION_COURTE}
				 *
				 * {DESCRIPTION_LONGUE} (s'il y en a besoin)
				 *
				 * <code>
				 * Un exemple d'utilisation si besoin
				 * </code>
				 *
				 * @param{TAB}int{TAB}$argument1{TAB}Mon premier argument
				 * @param{TAB}string{TAB}$argument2{TAB}Mon deuxi�me argument
				 * @return{TAB}int{TAB}Ma valeur de retour
				 */
				
				function maskByte($byte){
					switch($byte){
						case($byte==1):
							return 128;
						case($byte==2):
							return 192;
						case($byte==3):
							return 224;
						case($byte==4):
							return 240;
						case($byte==5):
							return 248;
						case($byte==6):
							return 252;
						case($byte==7):
							return 254;
						case($byte==8):
							return 255;
					}
				}
				
				/*
				 * {Convert an ip address to an subnet address}
				 *
				 * @param	string	$ip	ip address
                 * @param	string	$mask	network mask
				 * @return	string	network address
				 */

				function ip2Subnet($ip,$mask){
					$addr=ip2long($ip);
					$mas=ip2long($mask);
					$naddr=$addr & $mas;
					return long2ip($naddr);
				}

                /*
				 * {Clear one row in mod_discovery_rangeip table}
				 *
				 * @param	int	$n	number of the line to delete
				 * @return	void	work on table mod_discovery_rangeip
				 */

				function clearRow($n){
					$sql=mysql_query("DELETE FROM mod_discovery_rangeip WHERE id = ".$n.";");
				}

				/*
				 * {clear the global array array}
				 *
				 * @return	void	work on global variable
				 */
				
				function clearArray(){
					$sql=mysql_query("DELETE FROM mod_discovery_rangeip WHERE id!=0;");
				}

				/*
				 * {main function}
				 *
				 */
				
				function doPost() {
					global $res, $error, $conf_centreon;
					$db = dbConnect($conf_centreon['hostCentreon'], $conf_centreon['user'], $conf_centreon['password'],$conf_centreon['db'], true);
					/*if (isset($_GET["p"]) && $_GET["p"]==613){
						//clean_sql();
						echo '<script type="text/javascript">';
						echo '    window.location = "main.php?p=61201"';
						echo '</script>';
					}*/

					if(isset($_POST["net"])) {
						$nbPlage=mysql_query("SELECT count(*) FROM mod_discovery_rangeip WHERE id!=0;");
						$nbPlageData=mysql_fetch_array($nbPlage);
						if($nbPlageData[0]<=15){
							$tmp=explode(" ",$_POST["net"]);
							if(isset($tmp[1])) {
								if (validateIpAddress($tmp[0]) && validateMask($tmp[1])){
									$netAddr = ip2Subnet($tmp[0],$tmp[1]);
									if (mysql_num_rows(mysql_query("SELECT * FROM mod_discovery_rangeip WHERE plage='".$netAddr."';")) == 0 ){
										$poller = findPoller($netAddr,maskToCidr($tmp[1]));
										if(!mysql_query("INSERT INTO mod_discovery_rangeip (plage,masque,cidr,nagios_server_id) VALUES('".$netAddr."','".$tmp[1]."','".maskToCidr($tmp[1])."','".$poller["poller_id"]."');")){
											echo mysql_error();
										}
									}
									else {
										$error=2;
									}
								}
								else
									$error=1;
							}
							else {
								$tmp=explode("/",$_POST["net"]);
								if($tmp[1]){
									if (validateIpAddress($tmp[0]) && validateCidr($tmp[1])){
										$netAddr = ip2Subnet($tmp[0],cidrToMask($tmp[1]));
										if (mysql_num_rows(mysql_query("SELECT * FROM mod_discovery_rangeip WHERE plage='".$netAddr."';")) == 0 ){
											$poller = findPoller($netAddr,$tmp[1]);
											if(!mysql_query("INSERT INTO mod_discovery_rangeip (plage,masque,cidr,nagios_server_id) VALUES('".$netAddr."','".cidrToMask($tmp[1])."','".$tmp[1]."','".$poller["poller_id"]."');")){
												echo mysql_error();
											}
										}
										else {
											$error=2;
										}
									}
									else
										$error=1;
								}
								else{
									$error=1;
								}
							}
						}
						else{
							$error=3;
						}
						unset($_POST);
					}
					
					if (isset($_POST["ClearAll"])){
						if ($_POST["ClearAll"]==" Clear All ") {
							clearArray();
						}
					}

					if (!empty($_POST)){
						$subnetIDList=mysql_query("SELECT id FROM mod_discovery_rangeip WHERE id!=0;");
						while ($subnetIDListData=mysql_fetch_array($subnetIDList,MYSQL_ASSOC)) {
							$id=$subnetIDListData["id"];
							$postVar=$id."_x";
							if (isset($_POST[$postVar]) || isset($_POST[$id])){
								clearRow($id);
							}
						}
						unset($_POST);
					}

					doInput($error);
					doFormTab($error);
					dbClose($db);
					}
				doPost();
				/*
				MODIFICATIONS A APPORTER:
				- Corriger le probleme de determination du poller
				*/
			?>
        </span>
    </body>
</html>

