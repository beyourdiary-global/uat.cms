var currentTab = 0;

showTab(currentTab); // Display the current tab

function nextPrev(n) {
    var x = document.getElementsByClassName("step");

    if (n == 1 && !validateForm()) return false;

    x[currentTab].style.display = "none";
    currentTab = currentTab + n;

    if (currentTab >= x.length) {
        if (currentTab === x.length) {
            document.getElementById("nextBtn").type = "submit";
        }
        document.getElementById("employeeDetailsForm").submit();
        return false;
    }

    showTab(currentTab);
}

function showTab(n) {


    var x = document.getElementsByClassName("step");
    var editButton = document.getElementById("editButton");
    x[n].style.display = "block";

    if (n == 0) {
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 1)) {
        if ('<?php echo $act; ?>' === '') {
            document.getElementById("nextBtn").style.display = "none";
        } else {
            document.getElementById("nextBtn").innerHTML = "<?php echo $buttonText; ?>";
            document.getElementById("nextBtn").value = "<?php echo $buttonValue; ?>";
        }

        // Show editButton when $act is 'E' and not on the last step
        if ('<?php echo $act; ?>' === 'E' && n !== (x.length - 1)) {
            editButton.style.display = "block";
        } else {
            editButton.style.display = "none";
        }
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
    }

    fixStepIndicator(n);
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

            displayErrorMessage(y[i], "Please fill the " + getLabelContent(y[i]) + " field.");
        } else {
            y[i].classList.remove("invalid");
            y[i].style.borderColor = "";

            hideErrorMessage(y[i]);
        }
    }

    if (valid) {
        document.getElementsByClassName("stepIndicator")[currentTab].classList.add("finish");
    }

    return valid;
}

function displayErrorMessage(inputField, message) {

    var errorMessageElement = inputField.nextElementSibling;

    if (!errorMessageElement || errorMessageElement.className !== "error-message") {
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
    if (errorMessageElement && errorMessageElement.className === "error-message") {
        errorMessageElement.parentNode.removeChild(errorMessageElement);
    }
}

function getLabelContent(inputField) {
    // Find the associated label element
    var label = document.querySelector('[for="' + inputField.id + '"]');

    // Get the content of the label excluding any child elements
    return label ? label.childNodes[0].nodeValue.trim() : "this field";
}


function fixStepIndicator(n) {
    var i, x = document.getElementsByClassName("stepIndicator");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" active", "");
    }
    x[n].className += " active";
}

document.addEventListener('DOMContentLoaded', function() {

    var formElements = document.querySelectorAll('.form-control, .form-select');
    var currentPage = "<?php echo $buttonValue; ?>";
    var previousPage = localStorage.getItem('previousPage');

    if (currentPage != previousPage) {
        localStorage.clear();
        localStorage.setItem('previousPage', currentPage);
    }

    formElements.forEach(function(element) {
        var savedValue = getSavedValue(element.id);

        if (savedValue !== null && savedValue !== '0') {
            element.value = savedValue;
        }

        element.addEventListener((element.tagName === 'SELECT') ? 'change' : 'input', function() {
            if (this.value !== null && this.value !== '0') {
                saveValue(this);
            }
        });
    });

    function saveValue(e) {
        var id = e.id;
        var val = (e.tagName === 'SELECT') ? e.options[e.selectedIndex].value : e.value;
        localStorage.setItem(id, val);
    }

    function getSavedValue(v) {
        return localStorage.getItem(v);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var maritalStatusDropdown = document.getElementById('maritalStatus');
    var noOfChildInput = document.getElementById('noOfChild');

    function updateNoOfChildInput() {
        if (maritalStatusDropdown.value === '2') {
            noOfChildInput.disabled = false;
        } else {
            noOfChildInput.disabled = true;
            noOfChildInput.value = '';
        }
    }

    maritalStatusDropdown.addEventListener('change', updateNoOfChildInput);

    updateNoOfChildInput();
});

document.addEventListener('DOMContentLoaded', function() {

    var epfOptionDropdown = document.getElementById('epfOption');
    var epfNoInput = document.getElementById('epfNo');
    var epfNoInputLbl = document.getElementById('epfNoLbl');
    var employeeEpfRateSelect = document.getElementById('employeeEpfRate');
    var employerEpfRateSelect = document.getElementById('employerEpfRate');
    var employeeEpfRateLabel = document.getElementById('employeeEpfRateLbl');
    var employerEpfRateLabel = document.getElementById('employerEpfRateLbl');

    function updateEpfFields(enabled, required, label, input) {
        input.required = required;
        input.disabled = !enabled;
        label.innerHTML = label.textContent + (required ? '<span class="requireRed">*</span>' : '');
    }

    function updateEpfNoField() {
        var isEpfYes = epfOptionDropdown.value === 'Yes';

        updateEpfFields(isEpfYes, isEpfYes, 'Employee EPF Rate', employeeEpfRateSelect);
        updateEpfFields(isEpfYes, isEpfYes, 'Employer EPF Rate', employerEpfRateSelect);
        updateEpfFields(isEpfYes, isEpfYes, 'Contributing EPF No', epfNoInput);
    }

    epfOptionDropdown.addEventListener('change', updateEpfNoField);

    updateEpfNoField();
});

document.addEventListener('DOMContentLoaded', function() {
    var epfOptionDropdown = document.getElementById('epfOption');
    var epfNoInput = document.getElementById('epfNo');
    var employeeEpfRateSelect = document.getElementById('employeeEpfRate');
    var employerEpfRateSelect = document.getElementById('employerEpfRate');

    function clearEpfFields() {
        epfNoInput.value = '';
        employeeEpfRateSelect.selectedIndex = 0;
        employerEpfRateSelect.selectedIndex = 0;
    }

    function updateEpfNoField() {
        if (epfOptionDropdown.value === 'No') {
            clearEpfFields();
        }
    }

    epfOptionDropdown.addEventListener('change', updateEpfNoField);

    updateEpfNoField();
});

document.addEventListener('DOMContentLoaded', function() {
    var formElements = document.querySelectorAll('.form-select, input, textarea');

    formElements.forEach(function(element) {
        if ('<?php echo $act; ?>' === '') {
            if (element.tagName.toLowerCase() === 'select' && element.classList.contains('form-select')) {
                element.disabled = true;
            }
            if ((element.tagName.toLowerCase() === 'input' || element.tagName.toLowerCase() === 'textarea') && element.type !== 'file') {
                element.readOnly = true;
            }
        }
    });
});
/*
document.addEventListener('DOMContentLoaded', function() {
    var residenceStatusSelect = document.getElementById("employeeResidenceStatus");
    var nationalitySelect = document.getElementById("employeeNationality");

    function updataEmpNationality() {
        for (var i = 0; i < nationalitySelect.options.length; i++) {
            if (nationalitySelect.options[i].text === "MALAYSIA") {
                nationalitySelect.options[i].selected = true;
                nationalitySelect.disabled = true;

                var phoneCode = nationalitySelect.options[i].getAttribute('data-phone-code');
                document.getElementById('phoneCodeSpan').textContent = phoneCode;
                document.getElementById('alternatePhoneCodeSpan').textContent = phoneCode;
                document.getElementById('emergencyContactNumSpan').textContent = phoneCode;

                break;
            }
        }
    }

    residenceStatusSelect.value = localStorage.getItem('employeeResidenceStatus') || residenceStatusSelect.value;

    if (residenceStatusSelect.value === "Resident") {
        updataEmpNationality();
    } else {
        nationalitySelect.disabled = false;
    }

    residenceStatusSelect.addEventListener("change", function() {
        if (residenceStatusSelect.value === "Resident") {
            updataEmpNationality();
            localStorage.setItem('employeeResidenceStatus', residenceStatusSelect.value);
            localStorage.setItem('employeeNationality', nationalitySelect.value);
        } else {
            nationalitySelect.disabled = false;
        }
    });
});
*/
document.addEventListener('DOMContentLoaded', function() {

    function updatePhoneCode() {
        var selectedCountry = document.getElementById('employeeNationality').options[document.getElementById('employeeNationality').selectedIndex];

        var phoneCode = selectedCountry.getAttribute('data-phone-code');
        document.getElementById('phoneCodeSpan').textContent = phoneCode;
        document.getElementById('alternatePhoneCodeSpan').textContent = phoneCode;
        document.getElementById('emergencyContactNumSpan').textContent = phoneCode;
    }

    document.getElementById('employeeNationality').addEventListener('change', updatePhoneCode);

    updatePhoneCode();
});