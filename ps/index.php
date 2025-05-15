<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Text Converter</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    input[type="text"] {
      width: calc(100% - 20px);
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px 0 0 5px;
    }

    button {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      cursor: pointer;
      border-radius: 5px;
    }

    .converted-text {
      width: calc(100% - 20px);
      height: 30px;
      margin-top: 20px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      overflow-wrap: break-word;
    }

    .button-group {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 10px;
    }

    .message {
      margin-top: 10px;
      font-style: italic;
      color: green;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Text Converter</h2>
    <input type="text" id="inputText" placeholder="Enter your text..." />
    <div class="button-group">
      <button onclick="convertText()">Convert</button>
      <button onclick="copyToClipboard()">Copy</button>
      <button onclick="clearFields()">Clear</button>
    </div>
    <div class="converted-text" id="convertedText"></div>
    <div class="message" id="messageBox"></div>
  </div>

  <script>
    function convertText() {
      const input = document.getElementById('inputText').value;
      // Remove parentheses, dashes, and other specified characters
      const cleaned = input.replace(/[&[\]/:()-]/g, '');
      const converted = cleaned.replace(/\s+/g, '-');
      document.getElementById('convertedText').textContent = converted;
      showMessage('Text converted!');
    }


    function copyToClipboard() {
      const text = document.getElementById('convertedText').textContent;
      if (!text) {
        showMessage('Nothing to copy.');
        return;
      }

      const textarea = document.createElement('textarea');
      textarea.value = text;
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
      showMessage('Converted text copied to clipboard!');
    }

    function clearFields() {
      document.getElementById('inputText').value = '';
      document.getElementById('convertedText').textContent = '';
      showMessage('Cleared!');
    }

    function showMessage(message) {
      const messageBox = document.getElementById('messageBox');
      messageBox.textContent = message;
      setTimeout(() => {
        messageBox.textContent = '';
      }, 2000);
    }
  </script>
</body>
</html>
