// JavaScript code to update the color code display
const colorInput = document.getElementById("segmentationColor");
const colorDisplay = document.getElementById("color-display");

// Add an event listener to the color input
colorInput.addEventListener("input", function() {
    const selectedColor = colorInput.value;
    colorDisplay.textContent = selectedColor;
});

function validateNumericInput(inputField, errorMsgId, otherErrorMsgId) {
    const inputValue = inputField.value;
    const numericValue = parseFloat(inputValue);

    if (isNaN(numericValue)) {
        inputField.value = inputValue.replace(/[^0-9.]/g, '');
    }

    const currentErrorMsg = document.getElementById(errorMsgId);
    const otherErrorMsg = document.getElementById(otherErrorMsgId);

    if (isNaN(parseFloat(document.getElementById("boxFrom").value)) && isNaN(parseFloat(document.getElementById("boxUntil").value))) {
        currentErrorMsg.textContent = "Please enter a number.";
        currentErrorMsg.classList.add("error-message");
        otherErrorMsg.textContent = "";
        otherErrorMsg.classList.remove("error-message");
    } else {
        currentErrorMsg.textContent = "";
        currentErrorMsg.classList.remove("error-message");
    }
}

//autocomplete
$(document).ready(function() {

    if (!($("#brandSeries").attr('disabled'))) {
        $("#brandSeries").keyup(function() {
            var param = {
                search: $(this).val(),
                searchType: 'name', // column of the table
                elementID: $(this).attr('id'), // id of the input
                hiddenElementID: $(this).attr('id') + '_hidden', // hidden input for storing the value
                dbTable: '<?= BRD_SERIES ?>', // json filename (generated when login)
            }
            console.log("Element ID:", param["elementID"]);
            console.log("Site URL:", '<?= $SITEURL ?>');
            searchInput(param, '<?= $SITEURL ?>');
        });
    }
})