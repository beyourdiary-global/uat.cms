//Setup A Required Attribute For Manager Approved
function setManagerRequiredAttribute() {
  var managerSelect = JSON.parse(localStorage.getItem("managerApproveLeave"));
  var managerSelectField = document.getElementById('managerApproveLeave');

  if (managerSelect && managerSelect.length > 0) {
    managerSelectField.required = false;
  } else {
    managerSelectField.required = true;
  }
}

//Onclick Back Redirect
function clearLocalStorageAndRedirect() {
  localStorage.clear();
  window.location.href = "employeeDetailsTable.php";
}

//Multiple Form And Alert Msg

var currentTab = 0;

showTab(currentTab); // Display the current tab

function nextPrev(n) {
  var x = document.getElementsByClassName("step");

  if (!validateEmailInput()) {
    return false;
  }

  if (n == 1 && !validateForm()) {
    return false;
  }

  x[currentTab].style.display = "none";
  currentTab = currentTab + n;

  if (currentTab >= x.length) {
    if (currentTab === x.length)
      document.getElementById("nextBtn").type = "submit";

    document.getElementById("employeeDetailsForm").submit();
    return false;
  }

  showTab(currentTab);
}

function validateForm() {
  var x, y, i, valid = true;
  x = document.getElementsByClassName("step");
  y = x[currentTab].querySelectorAll("input, select");

  for (i = 0; i < y.length; i++) {

    if ((y[i].value === "" || (y[i].tagName === "SELECT" && y[i].selectedIndex === 0)) && y[i].hasAttribute("required")) {

      y[i].classList.add("invalid");
      y[i].style.borderColor = "red";
      valid = false;

      displayErrorMessage(y[i], "<p style='margin-bottom:0;'>Please fill the " + getLabelContent(y[i]) + " field.</p>");

    } else {

      y[i].classList.remove("invalid");
      y[i].style.borderColor = "";

      hideErrorMessage(y[i]);
    }
  }

  if (valid)
    document.getElementsByClassName("stepIndicator")[currentTab].classList.add("finish");

  return valid;
}


function showTab(n) {
  var x = document.getElementsByClassName("step");
  var editButton = document.getElementById("editButton");
  x[n].style.display = "block";

  if (n == 0)
    document.getElementById("prevBtn").style.display = "none";
  else
    document.getElementById("prevBtn").style.display = "inline";

  if (n == x.length - 1) {

    if (!action) {
      document.getElementById("nextBtn").style.display = "none";
    } else {
      document.getElementById("nextBtn").innerHTML = "<?php echo $buttonText; ?>";
      document.getElementById("nextBtn").value = "<?php echo $buttonValue; ?>";
    }

    // Show editButton when $act is 'E' and not on the last step
    if (action === "E" && n !== x.length - 1)
      editButton.style.display = "block";
    else
      editButton.style.display = "none";

  } else {
    document.getElementById("nextBtn").innerHTML = "Next";
    document.getElementById("nextBtn").style.display = "";
  }

  fixStepIndicator(n);
}

function displayErrorMessage(inputField, message) {

  var errorMessageElement = inputField.nextElementSibling;

  if (
    !errorMessageElement ||
    errorMessageElement.className !== "error-message"
  ) {
    errorMessageElement = document.createElement("span");
    errorMessageElement.className = "error-message";
    errorMessageElement.style.color = "red";
    errorMessageElement.style.display = "block";
    inputField.parentNode.appendChild(errorMessageElement);
  }

  errorMessageElement.innerHTML = message;
}

function hideErrorMessage(inputField) {
  var errorMessageElement = inputField.nextElementSibling;
  if (
    errorMessageElement &&
    errorMessageElement.className === "error-message"
  ) {
    errorMessageElement.parentNode.removeChild(errorMessageElement);
  }
}

function getLabelContent(inputField) {
  var label = document.querySelector('[for="' + inputField.id + '"]');
  return label ? label.childNodes[0].nodeValue.trim() : "this field";
}

function fixStepIndicator(n) {
  var i, x = document.getElementsByClassName("stepIndicator");

  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }

  x[n].className += " active";
}
//Multiple Form And Alert Msg


document.addEventListener("DOMContentLoaded", function () {

  //Manager Approved Input Field Control
  function checkSelectRequired() {
    var selectElement = document.getElementById("managerApproveLeave");
    var isSelectEmpty = selectElement.selectedOptions.length === 0;

    if (isSelectEmpty)
      selectElement.required = true;
    else
      selectElement.required = false;
  }
  checkSelectRequired();


  //Disable Edit Button When Employee Details No In Edit Mode
  var editButton = document.querySelector('[name="actionBtn"][value="updEmpDetails"]');

  if (action !== "E")
    editButton.style.display = "none";


  //Disabled the number of child input when marital status is single
  function updateNoOfChildInput() {
    var maritalStatusDropdown = document.getElementById("maritalStatus");
    var noOfChildInput = document.getElementById("noOfChild");

    if (maritalStatusDropdown.value === "2") {
      noOfChildInput.disabled = false;
      noOfChildInput.required = true;
    } else {
      noOfChildInput.disabled = true;
      noOfChildInput.value = "";
      noOfChildInput.required = false;
    }
  }

  document.getElementById("maritalStatus").addEventListener("change", updateNoOfChildInput);
  updateNoOfChildInput();


  //Update A Phone Code 
  function updatePhoneCode() {
    var selectedCountry = document.getElementById("employeeNationality").options[document.getElementById("employeeNationality").selectedIndex];

    var phoneCode = selectedCountry.getAttribute("data-phone-code");
    document.getElementById("phoneCodeSpan").textContent = phoneCode;
    document.getElementById("alternatePhoneCodeSpan").textContent = phoneCode;
    document.getElementById("emergencyContactNumSpan").textContent = phoneCode;
  }

  document.getElementById("employeeNationality").addEventListener("change", updatePhoneCode);
  updatePhoneCode();


  //Add Readonly Attribute To All Input/Select Field When Is VIew Mode
  var formElements = document.querySelectorAll(".form-select, input, textarea");

  formElements.forEach(function (element) {
    if (action === "") {
      if (element.tagName.toLowerCase() === "select" && element.classList.contains("form-select"))
        element.disabled = true;
      if ((element.tagName.toLowerCase() === "input" || element.tagName.toLowerCase() === "textarea") && element.type !== "file")
        element.readOnly = true;
    }
  });


  //To Control A Employee/Employer Epf Rate
  var epfOptionDropdown = document.getElementById("epfOption");
  var epfNoInput = document.getElementById("epfNo");
  var employeeEpfRateSelect = document.getElementById("employeeEpfRate");
  var employerEpfRateSelect = document.getElementById("employerEpfRate");

  function updateEpfNoField() {

    if (epfOptionDropdown.value === "No")
      clearEpfFields();

    var isEpfYes = epfOptionDropdown.value === "Yes";
    updateEpfField(isEpfYes, isEpfYes, "Employee EPF Rate", employeeEpfRateSelect);
    updateEpfField(isEpfYes, isEpfYes, "Employer EPF Rate", employerEpfRateSelect);
    updateEpfField(isEpfYes, isEpfYes, "Contributing EPF No", epfNoInput);

  }

  function clearEpfFields() {
    epfNoInput.value = "";
    employeeEpfRateSelect.selectedIndex = 0;
    employerEpfRateSelect.selectedIndex = 0;
  }

  function updateEpfField(enabled, required, label, input) {
    input.required = required;
    input.disabled = !enabled;
    label.innerHTML = label.textContent + (required ? '<span class="requireRed">*</span>' : "");
  }

  epfOptionDropdown.addEventListener("change", updateEpfNoField);
  updateEpfNoField();


  //Residence And Nationality
  function updateEmpNationality() {
    var nationalitySelect = document.getElementById("employeeNationality");
    var nationalityHiddenInput = document.getElementById("nationality");
    var residenceStatusSelect = document.getElementById("employeeResidenceStatus");

    nationalitySelect.addEventListener("change", function () {
      nationalityHiddenInput.value = nationalitySelect.value;
      localStorage.setItem(nationalityHiddenInput.id, nationalityHiddenInput.value);
    });

    if (residenceStatusSelect.value === 'Resident') {
      for (var i = 0; i < nationalitySelect.options.length; i++) {
        if (nationalitySelect.options[i].text === "MALAYSIA") {

          nationalitySelect.options[i].selected = true;
          nationalitySelect.disabled = true;
          document.getElementById("nationality").value = nationalitySelect.options[i].value;

          updatePhoneCode();
          break;
        }
      }
    } else {
      nationalitySelect.disabled = false;
    }
  }

  document.getElementById("employeeResidenceStatus").addEventListener("change", updateEmpNationality);
  updateEmpNationality();

  function getSelectedOptionsArray(id) {

    var selectElement = document.getElementById(id);
    var selectedOptions = [];

    for (var i = 0; i < selectElement.options.length; i++) {
      if (selectElement.options[i].selected) {
        selectedOptions.push(selectElement.options[i].value);
      }
    }
    return selectedOptions;
  }

  //Manager Approved Save LocalStorage
  var manager;

  if (action) {
    $('#managerApproveLeave').on('change', function () {
      manager = getSelectedOptionsArray('managerApproveLeave');
      localStorage.setItem("managerApproveLeave", JSON.stringify(manager));
    });
  }

  if (action !== 'E' && action !== '') {
    localStorage.setItem("managerEditModeCount", true);
  }

  if (!localStorage.getItem("managerEditModeCount")) {
    if (managerAssignJSON) {
      var managerSelect = managerAssignJSON;
      localStorage.setItem("managerApproveLeave", JSON.stringify(managerAssignJSON));
      if (action)
        localStorage.setItem("managerEditModeCount", true);
    }
  } else {
    var managerSelect = JSON.parse(localStorage.getItem("managerApproveLeave"));
  }

  if (managerSelect) {
    if (managerSelect.length) {
      $("#managerApproveLeave").val(managerSelect).trigger('change');
      if (!action)
        localStorage.removeItem('managerApproveLeave');
    }
  }
});

function validateEmailInput() {
  var emailInput = $("#employeeEmail");
  var emailMsg = $("#emailMsg1");

  if (!emailInput.val()) {
    emailInput.css("border-color", "red");
    emailMsg.html("<p style='color:red;margin-bottom:0'>Email is required!</p>");
    return false
  } else if (!validateEmail('#employeeEmail')) {
    emailInput.css("border-color", "red");
    emailMsg.html("<p style='color:red;margin-bottom:0;'>Invalid Email Format</p>");
    return false
  }

  emailInput.css("border-color", "");
  emailMsg.html("");
  return true

}

function validateEmail(inputID) {

  var email = $(inputID).val();
  var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;

  if (reg.test(email))
    return true;
  else
    return false;
}
