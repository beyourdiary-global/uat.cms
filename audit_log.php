<?php
include './include/common.php';
include './include/connection.php';

$num = 1;  //for numbering
$query = "SELECT *, concat(create_date,' ',create_time) as datetimes FROM ".AUDIT_LOG." ORDER BY datetimes desc";
$result = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html>
<head>
<?php include "header.php"; ?>
<link rel="stylesheet" href="./css/main.css">

</head>

<style>
h2, a {
    color: #000000;
}
</style>

<script>
$( document ).ready(() => {
    createSortingTable('audit_log_table');
});    
</script>

<body>

<div id="dispTable" class="container-fluid d-flex justify-content-center mt-3">

        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">

            <div class="d-flex justify-content-between">
                <div class="left">
                        <h2>Audit Log</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Audit Log</p>
                </div>
            </div>

            <table class="table table-striped" id="audit_log_table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">DateTime</th>
                        <th scope="col">Username</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php
if(mysqli_num_rows($result) >= 1)
{
    while($row = $result->fetch_assoc())
    {
        $query2 = "SELECT `username` FROM ".USR_USER." WHERE id = '".$row['user_id']."'";
        $result2 = mysqli_query($connect, $query2);
        $row2 = $result2->fetch_assoc();
?>
                    <tr>
                        <th scope="row"><?php echo $num; ?></th>
                        <td scope="row"><?php echo $row['create_date'] . ', ' . $row['create_time'] ?></td>
                        <td scope="row"><?php echo $row2['username']?></td>
                        <td scope="row"><?php echo $row['action_message']?></td>
                    </tr>
<?php
    $num++; }
} else {
?>
                    <tr>
                        <th scope="row" colspan="4">No audit log record.</th>
                        <td scope="row" style="display: none"></td>
                        <td scope="row" style="display: none"></td>
                        <td scope="row" style="display: none"></td>
                    </tr>
<?php
}
?>
                </tbody>
            </table>
        </div>

</div>

</body>
</html>