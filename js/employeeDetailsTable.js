//Leave Application

window.addEventListener('beforeunload', function() {
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
