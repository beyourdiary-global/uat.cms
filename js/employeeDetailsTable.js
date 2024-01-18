//Leave Application

//Execute A Function By Using Event Listener
$('#fromTime, #toTime').on('change', calculateNumberOfDays);
$('#leaveType').on('change input click', checkLeaveCredit);
$('#leaveApplicationApplyForm').on('change input click', validateAppFormSubmitBtn);
document.getElementById("leaveType").addEventListener("change", updateRemainingLeaves);

var submitBtn = document.getElementById('actionBtn');

//Disable False When Is Edit Mode
if (!sessionStorage.getItem("leaveAppEdit"))
    submitBtn.disabled = true;

//To Check A Leave Date Apply By User Is Valid Or Not
function validateLeaveDateAvailableRange() {

    var fromTime = $('#fromTime').val();
    var toTime = $('#toTime').val();
    var dateRangeArr = leaveApplyDateArr;

    //console.log(dateRangeArr);

    if (fromTime && toTime) {

        // Parse input date range
        var inputStartDate = formatDateTime(fromTime);
        var inputEndDate = formatDateTime(toTime);
        var matchingDates = [];

        dateRangeArr.forEach(function (range, index) {
            var start = formatDateTime(range.split("->")[0]);
            var end = formatDateTime(range.split("->")[1]);

            if (inputStartDate !== start && inputEndDate !== end) {
                if (inputStartDate >= start && inputStartDate <= end)
                    matchingDates.push("[" + index + "] -> [ " + start + " TO " + end + " ]");

                if (inputEndDate >= start && inputEndDate <= end)
                    matchingDates.push("[" + index + "] -> [ " + start + " TO " + end + " ]");
            }
        });

        var warningMsgDate = $('.warning-msg-date');

        if (matchingDates.length > 0) {

            if (warningMsgDate.children().length === 0) {
                warningMsgDate.html('<div class="alert alert-danger fade show" role="alert">' +
                    '<p style="margin:0;text-align: justify;text-transform: capitalize;">The Date Range For Your Leave Application Conflicts With An Existing Leave Application Date. Kindly Choose A Other Date .</p>' +
                    '</div>');

                warningMsgDate.find('.alert').get(0).scrollIntoView();
            }
            //console.log("Matching Dates", matchingDates);
            return false
        } else {
            warningMsgDate.empty();
            return true
        }
    }
}

//Return A Date Format Ex : 2024-02-05 22:00:00
function formatDateTime(dateTime) {
    const dateObj = new Date(dateTime);
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const hours = String(dateObj.getHours()).padStart(2, '0');
    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
    const seconds = String(dateObj.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

//Initial A Leave Day Value When User Change The Leave Type
function updateInitialLeave() {
    var selectedLeaveId = document.getElementById("leaveType").value;
    var remainingLeave;

    if (leaveTypes.hasOwnProperty(selectedLeaveId)) {
        remainingLeave = leaveTypes[selectedLeaveId];
    }

    return remainingLeave;
}

//Check A Total Credit Available For Leave Type User Choose
function checkLeaveCredit() {

    var leaveCredit = updateInitialLeave();
    var warningMsg = $('.warning-msg');

    if (leaveCredit == 0) {
        $('#leaveApplicationApplyForm input, #leaveApplicationApplyForm select, #leaveApplicationApplyForm textarea').not('#leaveType').prop('disabled', true);
        $('#leaveType').prop('disabled', false);

        if (warningMsg.children().length === 0) {
            warningMsg.html('<div class="alert alert-danger fade show" role="alert">' +
                '<p style="margin:0;text-align: center;">Leave Type You Selected Is Out Of Available Leave Credit </p>' +
                '</div>');

            warningMsg.find('.alert').get(0).scrollIntoView();
        }

        return true;

    } else {
        $('#leaveApplicationApplyForm input, #leaveApplicationApplyForm select, #leaveApplicationApplyForm textarea').not('#leaveType').prop('disabled', false);
        warningMsg.empty();

        return false;
    }
}

//Change The Remaining Leave Values If Leave Type Have Selected
function updateRemainingLeaves() {
    var remainingLeaveValue = updateInitialLeave();

    if (remainingLeaveValue >= 0) {
        document.getElementById("remainingLeave").value = remainingLeaveValue;
    } else {
        document.getElementById("remainingLeave").value = 0;
    }
}

// Validate Time Input: Check if the input is a valid future date and time
function validateDateTime(inputId, errorId) {
    var input = document.getElementById(inputId);
    var error = document.getElementById(errorId);

    var inputTime = new Date(input.value);
    var currentTime = new Date();

    // Check if input is a valid date
    if (isNaN(inputTime)) {
        return false;
    }

    // Check for incomplete day input
    var dayIncomplete = input.value.split("T")[0].split("-").some(part => part.length < 2);

    if (dayIncomplete) {
        error.textContent = "Incomplete day";
        return false;
    }

    // Check if input date is in the future
    if (inputTime < currentTime) {
        error.textContent = "Date must be in the future";
        return false;
    }

    error.textContent = "";
    return true;
}

//Calc A Total Days (ToTime - FromTime)
function calculateNumberOfDays() {
    var fromTime = $('#fromTime').val();
    var toTime = $('#toTime').val();
    var submitBtn = document.getElementById('actionBtn');

    var currentTime = new Date();
    var fromTimeDate = new Date(fromTime);
    var toTimeDate = new Date(toTime);

    var warningMsg = $('.warning-msg');

    if (fromTime && toTime && fromTimeDate > currentTime && toTimeDate > currentTime) {
        var fromDate = new Date(fromTime);
        var toDate = new Date(toTime);

        var durationInMilliseconds = toDate - fromDate;

        var durationInDays = Math.round((durationInMilliseconds / (1000 * 60 * 60 * 24)) * 2) / 2;

        if (durationInDays == 0)
            durationInDays = 0.5;

        var remainingLeave = parseInt(updateInitialLeave());

        if (remainingLeave === undefined || isNaN(remainingLeave)) {
            if (warningMsg.children().length === 0) {
                warningMsg.html('<div class="alert alert-danger fade show" role="alert">' +
                    '<p style="margin:0;text-align: justify;">Please Select A Leave Type First' +
                    '</div>');

                warningMsg.find('.alert').get(0).scrollIntoView();
            }

            return false;
        } else {
            warningMsg.empty();
        }

        if (remainingLeave === 0) {
            checkLeaveCredit();
            return false;
        }

        if (toDate <= fromDate) {
            if (warningMsg.children().length === 0) {
                warningMsg.html('<div class="alert alert-danger fade show" role="alert">' +
                    '<p style="margin:0;text-align: justify;">The end date of the leave must exceed the start date of the leave.</p>' +
                    '</div>');

                warningMsg.find('.alert').get(0).scrollIntoView();
            }

            return false;
        } else {
            warningMsg.empty();
        }

        if (durationInDays >= remainingLeave) {
            $('#numOfdays').val(0);
            $('#remainingLeave').val(0);

            if (warningMsg.children().length === 0) {
                warningMsg.html('<div class="alert alert-danger fade show" role="alert">' +
                    '<p style="margin:0;text-align: justify;">Your Total Days Of Leave Exceed The Available Leave Credit.</p>' +
                    '</div>');

                warningMsg.find('.alert').get(0).scrollIntoView();
            }

            return false;
        } else {
            warningMsg.empty();
        }

        if (remainingLeave > 0 && (remainingLeave - durationInDays) <= remainingLeave) {
            $('#numOfdays').val(durationInDays);
            $('#remainingLeave').val(remainingLeave - durationInDays);
            return true;
        }
    } else {
        if (!checkLeaveCredit()) {
            warningMsg.empty();
        }
    }

    return false;
}

//Validate All The Input Field Inside A Leave Application Is Empty Or Not
function checkFormInputEmptyValue() {

    var requiredFields = document.getElementById('leaveApplicationApplyForm').querySelectorAll('[required]');

    var allFieldsFilled = Array.from(requiredFields).every(function (field) {
        return field.value.trim() !== '';
    });

    return allFieldsFilled;
}

//Check All the Validation,If All True Then Can Let The User Add/Edit Leave Application
function validateAppFormSubmitBtn() {
    var submitBtnBooleanArr = {
        'validEmptyField': [],
        'validToTime': [],
        'validFromTime': [],
        'validCalcTime': [],
        'validDateRange': [],
    };

    var submitBtn = document.getElementById('actionBtn');
    var leaveType = document.getElementById('leaveType');

    leaveType.addEventListener('change', calculateNumberOfDays);

    submitBtnBooleanArr['validEmptyField'].push(checkFormInputEmptyValue());
    submitBtnBooleanArr['validToTime'].push(validateDateTime("toTime", "toTimeError"));
    submitBtnBooleanArr['validFromTime'].push(validateDateTime("fromTime", "fromTimeError"));
    submitBtnBooleanArr['validCalcTime'].push(calculateNumberOfDays());
    submitBtnBooleanArr['validDateRange'].push(validateLeaveDateAvailableRange());

    var submitButtonDisabled = Object.values(submitBtnBooleanArr).every(function (conditionArray) {
        return conditionArray.every(function (condition) {
            return condition === true;
        });
    });

    submitBtn.disabled = !submitButtonDisabled;

    // console.log(submitBtnBooleanArr);
}

//Clear A Storage Before Reload Window
window.addEventListener('beforeunload', function () {
    localStorage.clear();
});

//Remove Leave Application Table While Delete
function leave_application_dlt_btn() {
    $('#leaveApplicationTableModal').modal('hide');
}

//Remove Keyword leaveType_
function removeLeaveTypePrefix(obj) {
    var result = {};
    for (var key in obj) {
        if (key.startsWith("leaveType_")) {
            var number = key.replace("leaveType_", "");
            result[number] = obj[key];
        }
    }
    return result;
}

//Pass Leave Application To Form Edit
function postLeaveID(id) {
    setCookie("leaveAppID", id, 1);
    sessionStorage.setItem("leaveAppEdit", "true");
    location.reload();
}

//Open Add Leave Application Form
function changeLeaveApplicationForm(leaveApplicationAction) {
    var formTitle = document.getElementById("leaveApplicationFormTitle");
    var actionBtn = document.getElementById("actionBtn");

    if (leaveApplicationAction === "addLeave") {
        formTitle.textContent = "Add Leave";
        actionBtn.value = "addLeave";
    }
}

$(document).ready(function () {

    //Img Preview 
    $('#leaveAttachment').on('change', function () {
        previewImage(this, 'leaveAttachmenetImg')
    })

    if ($('#leaveAttachmenetImgValue').val()) {
        $('#leaveAttachment').prop('required', false);
    }

    //Hide Leave Application Table Show Edit Field
    setTimeout(function () {
        if (sessionStorage.getItem("leaveAppEdit")) {

            var formTitle = document.getElementById("leaveApplicationFormTitle");
            var actionBtn = document.getElementById("actionBtn");
            var leaveApplicationModal = new bootstrap.Modal(document.getElementById('leaveApplicationModal'));
            var leaveApplicationTableModal = new bootstrap.Modal(document.getElementById('leaveApplicationTableModal'));

            formTitle.textContent = "Edit Leave";
            actionBtn.value = "editLeave";
            actionBtn.textContent = "Confirm";
            leaveApplicationTableModal.hide();
            leaveApplicationModal.show();

            sessionStorage.removeItem("leaveAppEdit");
        }
    }, 50);
});

document.addEventListener("DOMContentLoaded", function () {

    var leaveApplicationModal = new bootstrap.Modal(document.getElementById('leaveApplicationModal'));
    var leaveApplicationTableModal = new bootstrap.Modal(document.getElementById('leaveApplicationTableModal'));

    //Reset a leave application form
    leaveApplicationModal._element.addEventListener('hidden.bs.modal', function () {
        location.reload();
        localStorage.clear();
        setCookie('leaveAppID', '', 0);
    });

    //Reset a leave table
    leaveApplicationTableModal._element.addEventListener('hidden.bs.modal', function () {
        var dataTable = $('#leaveApplicationTable').DataTable();
        dataTable.destroy();
        createSortingTable('leaveApplicationTable');
        datatableAlignment('leaveApplicationTable');
    });
});




//Leave Assign
$(document).ready(function ($) {
    $(document).on("change", ".leaveAssignAll", function (event) {
        event.preventDefault();

        var isChecked = $(this).prop("checked");
        $(".leaveAssign").prop("checked", isChecked);
        $(".leaveAssignAll").prop("checked", isChecked);
    });
});

$(document).ready(function () {
    $('button[name="leaveAssignBtn"]').on("click", function () {
        var checkboxValues = [];

        $(".leaveAssign:checked").each(function () {
            checkboxValues.push($(this).val());
        });

        setCookie("employeeID", checkboxValues, 1);
        sessionStorage.setItem("leaveAssignClick", "true");
        location.reload(true);
    });

    $("#assignLeaveBtn, #unassignLeaveBtn").on("click", function () {
        var assignTypeValue = $(this).val();
        setCookie("assignType", assignTypeValue, 1);
        sessionStorage.setItem("leaveAssignType", "true");
        location.reload(true);
    });

    $("#leaveAssignCheckBtn").on("click", function () {
        var checkboxValues = [];

        $(".leaveAssignCheck:checked").each(function () {
            checkboxValues.push($(this).val());
        });

        setCookie("leaveTypeSelect", checkboxValues, 1);
        sessionStorage.setItem("leaveAssignSelect", "true");
        location.reload(true);
    });

    $(".completeLeaveAssign").on("click", function () {
        setCookie("leaveTypeSelect", "", 0);
        setCookie("employeeID", "", 0);
        setCookie("assignType", "", 0);
    });

    setTimeout(function () {
        if (sessionStorage.getItem("leaveAssignClick")) {
            $("#myModal").modal("show");
            sessionStorage.removeItem("leaveAssignClick");
        } else if (sessionStorage.getItem("leaveAssignType")) {
            $("#secondModal").modal("show");
            sessionStorage.removeItem("leaveAssignType");
        } else if (sessionStorage.getItem("leaveAssignSelect")) {
            $("#thirdModal").modal("show");
            sessionStorage.removeItem("leaveAssignSelect");
        }
    }, 50);
});
