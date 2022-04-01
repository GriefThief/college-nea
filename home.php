<?php
	session_start(); // Resume the session that was created when the user first logged in

	require_once("config.php");

	if (!isset($_SESSION["auth"]) || $_SESSION["auth"] !== true) { // check if the auth variable has been set to false or is unset
		header("location: index.php"); // redirect to login page if not authorised
		exit; // terminate any scripts on this page
	}

	$page = $_SERVER['PHP_SELF'];
	$sec = "300";

	//getStudents is used to get an array containing all the records from the Students database
	//an input of the database connection is taken
	//the output is the array containing all student records
	//the only precondition is that the database connection is not null
	function getStudents($db) {
		// form SQL statement to get all records from the database
		mysqli_refresh($db, MYSQLI_REFRESH_SLAVE);
		$sql = "SELECT Surname, Forename, Tutor_Group, Status, Last_Scan FROM Students ORDER BY Surname ASC";
		$result = mysqli_query($db, $sql); // query the database
		$fin = array(); // create an empty array
		while ($row = mysqli_fetch_assoc($result)) { // for each row in the result
			// create a temporary array so that it can be added to $fin
			$temp = array($row["Surname"], $row["Forename"], $row["Tutor_Group"], $row["Status"], $row["Last_Scan"]);
			array_push($fin, $temp); // append $temp to $fin
		}
		return $fin; // return
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") { // on post request
		$tutorGroup = $_POST['tutorSelect']; // set variable tutorGroup to the value in the element tutorSelect
		if ($tutorGroup != "Please select") { // if an option other than the default has been selected
			header("location: tutorTable.php?tutor=" . $tutorGroup); // redirect to tutorTable.php with the tutor as an ID
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
		<link rel="stylesheet" href="css/styles.css"/>
		<meta charset="UTF-8">
		<meta name="description" content="Student database access">
		<meta name="keywords" content="database, fire, students">
		<meta name="author" content="Harry LS">
		<meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
		<script>
			//getTime is a function used to update a HTML element with the ID 'clock' every second so that it shows the current date and time in the UTC format
			//no input is taken
			//the output is updating the HTML element with the date and time
			//the precondition for this is that there is a HTML element with the ID 'clock' that exists
			function getTime() {
				const date = Date.now(); //set the const date to the current date
				const today = new Date(date); //format the date variable and assign it to the const today
				document.getElementById("clock").innerHTML = today.toUTCString(); //find the element with the id 'clock' and update its text to the const 'today'
				setTimeout(getTime, 1000); //set the timout to 1000 ms so that this function runs every second
			}

			//formatEpoch is a function used to translate a unix epoch value into the format dd/MM/yyyy
			//an input of the epoch is taken
			//the output is the formatted date
			//the precondition for this is that the value given is an epoch
			function formatEpoch(epoch) {  // define the function
				const date = new Date(epoch*1000);
				let day = "0" + date.getUTCDate().toString(); // get the day from date
				let month = "0" + (date.getUTCMonth() + 1).toString(); // get the month from date
				let year = date.getUTCFullYear().toString(); // get the year from date
				let hour = "0" + date.getUTCHours(); // get the hour from date
				let minute = "0" + date.getUTCMinutes(); // get the minute from date
				return day.substr(-2) + "/" + month.substr(-2) + "/" + year + " " + hour.substr(-2) + ":" + minute.substr(-2); // return the date
			}

			// formatDate is used to translate a dd/MM/yyyy hh:mm date to a seconds epoch
			// it takes the date as an input
			// it outputs the seconds epoch
			// the precondition is that date is in a valid format
			function formatDate(date) {
				let fd = date.split(" "); // split the date into an array on the space
				let d = fd[0].split("/"); // split the date into an array on the /
				let t = fd[1].split(":"); // split the time into an array on the :
				let epoch = new Date(d[2], d[1] - 1, d[0], t[0], t[1], 0) / 1000; // calculate the epoch in seconds
				return epoch; // return the epoch
			}

			// clearTable is used to empty the table of all data
			// it takes the table to clear as an input
			// it has no output
			// the precondition is that the table exists
			function clearTable(table) {
				let rowLen = table.rows.length; // variable rowlen is how many rows are in the table
				for (let i = rowLen - 1; i >= 0; i--) { // iterate through each row
					table.deleteRow(i); // delete the row
				}
			}
			
			//populateTable is used to fill the table with the student records
			//it takes one input which is the 2d array containing student records
			//the output is adding the data to the table
			//there are two preconditons:
			//1) either the records or null must be passed
			//2) this function MUST be run AFTER the table has been created
			//if it is run before, then there is no table for it to populate
			function populateTable(records) {
				let table = document.getElementById("studentTableBody");
				clearTable(table);
				for (let i = 0; i < records.length; i++) { // iterate over arr
					let row = table.insertRow(); // inserts a new row
					let sur = row.insertCell(0); // inserts a new cell
					sur.innerHTML = records[i][0]; // sets text of new cell to records[i][0]
					let fore = row.insertCell(1); // repeat
					fore.innerHTML = records[i][1]; // repeat
					let tut = row.insertCell(2); // repeat
					tut.innerHTML = records[i][2]; // repeat
					let sta = row.insertCell(3); // repeat
					switch (records[i][3].toString()) { // switch on expression records[i][3]
						case "0": // if it's 0
							sta.innerHTML = "Out"; // set cell text to Out
							break; // end case
						
						case "Out":
							sta.innerHTML = "Out"; // set cell text to Out
							break; // end case

						case "1": // if it's 1
							sta.innerHTML = "In"; // set cell text to In
							break; // end case

						case "In": // if it's 1
							sta.innerHTML = "In"; // set cell text to In
							break; // end case
						
						default: // if neither apply
							sta.innerHTML = "Oh no!"; // set cell text to Oh no!
					}
					let las = row.insertCell(4); // repeat
					if (!isNaN(records[i][4])) {
						las.innerHTML = formatEpoch(records[i][4]); // set text to formatted epoch
					} else {
						las.innerHTML = records[i][4];
					}
				}
			}

			// mergeSort is used to merge sort the data from the table on the given header
			// it takes two inputs, the array to be sorted and the header to sort on
			// it returns the sorted array
			// there are no preconditions
			function mergeSort(dir, items, header) {
				if (items.length > 1) { // exit condition for recursion
					let mid = items.length / 2; // find the middle of the array
					const leftHalf = items.slice(0, mid); // slice the items array from the start to the middle
					const rightHalf = items.slice(mid); // slice the items array from the end to the middle

					mergeSort(dir, leftHalf, header); // merge sort the left half (recursion)
					mergeSort(dir, rightHalf, header); // merge sort the right half (recursion)

					let i = 0; // counts position for the left half
					let j = 0; // counts position for the right half
					let k = 0; // counts position in the final array
					while (i < leftHalf.length && j < rightHalf.length) { // checks that the end has not been reached of each array
						if (dir == "asc") {
							if (leftHalf[i][header] < rightHalf[j][header]) { // compares records using the header given
								items[k] = leftHalf[i]; // add record from lefthalf to items
								i++; // increment i
							} else { // if righthalf is smaller
								items[k] = rightHalf[j]; // add record from righthalf to items
								j++; // increment j
							}
							k++; // increment k
						} else {
							if (leftHalf[i][header] > rightHalf[j][header]) { // compares records using the header given
								items[k] = leftHalf[i]; // add record from lefthalf to items
								i++; // increment i
							} else { // if righthalf is smaller
								items[k] = rightHalf[j]; // add record from righthalf to items
								j++; // increment j
							}
							k++; // increment k
						}
					}
					while (i < leftHalf.length) { // check if lefthalf still has unsorted records
						items[k] = leftHalf[i]; // add then to items
						i++; // increment i
						k++; // increment k
					}
					while (j < rightHalf.length) { // check if righthalf still has unsorted records
						items[k] = rightHalf[j]; // add them to items
						j++; // increment j
						k++; // increment k
					}
				}
				return items; // return the items array
			}

			// sortTable is used to sort the studentTableBody
			// it takes the input of the header by which it needs to sort
			// it has no output as it runs a function to populate the table
			// there are no preconditions
			function sortTable(header) {
				let table = document.getElementById("studentTableBody"); // assign the target table to variable table
				const records = []; // create an empty array
				for (let i = 0; i < table.rows.length; i++) { // iterate over the rows in the table
					let row = table.rows[i]; // grab the current iteration's row
					const temp = []; // create a temporary array
					for (let j = 0; j < row.cells.length; j++) { // iterate over cells in current row
						temp.push(row.cells[j].innerHTML); // add the text from current cell to temp array
					}
					records.push(temp); // temp (which now contains entire row as an array) is pushed into records 2d array
				}
				
				for (let i = 0; i < records.length; i++) {
					let d = records[i][4];
					d = formatDate(d);
					records[i][4] = d;
				}
				
				let headers = document.getElementsByTagName("th"); // get all the headers from the table
				let head = headers[header]; // get the target header
				let clas = head.className; // get the target header's class
				let dir = "asc"; // set the default direction for sorting

				for (let t = 0; t < headers.length; t++) { // iterate over the headers
					headers[t].classList.remove("asc"); // remove the asc class
					headers[t].classList.remove("desc"); // remove the desc class
				}

				if (clas == "asc") { // check current class of target header
					dir = "desc"; // if it's ascending set it to descending
					head.classList.remove("asc"); // remove the asc class
					head.classList.add("desc"); // add the desc class
				} else if (clas == "desc") { // if it's descending set it to ascending
					head.classList.remove("desc"); // remove the desc class
					head.classList.add("asc"); // add the asc class
				} else { // if the class hasn't been set yet
					head.classList.add("asc"); // add the asc class
				}

				let sortRecords = mergeSort(dir, records, header); // perform the merge sort on the 2d array
				populateTable(sortRecords); // populate the table with the sorted 2d array
			}

			// searchTable is a function used to search the table for a given term
			// it takes the search term as an input
			// it has no output and instead repopulates the table with the searched data
			// there are no preconditions
			function searchTable(term) {
				let searchedRecords = []; // create an empty array for the searched records
				for (let i = 0; i < originalRecords.length; i++) {
					let row = originalRecords[i]; // grab the current iteration's row
					while (true) { // whille a record has not been found
						for (let j = 0; j < row.length; j++) { // iterate over cells in current row
							let cell = row[j]; // a field in the current record
							if (cell.toLowerCase().includes(term.toLowerCase())) { // if the current cell includes the search term
								searchedRecords.push(row); // add the whole row to the sorted records list
								break; // break out the loop
							}
						}
						break; // break if nothing is found
					}
				}
				populateTable(searchedRecords);
			}

				// let table = document.getElementById("studentTableBody");
				// let rows = table.getElementsByTagName("tr");
				// for (let i = 0; i < rows.length; i++) {
				// 	console.log(rows[i].innerText.split("\\t"));
				// 	let text = rows[i].innerText.split("\\t");
				// 	console.log(text);
				// 	console.log(text[3]);
				// 	if (text[3] == "In") {
				// 		rows[i].classList.add("red");
				// 	} else {
				// 		rows[i].classList.add("green");
				// 	}
				// }

			// the evacuate function is used to toggle the visibility of the tutor selection form
			// it takes no inputs
			// it gives no output, instead it updates the table
			// there are no preconditions
			function evacuate() {
				let box = document.getElementById("vanisher"); // get the div labelled vanisher
				
				if (box.style.display == "block") { // if the css display option is set to block (visible)
					box.style.display = "none"; // set the display to none (invisible)
				} else {
					box.style.display = "block"; // set the display to block (visible)
				}

				const tutorGroups = []; // create an empty array called tutor groups
				for (let i = 0; i < originalRecords.length; i++) { // iterate over the rows in the table
					if (!tutorGroups.includes(originalRecords[i][2])) { // if the tutor group is not already in the array
						tutorGroups.push(originalRecords[i][2]); // add the tutor group to the array
					}
				}

				tutorGroups.sort(); // sort the tutor groups
				let select = document.getElementById("tutorSelect"); // get the select tag
				removeChildren(select); // remove all the existing children
				let pleaseSelect = document.createElement('option'); // create new option
				pleaseSelect.value = "Please select"; // set the value to "Please select"
				pleaseSelect.innerHTML = "Please select"; // set the inner HTML to "Please select"
				select.appendChild(pleaseSelect); // add the option to the select
				

				for (let i = 0; i < tutorGroups.length; i++) {	 // loop over the tutor groups
					let option = document.createElement('option'); // create new option
					option.value = tutorGroups[i]; // set value to the tutor group
					option.innerHTML = tutorGroups[i]; // set the innerHTMl to the tutor group
					select.appendChild(option); // add the option to the select
				}
				tutorGroups.length = 0; // empty the tutorGroups array
			}

			// the removeChildren function removes all children from a given parent
			// it takes the parent as an input
			// it gives no output
			// there are no preconditions
			function removeChildren(parent) {
				while (parent.firstChild) { // while there is an existing child
					parent.removeChild(parent.firstChild); // delete that child
				}
			}
		</script>
	</head>
	<body>
		<div class="header">
			<h1>Home</h1>
		</div>
		<div class="topnav">
			<a class="topnav active" href="">Home</a>
			<!-- <a href="">Admin</a> -->
			<a style="float: right" href="logout.php">Log Out</a>
			<a style="float: right" href="#" onclick="evacuate();">Evacuation Report</a>
		</div>
		<div class="row">
			<div class="column side">
				<p>
				<!-- <div id="vanisher" style="display:none;">
					<div class="form">
						<div class="box">
							<input name="surname" type="text" placeholder="Surname">
							<input name="forename" type="text" placeholder="Forename">
							<input name="tutorGroup" type="text" placeholder="Tutor Group">
							<input name="id" type="text" placeholder="Badge ID (Optional)">
							<button name="clear">Clear</button>
							<button name="submit">Submit</button>
						</div>
					</div>
				</div> -->
			</div>
			<div class="column middle">
				<div class="form border">
					<form method="post">
						<input id="search" onInput="searchTable(document.getElementById('search').value);" style="display: inline-block; margin: 6px; width: 500px; text-align: center;" placeholder="Search" type="text"/>
					</form>
				</div>
				<table id="studentTable">
					<thead>
						<tr id="studentTableHeaders">
							<th id="tableHeader0" style="cursor: pointer" onClick="sortTable(0);">Surname</th>
							<th id="tableHeader1" style="cursor: pointer" onClick="sortTable(1);">Forename</th>
							<th id="tableHeader2" style="cursor: pointer" onClick="sortTable(2);">Tutor Group</th>
							<th id="tableHeader3" style="cursor: pointer" onClick="sortTable(3);">Status</th>
							<th id="tableHeader4" style="cursor: pointer" onClick="sortTable(4);">Last In/Out</th>
						</tr>
					</thead>
					<tbody id="studentTableBody"></tbody>
				</table>
			</div>
			<div class="column side">
				<div id="vanisher">
					<div id="tutorBox" class="box" style="float: right;">
						<div class="form">
							<form method="POST">
								<h4>Choose tutor group:</h4>
								<select name="tutorSelect" id="tutorSelect">
								</select>
								<button name="submit">Generate</button>
							</form>
						</div>
					</div>
				</div>
				<h5 id="clock" style="float: right; padding: 0 30px;">[UTC String]</h5>
		   </div>
		</div>
		<script>
			getTime();
			let rec = <?php echo json_encode(getStudents($database)); ?>;
			populateTable(rec);

			let studTable = document.getElementById("studentTableBody");
			let originalRecords = []; // empty array to contain the original records
			for (let i = 0; i < studTable.rows.length; i++) { // iterate over the rows in the table
				let row = studTable.rows[i]; // grab the current iteration's row
				const temp = []; // create a temporary array
				for (let j = 0; j < row.cells.length; j++) { // iterate over cells in current row
					temp.push(row.cells[j].innerHTML); // add the text from current cell to temp array
				}
				originalRecords.push(temp); // temp (which now contains entire row as an array) is pushed into records 2d array
			}
		</script>
	</body>
</html>