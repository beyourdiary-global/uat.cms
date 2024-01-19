document.addEventListener("DOMContentLoaded", function() {

    const hasValidationElements = document.querySelectorAll('.has-validation');

    hasValidationElements.forEach(function(container) {
        const inputField = container.querySelector('input, select');
        const errorMessage = container.querySelector('.invalid-msg');

        if (inputField && inputField.hasAttribute('required')) {
            errorMessage.style.display = 'none';

            inputField.addEventListener('input', function() {
                if (inputField.validity.valid) {
                    errorMessage.style.display = 'none';
                    inputField.style.borderColor = '';
                } else {
                    errorMessage.style.display = 'block';
                }
            });
        }
    });

    document.getElementById('makeOrderForm').addEventListener('submit', function(event) {
        hasValidationElements.forEach(function(container) {
            const inputField = container.querySelector('input, select');
            const errorMessage = container.querySelector('.invalid-msg');

            if (inputField && !inputField.validity.valid) {
                errorMessage.style.display = 'block';
                inputField.style.borderColor = 'red';
                event.preventDefault();
            }

            if (!validateEmail('#sender_email') || !validateEmail('#receiver_email')) {
                event.preventDefault();
            }

            if (!validatePhoneNum('#sender_tel', '#sender_country') || !validatePhoneNum('#receiver_tel', '#receiver_country')) {
                event.preventDefault();
            }

            if ($('#sender_alt_tel').val() !== '') {
                if (!validatePhoneNum('#sender_alt_tel', '#sender_country')) {
                    event.preventDefault();
                }
            }

            if ($('#receiver_alt_tel').val() !== '') {
                if (!validatePhoneNum('#receiver_alt_tel', '#receiver_country')) {
                    event.preventDefault();
                }
            }

            if (!validateDate('#pickup_date')) {
                event.preventDefault();
            }
        });
    });
});

$("#makeOrderForm").on("submit", function() {
    if (!validateEmail('#sender_email')) {
        $("#emailMsg1").html("<p style='color:red'>Invalid Email Format</p>");
        $("#sender_email").css("border-color", "red");
    } else {
        $("#emailMsg1").html("");
        $("#sender_email").css("border-color", "");
    }


    if (!validateEmail('#receiver_email')) {
        $("#emailMsg2").html("<p style='color:red'>Invalid Email Format</p>");
        $("#receiver_email").css("border-color", "red");
    } else {
        $("#emailMsg2").html("");
        $("#receiver_email").css("border-color", "");
    }

    if (!validatePhoneNum('#sender_tel', '#sender_country')) {
        $("#telMsg1").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#sender_tel").css("border-color", "red");
    } else {
        $("#telMsg1").html("");
        $("#sender_tel").css("border-color", "");
    }


    if (!validatePhoneNum('#sender_alt_tel', '#receiver_country')) {
        $("#telMsg2").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#sender_alt_tel").css("border-color", "red");
    } else {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
    }

    if ($('#sender_alt_tel').val() === '') {
        $("#telMsg2").html("");
        $("#sender_alt_tel").css("border-color", "");
    }


    if (!validatePhoneNum('#receiver_tel', '#receiver_country')) {
        $("#telMsg3").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#receiver_tel").css("border-color", "red");
    } else {
        $("#telMsg3").html("");
        $("#receiver_tel").css("border-color", "");
    }

    if (!validatePhoneNum('#receiver_alt_tel', '#receiver_country')) {
        $("#telMsg4").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#receiver_alt_tel").css("border-color", "red");
    } else {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
    }

    if ($('#receiver_alt_tel').val() === '') {
        $("#telMsg4").html("");
        $("#receiver_alt_tel").css("border-color", "");
    }

    if (!validateDate('#pickup_date')) {
        $("#dateMsg5").html("<p style='color:red'>Invalid Pick Up Date</p>");;
        $("#pickup_date").css("border-color", "red");
    } else {
        $("#dateMsg5").html("");
        $("#pickup_date").css("border-color", "");
    }
});


$("#sender_email").on("input", function() {
    if (!validateEmail('#sender_email')) {
        $("#emailMsg1").html("<p style='color:red'>Invalid Email Format</p>");
        $("#sender_email").css("border-color", "red");
    } else {
        $("#emailMsg1").html("");
        $("#sender_email").css("border-color", "");
    }
});

$("#receiver_email").on("input", function() {
    if (!validateEmail('#receiver_email')) {
        $("#emailMsg2").html("<p style='color:red'>Invalid Email Format</p>");
        $("#receiver_email").css("border-color", "red");
    } else {
        $("#emailMsg2").html("");
        $("#receiver_email").css("border-color", "");
    }
});

$("#sender_tel").on("input", function() {
    if (!validatePhoneNum('#sender_tel', '#sender_country')) {
        $("#telMsg1").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#sender_tel").css("border-color", "red");
    } else {
        $("#telMsg1").html("");
        $("#sender_tel").css("border-color", "");
    }
});

$("#sender_alt_tel").on("input", function() {
    if (!validatePhoneNum('#sender_alt_tel', '#receiver_country')) {
        $("#telMsg2").html("<p style='color:red'>Invalid Phone Number</p>");
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

$("#receiver_tel").on("input", function() {
    if (!validatePhoneNum('#receiver_tel', '#receiver_country')) {
        $("#telMsg3").html("<p style='color:red'>Invalid Phone Number</p>");
        $("#receiver_tel").css("border-color", "red");
    } else {
        $("#telMsg3").html("");
        $("#receiver_tel").css("border-color", "");
    }
});

$("#receiver_alt_tel").on("input", function() {
    if (!validatePhoneNum('#receiver_alt_tel', '#receiver_country')) {
        $("#telMsg4").html("<p style='color:red'>Invalid Phone Number</p>");
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

$("#pickup_date").on("input", function() {
    if (!validateDate('#pickup_date')) {
        $("#dateMsg5").html("<p style='color:red'>Invalid Pick Up Date</p>");
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

    if (reg.test(phoneNum))
        return true;
    else
        return false;

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