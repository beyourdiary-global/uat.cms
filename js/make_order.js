document.addEventListener("DOMContentLoaded", function () {

    const hasValidationElements = document.querySelectorAll('.has-validation');

    hasValidationElements.forEach(function (container) {
        const inputField = container.querySelector('input, select');
        const errorMessage = container.querySelector('.invalid-msg');

        if (inputField && inputField.hasAttribute('required')) {
            errorMessage.style.display = 'none';

            inputField.addEventListener('input', function () {
                if (inputField.validity.valid) {
                    errorMessage.style.display = 'none';
                    inputField.style.borderColor = '';
                } else {
                    errorMessage.style.display = 'block';
                }
            });
        }
    });

    document.getElementById('makeOrderForm').addEventListener('submit', function (event) {
        hasValidationElements.forEach(function (container) {
            const inputField = container.querySelector('input, select');
            const errorMessage = container.querySelector('.invalid-msg');

            if (inputField && !inputField.validity.valid) {
                errorMessage.style.display = 'block';
                inputField.style.borderColor = 'red';
                event.preventDefault();
            }
        });
    });
});

$("#makeOrderForm").on("submit", function () {

    var errorExist = false;
    var errorExist_2 = false;
    var errorExist_3 = false;

    if (!$("#sender_email").val()) {
        $("#emailMsg1").html("<p style='color:red'>Please enter sender email</p>");
        $("#sender_email").css("border-color", "red");
        errorExist = true;
    } else if (!validateEmail('#sender_email')) {
        $("#emailMsg1").html("<p style='color:red'>Invalid email format</p>");
        $("#sender_email").css("border-color", "red");
        errorExist = true;
    } else {
        $("#emailMsg1").html("");
        $("#sender_email").css("border-color", "");
    }

    if (!$("#receiver_email").val()) {
        $("#emailMsg2").html("<p style='color:red'>Please enter receiver email</p>");
        $("#receiver_email").css("border-color", "red");
        errorExist = true;
    } else if (!validateEmail('#receiver_email')) {
        $("#emailMsg2").html("<p style='color:red'>Invalid email format</p>");
        $("#receiver_email").css("border-color", "red");
        errorExist = true;
    } else {
        $("#emailMsg2").html("");
        $("#receiver_email").css("border-color", "");
    }

    if (!$("#sender_tel").val()) {
        $("#telMsg1").html("<p style='color:red'>Please enter sender phone number</p>");
        $("#sender_tel").css("border-color", "red");
        errorExist = true;
    } else if (!validatePhoneNum('#sender_tel', '#sender_country')) {
        $("#telMsg1").html("<p style='color:red'>Invalid phone number</p>");
        $("#sender_tel").css("border-color", "red");
        errorExist = true;
    } else {
        $("#telMsg1").html("");
        $("#sender_tel").css("border-color", "");
    }

    if (!validatePhoneNum('#sender_alt_tel', '#sender_country')) {
        $("#telMsg2").html("<p style='color:red'>Invalid phone number</p>");
        $("#sender_alt_tel").css("border-color", "red");
        errorExist_2 = true;
    } else {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
    }

    if ($('#sender_alt_tel').val() === '') {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
        errorExist_2 = false;
    }

    if (!$("#receiver_tel").val()) {
        $("#telMsg3").html("<p style='color:red'>Please enter receiver phone number</p>");
        $("#receiver_tel").css("border-color", "red");
        errorExist = true;
    } else if (!validatePhoneNum('#receiver_tel', '#receiver_country')) {
        $("#telMsg3").html("<p style='color:red'>Invalid phone number</p>");
        $("#receiver_tel").css("border-color", "red");
        errorExist = true;
    } else {
        $("#telMsg3").html("");
        $("#receiver_tel").css("border-color", "");
    }

    if (!validatePhoneNum('#receiver_alt_tel', '#receiver_country')) {
        $("#telMsg4").html("<p style='color:red'>Invalid phone number</p>");
        $("#receiver_alt_tel").css("border-color", "red");
        errorExist_3 = true;
    } else {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
    }

    if ($('#receiver_alt_tel').val() === '') {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
        errorExist_3 = false;
    }

    if (!$("#pickup_date").val()) {
        $("#dateMsg5").html("<p style='color:red'>Please enter parcel pick up date</p>");
        $("#pickup_date").css("border-color", "red");
        errorExist = true;
    } else if (!validateDate('#pickup_date')) {
        $("#dateMsg5").html("<p style='color:red'>Invalid pick up date</p>");;
        $("#pickup_date").css("border-color", "red");
        errorExist = true;
    } else {
        $("#dateMsg5").html("");
        $("#pickup_date").css("border-color", "");
    }

    if (errorExist || errorExist_2 || errorExist_3)
        return false;
});


$("#sender_email").on("input", function () {
    if (!$("#sender_email").val()) {
        $("#emailMsg1").html("<p style='color:red'>Please enter sender email</p>");
    } else if (!validateEmail('#sender_email')) {
        $("#emailMsg1").html("<p style='color:red'>Invalid email format</p>");
        $("#sender_email").css("border-color", "red");
    } else {
        $("#emailMsg1").html("");
        $("#sender_email").css("border-color", "");
    }
});

$("#receiver_email").on("input", function () {
    if (!$("#receiver_email").val()) {
        $("#emailMsg2").html("<p style='color:red'>Please enter receiver email</p>");
    } else if (!validateEmail('#receiver_email')) {
        $("#emailMsg2").html("<p style='color:red'>Invalid email format</p>");
        $("#receiver_email").css("border-color", "red");
    } else {
        $("#emailMsg2").html("");
        $("#receiver_email").css("border-color", "");
    }
});

$("#sender_tel").on("input", function () {
    if (!$("#sender_tel").val()) {
        $("#telMsg1").html("<p style='color:red'>Please enter sender phone number</p>");
    } else if (!validatePhoneNum('#sender_tel', '#sender_country')) {
        $("#telMsg1").html("<p style='color:red'>Invalid phone number</p>");
        $("#sender_tel").css("border-color", "red");
    } else {
        $("#telMsg1").html("");
        $("#sender_tel").css("border-color", "");
    }
});

$("#sender_alt_tel").on("input", function () {
    if (!validatePhoneNum('#sender_alt_tel', '#receiver_country')) {
        $("#telMsg2").html("<p style='color:red'>Invalid phone number</p>");
        $("#sender_alt_tel").css("border-color", "red");
    } else {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
    }

    if ($('#sender_alt_tel').val() === '') {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
    }
});

$("#receiver_tel").on("input", function () {
    if (!$("#receiver_tel").val()) {
        $("#telMsg3").html("<p style='color:red'>Please enter receiver phone number</p>");
    } else if (!validatePhoneNum('#receiver_tel', '#receiver_country')) {
        $("#telMsg3").html("<p style='color:red'>Invalid phone number</p>");
        $("#receiver_tel").css("border-color", "red");
    } else {
        $("#telMsg3").html("");
        $("#receiver_tel").css("border-color", "");
    }
});

$("#receiver_alt_tel").on("input", function () {
    if (!validatePhoneNum('#receiver_alt_tel', '#receiver_country')) {
        $("#telMsg4").html("<p style='color:red'>Invalid phone number</p>");
        $("#receiver_alt_tel").css("border-color", "red");
    } else {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
    }

    if ($('#receiver_alt_tel').val() === '') {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
    }
});

$("#pickup_date").on("input", function () {
    if (!$("#pickup_date").val()) {
        $("#dateMsg5").html("<p style='color:red'>Please enter parcel pick up date</p>");
    } else if (!validateDate('#pickup_date')) {
        $("#dateMsg5").html("<p style='color:red'>Invalid pick up date</p>");
        $("#pickup_date").css("border-color", "red");
    } else {
        $("#dateMsg5").html("");
        $("#pickup_date").css("border-color", "");
    }
});

function validateEmail(inputID) {
    // get value of input email
    var email = $(inputID).val();
    // use reular expression
    var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/

    if (reg.test(email))
        return true;
    else
        return false;
}

function validatePhoneNum(inputID, countryID) {
    var phoneNum = $(inputID).val();
    var country = $(countryID).val();

    country = country.toLowerCase();
    var reg;

    if (country === 'malaysia') {
        reg = /^(\+?6?01)[02-46-9]-*[0-9]{7}$|^(\+?6?01)[1]-*[0-9]{8}$/;
        phoneNum = '60' + phoneNum.replace(/\s/g, '');
    } else if (country === 'singapore') {
        reg = /65[6|8|9]\d{7}/g;
        phoneNum = '65' + phoneNum.replace(/\s/g, '');
    }

    if (reg) {
        if (reg.test(phoneNum))
            return true;
        else
            return false;
    } else {
        return true;
    }
}

function validateDate(inputID) {
    var date = $(inputID).val();

    var currentDate = new Date();
    var parsedInputDate = new Date(date);

    if (parsedInputDate > currentDate)
        return true;
    else
        return false;

}