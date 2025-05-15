<?php
$pageTitle = 'Goal Target';
include 'menuHeader.php';
include 'checkCurrentPagePin.php';
$redirect_page = $SITEURL . '/goalTarget_table.php';

// Get the `act` parameter (I for Insert, E for Edit)
// Get the `act` parameter (I for Insert, E for Edit)
$action = isset($_GET['act']) ? $_GET['act'] : null; // Get action parameter
$pageAction = getPageAction($action); // Use $act instead of undefined $action
$id = isset($_GET['id']) ? intval($_GET['id']) : null; // Get year from URL
$goals = [];
$isReadOnly = is_null($action); // Determine if fields should be readonly

$pageActionTitle = $pageAction . " " . $pageTitle;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = $_POST['year'];
    $months = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];

    if ($action === 'I') {
        // Insert Mode
        foreach ($months as $month) {
            $shopee_my_goal = isset($_POST["shopee_my_goal_$month"]) ? floatval($_POST["shopee_my_goal_$month"]) : 0;
            $shopee_sg_goal = isset($_POST["shopee_sg_goal_$month"]) ? floatval($_POST["shopee_sg_goal_$month"]) : 0;
            $lazada_goal = isset($_POST["lazada_goal_$month"]) ? floatval($_POST["lazada_goal_$month"]) : 0;
            $facebook_goal = isset($_POST["facebook_goal_$month"]) ? floatval($_POST["facebook_goal_$month"]) : 0;
            $website_goal = isset($_POST["website_goal_$month"]) ? floatval($_POST["website_goal_$month"]) : 0;

            // Prepare and execute the SQL statement
            $stmt = $connect->prepare("
                INSERT INTO " . YEARLYGOAL . " (year, month, shopee_my_goal, shopee_sg_goal, lazada_goal, facebook_goal, website_goal)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'issdddd',
                $year,
                $month,
                $shopee_my_goal,
                $shopee_sg_goal,
                $lazada_goal,
                $facebook_goal,
                $website_goal
            );

            if (!$stmt->execute()) {
                echo "<script>alert('Error saving goals for $month: " . $stmt->error . "');</script>";
            }
        }

        $connect->close();
        echo "<script>alert('Goals for the year $year have been added successfully.');</script>";
    } elseif ($action === 'E') {
        // Edit Mode
        foreach ($months as $month) {
            $shopee_my_goal = isset($_POST["shopee_my_goal_$month"]) ? floatval($_POST["shopee_my_goal_$month"]) : 0;
            $shopee_sg_goal = isset($_POST["shopee_sg_goal_$month"]) ? floatval($_POST["shopee_sg_goal_$month"]) : 0;
            $lazada_goal = isset($_POST["lazada_goal_$month"]) ? floatval($_POST["lazada_goal_$month"]) : 0;
            $facebook_goal = isset($_POST["facebook_goal_$month"]) ? floatval($_POST["facebook_goal_$month"]) : 0;
            $website_goal = isset($_POST["website_goal_$month"]) ? floatval($_POST["website_goal_$month"]) : 0;

            // Prepare and execute the SQL statement for update
            $stmt = $connect->prepare("
                UPDATE " . YEARLYGOAL . " 
                SET shopee_my_goal = ?, shopee_sg_goal = ?, lazada_goal = ?, facebook_goal = ?, website_goal = ?
                WHERE year = ? AND month = ?
            ");
            $stmt->bind_param(
                'dddddis',
                $shopee_my_goal,
                $shopee_sg_goal,
                $lazada_goal,
                $facebook_goal,
                $website_goal,
                $year,
                $month
            );

            if (!$stmt->execute()) {
                echo "<script>alert('Error updating goals for $month: " . $stmt->error . "');</script>";
            }
        }

        $connect->close();
        echo "<script>alert('Goals for the year $year have been updated successfully.');</script>";
    }
} elseif ($id) {
    // If no `act` is specified, fetch data for the given year
    $stmt = $connect->prepare("
        SELECT *,total_goal FROM " . YEARLYGOAL . " WHERE year = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {
        $goals[$row['month']] = $row;
    }
    $stmt->close();
}
function generateYearlyGoalForm($goals = [], $isReadOnly = false)
{
    $readonlyAttr = $isReadOnly ? 'readonly' : ''; // Add readonly attribute if needed
    $disabledAttr = $isReadOnly ? 'disabled' : ''; // Add disabled attribute for buttons if needed

    $totalYearGoal = 0;
    $months = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
    foreach ($months as $month) {
        $monthData = isset($goals[$month]) ? $goals[$month] : ['total_goal' => 0];
        $totalYearGoal += $monthData['total_goal'];
    }
    // Year input
    echo '<div class="form-group mb-3 ">
            <label for="year">Year:</label>
            <div class="row">
            <div class="col-6"><input type="tel" class="form-control" id="year" name="year" maxlength="4" required value="' . (isset($_GET['id']) ? $_GET['id'] : '') . '"' . $readonlyAttr . '></div>
            <div class="col-6"><input type="tel" class="form-control" id="Totalyear" name="Totalyear" placeholder="Total Goal" required readonly value="' . $totalYearGoal . '"></div>
            </div>
          </div>';

    // Months and their corresponding inputs
    echo '<div id="months" class="row">';


    foreach (array_chunk($months, 4) as $monthRow) {
        echo '<div class="row mb-3">';
        foreach ($monthRow as $month) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1)); // Get the month name
            $monthData = isset($goals[$month]) ? $goals[$month] : ['shopee_my_goal' => 0, 'shopee_sg_goal' => 0, 'lazada_goal' => 0, 'facebook_goal' => 0, 'website_goal' => 0, 'total_goal' => 0];
            echo "<div class='col-12 col-md-3'>
                    <div class='accordion' data-row-group='rowGroup_$monthRow[0]' id='accordion_$month'>
                        <div class='accordion-item'>
                            <h2 class='accordion-header' id='heading_$month'>
                                <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse_$month' aria-expanded='false' aria-controls='collapse_$month'>
                                    $monthName: <span class='month-total-display' id='total_display_$month'>" . $monthData['total_goal'] . "</span>
                                </button>
                            </h2>
                            <div id='collapse_$month' class='accordion-collapse collapse' aria-labelledby='heading_$month'>
                                <div class='accordion-body'>
                                    <div class='row'>
                                        <div class='col-12 mb-2'>
                                            <label>Shopee MY Goal:</label>
                                            <input type='tel' class='form-control goal' data-month='$month' name='shopee_my_goal_$month' value='" . $monthData['shopee_my_goal'] . "' $readonlyAttr>
                                        </div>
                                        <div class='col-12 mb-2'>
                                            <label>Shopee SG Goal:</label>
                                            <input type='tel' class='form-control goal' data-month='$month' name='shopee_sg_goal_$month' value='" . $monthData['shopee_sg_goal'] . "' $readonlyAttr>
                                        </div>
                                        <div class='col-12 mb-2'>
                                            <label>Lazada Goal:</label>
                                            <input type='tel' class='form-control goal' data-month='$month' name='lazada_goal_$month' value='" . $monthData['lazada_goal'] . "' $readonlyAttr>
                                        </div>
                                        <div class='col-12 mb-2'>
                                            <label>Facebook Goal:</label>
                                            <input type='tel' class='form-control goal' data-month='$month' name='facebook_goal_$month' value='" . $monthData['facebook_goal'] . "' $readonlyAttr> 
                                        </div>
                                        <div class='col-12 mb-2'>
                                            <label>Website Goal:</label>
                                            <input type='tel' class='form-control goal' data-month='$month' name='website_goal_$month' value='" . $monthData['website_goal'] . "' $readonlyAttr>
                                        </div>
                                        <div class='col-12'>
                                            <input type='hidden' class='month-total' id='total_$month' name='total_$month' value='0'>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>";
        }
        echo '</div>';
    }

    echo '</div>';


}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const goalInputs = document.querySelectorAll('.goal'); // All goal inputs
        const monthTotals = document.querySelectorAll('.month-total'); // Hidden monthly totals
        const yearTotalInput = document.getElementById('Totalyear'); // Total year input field

        // Ensure that yearTotalInput exists
        if (!yearTotalInput) {
            console.error("Yearly total input field (Totalyear) not found in the DOM.");
            return;
        }

        // Add event listeners to all goal inputs
        goalInputs.forEach(input => {
            input.addEventListener('input', function () {
                const month = this.dataset.month; // Current month from the data attribute
                if (!month) {
                    console.error("Missing data-month attribute on an input element.");
                    return;
                }

                const monthInputs = document.querySelectorAll(`.goal[data-month="${month}"]`); // All inputs for this month
                const monthTotalInput = document.getElementById(`total_${month}`); // Hidden total for this month
                const monthTotalDisplay = document.getElementById(`total_display_${month}`); // Visible total display

                let monthTotal = 0;

                // Iterate through the month's inputs and sum their values
                monthInputs.forEach(goalInput => {
                    let value = parseFloat(goalInput.value.trim()) || 0; // Parse value, default to 0 if invalid
                    if (goalInput.value.trim() === '') {
                        goalInput.value = '0'; // If the field is empty, set it to 0
                    }
                    monthTotal += value;
                });

                // Update the monthly total fields
                if (monthTotalInput) monthTotalInput.value = monthTotal.toFixed(2); // Hidden input
                if (monthTotalDisplay) monthTotalDisplay.textContent = monthTotal.toFixed(2); // Visible total

                // Recalculate and update the yearly total
                updateYearTotal();
            });
        });

        // Function to calculate and update the yearly total
        function updateYearTotal() {
            let yearTotal = 0;
            console.log(monthTotals);
            // Sum up all monthly totals
            monthTotals.forEach(monthTotalInput => {
                // console.log(monthTotalInput.value);
                const value = parseFloat(monthTotalInput.value) || 0; // Parse value, default to 0 if invalid
                yearTotal += value;
            });

            // Update the yearly total input field
            yearTotalInput.value = yearTotal;
        }
        const accordions = document.querySelectorAll('.accordion');
        accordions.forEach(accordion => {
            const rowGroup = accordion.getAttribute('data-row-group');
            const buttons = document.querySelectorAll(`.accordion[data-row-group="${rowGroup}"] .accordion-button`);

            buttons.forEach(button => {
                button.addEventListener('click', function () {
                    const isExpanding = button.classList.contains('collapsed');
                    buttons.forEach(btn => {
                        const target = document.querySelector(btn.getAttribute('data-bs-target'));
                        if (isExpanding) {
                            target.classList.add('show');
                            btn.classList.remove('collapsed');
                        } else {
                            target.classList.remove('show');
                            btn.classList.add('collapsed');
                        }
                    });
                });
            });
        });
    });
</script>

<style>
    .accordion-button {
        font-weight: bold;
    }

    .goal {
        width: 100%;
        box-sizing: border-box;
    }

    .month-total {
        font-weight: bold;
        color: #333;
    }

    .month-total-display {
        margin-left: 10px;
        font-weight: normal;
        color: #555;
    }

    .floatLeftBtn {
        width: 20%;
        height: 42px;
        display: flex;
        align-items: center;
        text-align: center;
        justify-content: center;
    }

    .textMIddle {
        text-align: center;
        justify-content: center;
        display: flex;
    }
</style>

<body>


    <form method="POST" id="goalForm" class="container-xxl mt-5">
        <div class="d-flex flex-column my-3">
            <p><a href="<?= $redirect_page ?>"><?= $pageTitle ?></a> <i class="fa-solid fa-chevron-right fa-xs"></i>
                <?php echo $pageActionTitle ?>
            </p>
        </div>
        <div class="d-flex flex-column my-3">
            <div class="form-group">
                <h2>
                    <?php echo $pageActionTitle ?>
                </h2>
            </div>
        </div>

        <?php echo generateYearlyGoalForm($goals, $isReadOnly); ?>

        <div class="textMIddle">
            <button type="submit" class="btn btn-primary btn-lg floatLeftBtn" <?php if ($isReadOnly)
                echo 'disabled'; ?>><?php echo $pageActionTitle; ?></button>
        </div>


    </form>
</body>