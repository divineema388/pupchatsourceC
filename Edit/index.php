<?php include("db_config.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfinityFree DB Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body class="container mt-4">
    <h2 class="text-center">InfinityFree Database Manager</h2>

    <!-- Table Selection -->
    <label for="tableSelect">Select Table:</label>
    <select id="tableSelect" class="form-select mb-3">
        <option value="">-- Choose Table --</option>
        <?php
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            echo "<option value='$row[0]'>$row[0]</option>";
        }
        ?>
    </select>

    <!-- Table Data Display -->
    <div id="tableData"></div>

    <!-- Query Execution -->
    <h4 class="mt-4">Run SQL Query:</h4>
    <textarea id="queryInput" class="form-control" rows="3" placeholder="Enter SQL query here"></textarea>
    <button class="btn btn-primary mt-2" onclick="executeQuery()">Run Query</button>
    <div id="queryResult" class="mt-3"></div>

    <script>
        $(document).ready(function() {
            $("#tableSelect").change(function() {
                var table = $(this).val();
                if (table) {
                    $.post("fetch_table.php", { table: table }, function(data) {
                        $("#tableData").html(data);
                    });
                }
            });
        });

        function executeQuery() {
            var query = $("#queryInput").val();
            if (query) {
                $.post("execute_query.php", { query: query }, function(data) {
                    $("#queryResult").html(data);
                });
            }
        }
    </script>
</body>
</html>