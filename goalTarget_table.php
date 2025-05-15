<?php
$pageTitle = "Goal Target";

include 'menuHeader.php';
include 'checkCurrentPagePin.php';

$tblName = YEARLYGOAL;
$pinAccess = checkCurrentPin($connect, $pageTitle);

$_SESSION['act'] = '';
$_SESSION['viewChk'] = '';
$_SESSION['delChk'] = '';
$num = 1;   // numbering

$redirect_page = $SITEURL . '/goalTarget.php';
$deleteRedirectPage = $SITEURL . '/goalTarget_table.php';

$result = getData(' year,sum(total_goal) as goal ', '  status = "A" group by year ', '', $tblName, $connect);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $year = $_POST['year'];
    $deleteQuery = "DELETE FROM $tblName WHERE year = ?";
    $stmt = $connect->prepare($deleteQuery);
    $stmt->bind_param('i', $year);
    if ($stmt->execute()) {
        echo "<script>
                alert('All records for the year $year have been deleted successfully.');
                window.location.href = '$deleteRedirectPage';
              </script>";
    } else {
        echo "<script>
                alert('Failed to delete records for the year $year.');
                window.location.href = '$deleteRedirectPage';
              </script>";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="<?= $SITEURL ?>/css/main.css">
</head>

<script>
    preloader(300);

    $(document).ready(() => {
        createSortingTable('table');
    });
</script>

<style>
    .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        margin: 3px;
    }

    .btn-container {
        white-space: nowrap;
    }
</style>

<body>
    <div class="pre-load-center">
        <div class="preloader"></div>
    </div>

    <div class="page-load-cover">

        <div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

            <div class="col-12 col-md-11">

                <div class="d-flex flex-column mb-3">
                    <div class="row">
                        <p><a href="<?= $SITEURL ?>/dashboard.php">Dashboard</a> <i
                                class="fa-solid fa-chevron-right fa-xs"></i> <?php echo $pageTitle ?></p>
                    </div>

                    <div class="row">
                        <div class="col-12 d-flex justify-content-between flex-wrap">
                            <h2><?php echo $pageTitle ?></h2>
                            <div class="mt-auto mb-auto">
                                <?php if (isActionAllowed("Add", $pinAccess)): ?>
                                    <a class="btn btn-sm btn-rounded btn-primary" name="addBtn" id="addBtn"
                                        href="<?= $redirect_page . "?act=" . $act_1 ?>"><i class="fa-solid fa-plus"></i> Add
                                        <?php echo $pageTitle ?> </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!$result) {
                    echo '<div class="text-center"><h4>No Result!</h4></div>';
                } else {
                    ?>
                    <table class="table table-striped" id="table">
                        <thead>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col">S/N</th>
                                <th scope="col" id="action_col">Action</th>
                                <th scope="col">Year</th>
                                <th scope="col">Goal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td class="hideColumn" scope="row"><?= $row['year'] ?></td>
                                    <td scope="row"><?= $num++; ?></td>
                                    <td scope="row" class="btn-container">
                                        <?php if (isActionAllowed("View", $pinAccess)): ?>
                                            <a class="btn btn-primary me-1" href="<?= $redirect_page . "?id=" . $row['year'] ?>"><i
                                                    class="fas fa-eye"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Edit", $pinAccess)): ?>
                                            <a class="btn btn-warning me-1"
                                                href="<?= $redirect_page . "?id=" . $row['year'] . '&act=' . $act_2 ?>"><i
                                                    class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (isActionAllowed("Delete", $pinAccess)): ?>
                                            <a class="btn btn-danger"
                                                onclick="confirmationDialogGoalYear('<?= $row['year'] ?>','Are you sure want to delete','<?php echo $pageTitle ?>','<?= $redirect_page ?>','<?= $deleteRedirectPage ?>','D')"><i
                                                    class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                    </td>
                                    <td scope="row"><?= $row['year'] ?></td>
                                    <td scope="row"><?= isset($row['goal']) ? $row['goal'] : '' ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>


                        <tfoot>
                            <tr>
                                <th class="hideColumn" scope="col">ID</th>
                                <th scope="col">S/N</th>
                                <th scope="col" id="action_col">Action</th>
                                <th scope="col">Year</th>
                                <th scope="col">Goal</th>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
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

        function confirmationDialogGoalYear(year, message, pageTitle, redirectPage, deleteRedirectPage, actionType) {
            if (confirm(message)) {
                $.post(deleteRedirectPage, { year: year, action: 'delete' }, function (response) {
                    alert(response);
                    location.href = redirectPage;
                });
            }
        }
    </script>
</body>

</html>