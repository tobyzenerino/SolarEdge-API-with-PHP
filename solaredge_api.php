<?php
//SolarEdge API Credentials
$solaredge_APIkey = "";
$solaredge_ID = "";

//////////////////////////
//   Auto Unit Prefix   //
//////////////////////////
//Calculates the Watt to Kilo Watt etc.
function unit_prefix($number){ 
	$numlenght = strlen(intval($number));
	$unit = "";
	if($numlenght <= 3) {
		$number = round($number,2);	
		$unit = "";
	} elseif ($numlenght <= 6) { // Kilo
		$number = round($number/1000,2);
		$unit = "k";
	} elseif ($numlenght <= 9) { // Mega
		$number = round($number/1000000,2);
		$unit = "M";
	} elseif ($numlenght <= 12) { // Giga
		$number = round($number/1000000000,2);
		$unit = "G";
	} elseif ($numlenght <= 15) { // Tera
		$number = round($number/1000000000000,2);
		$unit = "T";
	} elseif ($numlenght <= 18) { // Peta
		$number = round($number/1000000000000,2);
		$unit = "P";
	} else {
		$unit = "";
	}
	return array($number,$unit);
}

//////////////////////////
//  Current Power Flow  //
//////////////////////////
$content = file_get_contents("https://monitoringapi.solaredge.com/site/".$solaredge_ID."/currentPowerFlow?api_key=".$solaredge_APIkey );
$json=json_decode($content);

// Variables for Current Power Flow
$export_w = ($json->siteCurrentPowerFlow->GRID->currentPower)*1000;
$consumption_w = ($json->siteCurrentPowerFlow->LOAD->currentPower)*1000;
$pv_prod_now_w = ($json->siteCurrentPowerFlow->PV->currentPower)*1000;


//////////////////////////
//       Overview       //
//////////////////////////
$content = file_get_contents("https://monitoringapi.solaredge.com/site/".$solaredge_ID."/overview?api_key=".$solaredge_APIkey );
$json=json_decode($content);

//Variables for Overview
$last_update = $json->overview->lastUpdateTime;
$last_update_date = date("d.m.Y", strtotime($last_update)); 
$last_update_month = date("F Y", strtotime($last_update)); 
$last_update_year = date("Y", strtotime($last_update)); 
$pv_lifetime_wh = $json->overview->lifeTimeData->energy;
$pv_prod_year_wh = $json->overview->lastYearData->energy;
$pv_prod_month_wh = $json->overview->lastMonthData->energy;
$pv_prod_day_wh = $json->overview->lastDayData->energy;


//////////////////////////
//       Inventory			//
//////////////////////////
//Variables for Inventory
$content = file_get_contents("https://monitoringapi.solaredge.com/site/".$solaredge_ID."/inventory?api_key=".$solaredge_APIkey );
$json=json_decode($content);

$inverter=$json->Inventory->inverters;
$invvendor = $inverter[0]->manufacturer;
$invmodel = $inverter[0]->model;
$qtyoptimizers = $inverter[0]->connectedOptimizers;

//////////////////////////
//  Environment Data    //
//////////////////////////
//Variables for Environment data
$content = file_get_contents("https://monitoringapi.solaredge.com/site/".$solaredge_ID."/envBenefits?api_key=".$solaredge_APIkey );
$json=json_decode($content);

$co2=round($json->envBenefits->gasEmissionSaved->co2,1);
$so2=round($json->envBenefits->gasEmissionSaved->so2,1);
$nox=round($json->envBenefits->gasEmissionSaved->nox,1);
$treesplanted = round($json->envBenefits->treesPlanted);
$lightbulbs = $json->envBenefits->lightBulbs;

?>

<h1>Solar Edge Production</h1>
<p>SolarEdge values are updated every 15 minutes.
<table border="1">
	<tr>
	 <td bgcolor='#EDEFF7' colspan='3'><h3>Current Values</h3></td>	
	</tr>
	<tr>
		<td>Production</td>
		<td colspan='2'><?php echo unit_prefix($pv_prod_now_w)[0]." ".unit_prefix($pv_prod_now_w)[1]."W"; ?></td>
	</tr>
	<tr>
		<td>Export to grid</td>
		<td colspan='2'><?php echo unit_prefix($export_w)[0]." ".unit_prefix($export_w)[1]."W"; ?></td>
	</tr>
	<tr>
		<td>Consumption</td>
		<td colspan='2'><?php echo unit_prefix($consumption_w)[0]." ".unit_prefix($consumption_w)[1]."W"; ?></td>
	</tr>
	<tr>
		<td>From Grid</td>
		<td colspan='2'></td>
	</tr>
	<tr>
		<td  bgcolor='#EDEFF7' colspan="3"><h3>Production</h3></td>
	</tr>
	<tr>
		<td>Today</td>
		<td><?php echo unit_prefix($pv_prod_day_wh)[0]." ".unit_prefix($pv_prod_day_wh)[1]."Wh"; ?></td>
		<td><?php echo $last_update_date ?></td>
	</tr>
	<tr>
		<td>Month</td>
		<td><?php echo unit_prefix($pv_prod_month_wh)[0]." ".unit_prefix($pv_prod_month_wh)[1]."Wh"; ?></td>
		<td><?php echo $last_update_month ?></td>
	</tr>
	<tr>
		<td>Year</td>
		<td><?php echo unit_prefix($pv_prod_year_wh)[0]." ".unit_prefix($pv_prod_year_wh)[1]."Wh" ?></td>
		<td><?php echo $last_update_year ?></td>
	</tr>
	<tr>
		<td>Total</td>
		<td colspan='2'><?php echo unit_prefix($pv_lifetime_wh)[0]." ".unit_prefix($pv_lifetime_wh)[1]."Wh" ?></td>
	</tr>
	<tr>
		<td  bgcolor='#EDEFF7' colspan="3"><h3>Inventory and Equipment</h3></td>
	</tr>
	<tr>
		<td>Inverter Manufacturer</td>
		<td colspan='2'><?php echo $invvendor ?></td>
	</tr>
	<tr>
		<td>Inverter Model</td>
		<td colspan='2'><?php echo $invmodel ?></td>
	</tr>
	<tr>
		<td>Quantity Optimizer</td>
		<td colspan='2'><?php echo $qtyoptimizers ?></td>
  </tr>
	<tr>
	<td bgcolor='#EDEFF7' colspan="3"><h3>Environment</h3></td>
	</tr>
	<tr>
		<td>CO2-Emissions</td>
		<td><?php echo $co2 ?> kg</td>
		<td>Carbon dioxide</td> 
	</tr>
	<tr>
		<td>SO2-Emissionen</td>
		<td><?php echo $so2 ?> kg</td>
		<td>Sulfur dioxide</td> 
	</tr>
	<tr>
		<td>NOX-Emissions</td>
		<td><?php echo $nox ?> kg</td>
		<td>Nitrogen oxides</td> 
	</tr>
	<tr>
		<td>Trees planted</td>
		<td><?php echo $treesplanted ?></td>
		<td>Equivalent</td> 
	</tr>
</table>
