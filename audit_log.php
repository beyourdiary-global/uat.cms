<?php
$pageTitle = "Audit Log";
$currentPagePin = 18;

include 'menuHeader.php';
include 'checkCurrentPagePin.php';
include ROOT.'/include/access.php';

$tblName = AUDIT_LOG;

$num = 1;   // numbering

$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');
$selectedAction = $_GET['action'] ?? '';
$selectedScreen = $_GET['screen_type'] ?? '';

$query = "SELECT *, concat(create_date,' ',create_time) as datetimes FROM " . AUDIT_LOG . " WHERE 1=1";

if (!empty($selectedMonth)) {
    $query .= " AND MONTH(create_date) = '" . mysqli_real_escape_string($connect, $selectedMonth) . "'";
}

if (!empty($selectedYear)) {
    $query .= " AND YEAR(create_date) = '" . mysqli_real_escape_string($connect, $selectedYear) . "'";
}

if (!empty($selectedAction)) {
    $query .= " AND log_action = '" . mysqli_real_escape_string($connect, $selectedAction) . "'";
}

if (!empty($selectedScreen)) {
    $query .= " AND screen_type = '" . mysqli_real_escape_string($connect, $selectedScreen) . "'";
}


$query .= " ORDER BY datetimes DESC";

$result = mysqli_query($connect, $query);

if (!$result) {
    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    preloader(500);

    $(document).ready(() => {
        createSortingTable('table');
    });
</script>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">
        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

            <div class="col-12 col-md-8">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>
                </div>
                
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-2">
                        <select class="form-select" name="month" onchange="this.form.submit()">
                            <option value="">Select Month</option>
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $monthVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                                $monthName = date("F", mktime(0, 0, 0, $m, 1));
                                $selected = ($selectedMonth == $monthVal) ? 'selected' : '';
                                echo "<option value='$monthVal' $selected>$monthName</option>";
                            }
                            ?>
                        </select>
                    </div>
                
                    <div class="col-md-2">
                        <select class="form-select" name="year" onchange="this.form.submit()">
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                $selected = ($selectedYear == $y) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                
                    <div class="col-md-3">
                        <select class="form-select" name="action" onchange="this.form.submit()">
                            <option value="">Filter by Action</option>
                            <?php
                            $actionQuery = "SELECT DISTINCT log_action FROM " . AUDIT_LOG;
                            $actionResult = mysqli_query($connect, $actionQuery);
                            while ($actionRow = mysqli_fetch_assoc($actionResult)) {
                                $actionId = (int) $actionRow['log_action']; // e.g., 1, 2, 3
                                $selected = ($selectedAction == $actionId) ? 'selected' : '';
                        
                                // Use get_allowed_audit_actions to convert ID to name
                                $actionName = get_allowed_audit_actions($actionId); // e.g., 'view', 'edit'
                        
                                // Make it nice for display
                                $label = ucfirst($actionName);
                        
                                echo "<option value='" . htmlspecialchars($actionId, ENT_QUOTES) . "' $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="screen_type" onchange="this.form.submit()">
                            <option value="">Filter by Screen</option>
                            <?php
                            $screenQuery = "SELECT DISTINCT screen_type FROM " . AUDIT_LOG . " WHERE screen_type IS NOT NULL AND screen_type != ''";
                            $screenResult = mysqli_query($connect, $screenQuery);
                            while ($screenRow = mysqli_fetch_assoc($screenResult)) {
                                $screenVal = $screenRow['screen_type'];
                                $selected = ($selectedScreen == $screenVal) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($screenVal, ENT_QUOTES) . "' $selected>" . ucfirst($screenVal) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary w-100" style="height: 38px; border-radius:20px;display: block;align-content: center;">Reset Filters</a>
                    </div>
                </form>

                <table class="table table-striped" id="table">
                    <thead>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">DateTime</th>
                            <th scope="col">Username</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) >= 1) {

                            while ($row = $result->fetch_assoc()) {
                                $resultUser = getData('username', "id='" . $row['user_id'] . "'", '', USR_USER, $connect);
                                if (!$resultUser) {
                                    echo "<script type='text/javascript'>alert('Sorry, currently network temporary fail, please try again later.');</script>";
                                    echo "<script>location.href ='$SITEURL/dashboard.php';</script>";
                                }
                                $rowUser = $resultUser->fetch_assoc();

                                echo '
                            <tr>
                                <th class="hideColumn" scope="row">' . $row['id'] . '</th>
                                <th scope="row">' . $num++ . '</th>
                                <td scope="row">' . $row['create_date'] . ', ' . $row['create_time'] . '</td>
                                <td scope="row">' . $rowUser['username'] . '</td>
                                <td scope="row">' . $row['action_message'] . '</td>
                            </tr>
                            ';
                            }
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th class="hideColumn" scope="col">ID</th>
                            <th scope="col">S/N</th>
                            <th scope="col">DateTime</th>
                            <th scope="col">Username</th>
                            <th scope="col">Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        //Initial Page And Action Value
        var page = "<?= $pageTitle ?>";
        var action = "<?php echo isset($act) ? $act : ' '; ?>";

        checkCurrentPage(page, action);
        //to solve the issue of dropdown menu displaying inside the table when table class include table-responsive
        dropdownMenuDispFix();
        //to resize table with bootstrap 5 classes
        datatableAlignment('table');
        setButtonColor();
    </script>
</body>

</html>