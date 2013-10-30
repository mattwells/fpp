<!DOCTYPE html>
<html>
<head>
<?php	include 'common/menuHead.inc'; ?>
<script type="text/javascript" src="js/fpp.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script>
		$(function() {
		$('#tblEventEntries').on('mousedown', 'tr', function(event,ui){
					$('#tblEventEntries tr').removeClass('eventSelectedEntry');
					$(this).addClass('eventSelectedEntry');
					var items = $('#tblEventEntries tr');
					TriggerEventSelected = $(this).find('td:eq(0)').text().replace(' \/ ', '_');
					SetButtonState('#btnTriggerEvent','enable');
					SetButtonState('#btnEditEvent','enable');
					SetButtonState('#btnDeleteEvent','enable');

					if ($('#newEvent').is(":visible"))
							EditEvent();
		});
	});
</script>

<title>Falcon PI Player - Events</title>
</head>
<body onLoad="GetFPPDmode();StatusPopulatePlaylists();setInterval(updateFPPStatus,1000);GetVolume();">
<div id="bodyWrapper">
<?php
	include 'menu.inc';

	$eventIDoptions = "";

	function PrintEventRows()
	{
		global $eventDirectory;
		global $eventIDoptions;

		$usedIDs = array();
		for ($i = 1; $i < 25; $i++)
		{
			$usedIDs[$i] = array();
			for ($j = 1; $j < 25; $j++)
			{
				$usedIDs[$i][$j] = 0;
			}
		}

		foreach (scandir($eventDirectory) as $eventFile)
		{
			if($eventFile != '.' && $eventFile != '..' && preg_match('/.fevt$/', $eventFile))
			{
				$info = parse_ini_file($eventDirectory . "/" . $eventFile);

		# probably can clean this up a bit
				$eventFile = preg_replace('/^0/', '', $eventFile);
				$eventFile = preg_replace('/_0/', '_', $eventFile);
				$eventFile = preg_replace('/.fevt$/', '', $eventFile);

				$info['effect'] = preg_replace('/.eseq$/', '', $info['effect']);

				echo "<tr id='event_" . $eventFile . "'><td class='eventTblID'>" .
						$info['majorID'] . ' / ' . $info['minorID'] .
						"</td><td class='eventTblName'>" . $info['name'] .
						"</td><td class='eventTblEffect'>" . $info['effect'] .
						"</td><td class='eventTblStartCh'>" . $info['startChannel'] .
						"</td><td class='eventTblScript'>" . $info['script'] . "</td></tr>\n";

				$usedIDs[$info['majorID']][$info['minorID']] = 1;
			}
		}

		$eventIDoptions = "";
		for ($i = 1; $i < 25; $i++)
		{
			for ($j = 1; $j < 25; $j++)
			{
				if ($usedIDs["$i"]["$j"] == 0)
				{
					$eventIDoptions .= sprintf("<option value='" .
						sprintf( "%d_%d", $i, $j) .
						"'>%d / %d</option>\n", $i, $j);
				}
			}
		}
	}

	function PrintEventIDoptions()
	{
		global $eventIDoptions;

		echo $eventIDoptions;
	}

	function PrintEffectOptions()
	{
		global $effectDirectory;

		foreach(scandir($effectDirectory) as $seqFile)
		{
			if($seqFile != '.' && $seqFile != '..' && preg_match('/.eseq$/', $seqFile))
			{
				$seqFile = preg_replace('/.eseq$/', '', $seqFile);
				
				echo "<option value='" . $seqFile . "'>" . $seqFile . "</option>\n";
			}
		}
	}

	?>
<br/>
<div id="programControl" class="settings">
	<fieldset>
		<legend>Program Control</legend>
		<div id="daemonControl">
			<table width= "100%">
				<tr>
					<td class='controlHeader'> FPPD Mode: </td>
					<td><div id='textFPPDmode'>Player Mode</div>
				</tr>
				<tr>
					<td class='controlHeader'> FPPD Status: </td>
					<td id = "daemonStatus"></td>
				</tr>
				<tr>
					<td class='controlHeader'> FPP Time: </td>
					<td id="fppTime"></td>
				</tr>
			</table>
		</div>
		<hr>
		<div id="bytesTransferred"><H3>Bytes Transferred</H3>
		<div id="bridgeStatistics1"></div>
		<div id="bridgeStatistics2"></div>
		<div class="clear"></div>
		</div>
		<div id="playerStatus">
			<table width= "100%">
				<tr>
					<td class='controlHeader'>Player Status: </td>
					<td id="txtPlayerStatus"></td>
				</tr>
			</table>
			<table width= "100%">
				<tr>
					<td id="txtTimePlayed"></td>
					<td id="txtTimeRemaining"></td>
				</tr>
			</table>
		</div>
		<div id="playerControls" style="margin-top:5px">
			<input id= "btnPlay" type="button" class ="buttons"value="Start a Playlist" onClick="location.href = '/index.php';">
			<input id= "btnStopGracefully" type="button" class ="buttons"value="Stop Gracefully" onClick="StopGracefully();">
			<input id= "btnStopNow" type="button" class ="buttons" value="Stop Now" onClick="StopNow();">
		</div>
	</fieldset>

	<br />

	<fieldset>
		<legend>Events</legend>
		<div>
			<div id="eventList" class="unselectable">
				<table id="tblEventListHeader" width="100%">
					<tr class="eventListHeader">
						<td class='eventTblID'>ID</td>
						<td class='eventTblName'>Name</td>
						<td class='eventTblEffect'>Effect</td>
						<td class='eventTblStartCh'>Start Ch.</td>
						<td class='eventTblScript'>Script</td>
					</tr>
				</table>
				<div id= "eventListContents">
				<table id="tblEventEntries" width="100%">
<? PrintEventRows(); ?>
				 </table>
			</div>
			</div>

			<div id="eventControls" style="margin-top:5px">
				<input id= "btnAddEvent" type="button" class ="buttons desktopItem" value="Add Event" onClick="AddEvent();">
				<input id= "btnTriggerEvent" type="button" class ="disableButtons" value="Trigger Event" onClick="TriggerEvent();">
				<input id= "btnEditEvent" type="button" class ="disableButtons desktopItem" value="Edit Event" onClick="EditEvent();">
				<input id= "btnDeleteEvent" type="button" class ="disableButtons desktopItem" value="Delete Event" onClick="DeleteEvent();">
			 </div>
			<div id="newEvent" style="display: none;">
				<hr>
				<table width="100%">
					<tr>
						<td><b><center>Event Editor</center></b></td>
					</tr>
				</table>
				<table width="100%">
					<tr><td width="20%">Event ID (Major/Minor):</td><td width="80%"><select id='newEventID'><? PrintEventIDoptions(); ?></select></td></tr>
					<tr><td width="20%">Event Name:</td><td width="80%"><input id="newEventName" class="default-value" type="text" value="" size="30" maxlength="60" /></td></tr>
					<tr><td width="20%">Effect Sequence:</td><td width="80%"><select id="newEventEffect">
							<option value=''>--- NONE ---</option>
<? PrintEffectOptions(); ?>
</select></td></tr>
					<tr><td width="20%">Effect Start Channel:</td><td width="80%"><input id="newEventStartChannel" class="default-value" type="text" value="" size="5" maxlength="5" /></td></tr>
					<tr><td width="20%">Event Script:</td><td width="80%"><input id="newEventScript" class="default-value" type="text" value="" size="60" maxlength="1024" /></td></tr>
				</table>
				<input id= "btnSaveNewEvent" type="button" class ="buttons" value="Save Event" onClick="SaveEvent();">
				<input id= "btnCancelNewEvent" type="button" class ="buttons" value="Cancel Edit" onClick="CancelNewEvent();">
			</div>
		</div>
	</fieldset>
</div>
<?php	include 'common/footer.inc'; ?>
</body>
</html>
