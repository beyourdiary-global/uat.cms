<?php
$pageTitle = "Audit Log";
include 'menuHeader.php';

$num = 1;  //for numbering
$query = "SELECT *, concat(create_date,' ',create_time) as datetimes FROM ".AUDIT_LOG." ORDER BY datetimes desc";
$result = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html>
<head>
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

        <div class="col-12 col-md-8">

            <div class="d-flex justify-content-between">
                <div class="left">
                        <h2>Audit Log</h2>
                        <p><a href="dashboard.php">Dashboard</a> <i class="fa-solid fa-slash fa-rotate-90 fa-2xs"></i> Audit Log</p>
                </div>
            </div>

            <div class="table-responsive">
            <table class="table table-striped" id="audit_log_table">
                <thead>
                    <tr>
                        <th scope="col" style="display: none">ID</th>
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
    // get username
    $rst = getData('id, username','',USR_USER,$connect);
    $username_arr = array();
    if($rst != false)
    {
        while($row2 = $rst->fetch_assoc())
        {
            $username_arr[$row2['id']] = $row2['username'];
        }
    }
    

    while($row = $result->fetch_assoc())
    {
        $id = $row['user_id'];
?>
                    <tr>
                        <th scope="row" style="display: none"><?php echo $row['id']; ?></th>
                        <th scope="row"><?php echo $num; ?></th>
                        <td scope="row"><?php echo $row['create_date'] . ', ' . $row['create_time'] ?></td>
                        <td scope="row"><?php echo $username_arr["$id"]?></td>
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

</div>

</body>
<script>
dropdownMenuDispFix();
</script>
</html>