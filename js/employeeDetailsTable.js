//Leave Application
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

function changeLeaveApplicationForm(leaveApplicationAction) {
  var formTitle = document.getElementById("leaveApplicationFormTitle");
  var actionBtn = document.getElementById("actionBtn");
  var leaveApplicationModal = new bootstrap.Modal(document.getElementById('leaveApplicationModal')); 
  var leaveApplicationTableModal = new bootstrap.Modal(document.getElementById('leaveApplicationTableModal'));

  if (leaveApplicationAction === "addLeave") {
    formTitle.textContent = "Add Leave";
    actionBtn.value = "addLeave";
  } else if (leaveApplicationAction === "editLeave") {
    formTitle.textContent = "Edit Leave";
    actionBtn.value = "editLeave";
    leaveApplicationTableModal.hide();
    leaveApplicationModal.show(); 

    
  }
}



function updateRemainingLeaves() {
  var selectedLeaveId = document.getElementById("leaveType").value;
  var remainingLeave;

  if (leaveTypes.hasOwnProperty(selectedLeaveId)) {
    remainingLeave = leaveTypes[selectedLeaveId];
  } else {
    remainingLeave = 0;
  }

  return remainingLeave;
}

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
