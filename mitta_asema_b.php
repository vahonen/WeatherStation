<!DOCTYPE html>
<html>
<head>

    <?php date_default_timezone_set('Europe/Helsinki'); ?>
    <title>Mitta-asema</title>
    <link rel="stylesheet" type="text/css" href="./styles.css">
    

</head>

<body>

    <h1>DHT22-sensorin mittadataa</h1>

    <h2>Viimeisin mittaus: <span id="currentTime"></span></h2>
    
    
    <table style="border:none">
        <tr>
            <td style="border:none"><div id="measurement_div"></div></td>
            <td style="border:none"><div id="humidity_div"></div></td>

        </tr>
    </table>

    <h2>Hae mittauksia</h2><br>
    <form action="show-table.php" method="POST" target = "_blank">

        <p>
	        <label><b>Mistä:</b></label>
            <input type="date" name="dateFrom" value="<?php echo date("Y-m-d"); ?>"/>
        </p>

        <p>
            <label><b>Mihin: </b></label>
            <input type="date" name="dateTo" value="<?php echo date("Y-m-d"); ?>"/>
        </p>

        <p>
            <input type="submit" name="submit" value="Hae"/>
        </p>
    </form>

    <h2>Viimeisimmät 10 mittausta (mittausjakso: <span id = "measurementInterval"></span> s) <span id = "measurementStatus" class = "red"></span></h2>
    <div id="lastRecordsTable"></div>

    <table style="border:none">
        <tr>
            <td style ="border:none"><button onclick="deleteTable()">Tyhjennä taulukko</button></td>
            <td style ="border:none"><button id = "toggleMeasurement" onclick="toggleMeasurement()">Pysäytä mittaus</button></td>
            <td style = "border:none"><button onclick="setMeasPeriod()">Aseta mittausjakso</td>
            <td style = "border:none"><input id="intervalSet" type="number" min = "1" max="86400" style="width: 100px" /></td>
        </tr>
    </table>
    
    
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

        google.charts.load('current', {'packages':['gauge','corechart']});
        google.charts.setOnLoadCallback(drawChart);
	  
        var varTimer = setInterval(requestTimer, 1000); // 1000 ms
        var measurements = {"temperature":0, "humidity":0};
        
        showParameter("interval", "measurementInterval");

        function toggleMeasurement() {
           
            var url = "write-db.php?x=enabled&y=";
            var x = document.getElementById("toggleMeasurement");
            if (x.innerHTML === "Pysäytä mittaus") {
                x.innerHTML = "Käynnistä mittaus";
                x.style.color = "green";
                document.getElementById("measurementStatus").innerHTML = "Mittaus pysäytetty";
                url += "0";
                

            } else {
                x.innerHTML = "Pysäytä mittaus";
                x.style.color = "black";
                document.getElementById("measurementStatus").innerHTML = "";
                url += "1";
            }

            requestData(url, dummyXhttp);
        }

        function showParameter(parameterName, elementName) {
            var url = "read-db.php?x=parameter&y=" + parameterName;
            var xhttp;
	        xhttp=new XMLHttpRequest();
	        xhttp.onreadystatechange = function() {
		        if (this.readyState == 4 && this.status == 200) {
                    document.getElementById(elementName).innerHTML = xhttp.responseText;
                    //document.getElementById("measurementInterval").innerHTML = xhttp.responseText;

                }
            };
            xhttp.open("GET",url,true);
            xhttp.send();
        }

        function setMeasPeriod() {
            var x = document.getElementById("intervalSet").value;
            var url = "write-db.php?x=interval&y=" + x;
            requestData(url, measPeriod);
        
        }

        function deleteTable() {
	        if (confirm("Haluatko varmasti tyhjentää taulukon kaikki mittaukset")) {
                requestData('read-db.php?x=delete', deleteData);
            } else {}
        }
	
        function requestTimer() {
   
	        requestData('read-db.php?x=time', latestTimeStamp);
	        requestData('read-db.php?x=records', latestRecords);
	
	        requestData('read-db.php?x=latest', latestMeasurements);
	        drawChart();
        }

        function requestData(url, cFunction) {
	        var xhttp;
	        xhttp=new XMLHttpRequest();
	        xhttp.onreadystatechange = function() {
		        if (this.readyState == 4 && this.status == 200) {
		            cFunction(this);
                }
            };
            xhttp.open("GET",url,true);
            xhttp.send();
        }

        function dummyXhttp(xhttp){
        }
        
        function measPeriod(xhttp){
	        //alert("Mittajakso asetettu!");
            document.getElementById("measurementInterval").innerHTML = xhttp.responseText;
        }

        function deleteData(xhttp){
	        measurements["temperature"] = 0;
            measurements["humidity"] = 0;
	        //alert("Taulukko tyhjennetty!");
        }

        function latestTimeStamp(xhttp){
	        document.getElementById("currentTime").innerHTML = xhttp.responseText;
        }


        function latestRecords(xhttp){
	        document.getElementById("lastRecordsTable").innerHTML = xhttp.responseText;
        }

        function latestMeasurements(xhttp){
	        measurements = JSON.parse(xhttp.responseText);	
        }

        function drawChart(){

            var temperature = parseFloat(measurements["temperature"]);
            var humidity = parseFloat(measurements["humidity"]);
            
            var temperatureGauge = new google.visualization.Gauge(document.getElementById('measurement_div'));
            var humidityGauge = new google.visualization.Gauge(document.getElementById('humidity_div'));

            var dataT = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Lämpötila', temperature]
              
            ]);

            var optionsT = {
                max: 40, min: 0,
                width: 200, height: 200,
                majorTicks: ["0", "10", "20", "30", "40"],
                minorTicks: 10
            };

            var dataH = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Ilmankosteus', humidity]
              
            ]);

            var optionsH = {
                max: 100, min: 0,
                width: 200, height: 200,
                majorTicks: ["0", "", "20", "", "40", "", "60", "", "80", "", "100"],
                minorTicks: 10
            };
            
            temperatureGauge.draw(dataT, optionsT);
            humidityGauge.draw(dataH, optionsH);        
        }

    </script>	

</body>
</html>