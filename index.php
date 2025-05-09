<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to store results
session_start();

// Initialize session array for results if not set
if (!isset($_SESSION['conversion_results'])) {
    $_SESSION['conversion_results'] = [];
}

function convertNumberToWords($number) {
    $words = array(
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen',
        17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
        20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
        60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    );

    $units = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];

    if ($number == 0) return 'Zero';

    $numStr = strval($number);
    $numGroups = array_reverse(str_split(str_pad($numStr, ceil(strlen($numStr) / 3) * 3, '0', STR_PAD_LEFT), 3));
    $textParts = [];

    foreach ($numGroups as $index => $group) {
        $num = intval($group);
        if ($num == 0) continue;

        $hundred = floor($num / 100);
        $remainder = $num % 100;
        $groupText = '';

        if ($hundred) {
            $groupText .= $words[$hundred] . ' Hundred';
        }
        if ($remainder) {
            if ($remainder < 20) {
                $groupText .= ($groupText ? ' ' : '') . $words[$remainder];
            } else {
                $groupText .= ($groupText ? ' ' : '') . $words[floor($remainder / 10) * 10];
                if ($remainder % 10) {
                    $groupText .= ' ' . $words[$remainder % 10];
                }
            }
        }
        if ($units[$index]) {
            $groupText .= ' ' . $units[$index];
        }
        array_unshift($textParts, $groupText);
    }

    return implode(' ', $textParts);
}

function convertNumberToKhmerWords($number) {
    $khmerWords = array(
        0 => 'សូន្យ', 1 => 'មួយ', 2 => 'ពីរ', 3 => 'បី', 4 => 'បួន',
        5 => 'ប្រាំ', 6 => 'ប្រាំមួយ', 7 => 'ប្រាំពីរ', 8 => 'ប្រាំបី', 9 => 'ប្រាំបួន',
        10 => 'ដប់', 11 => 'ដប់មួយ', 12 => 'ដប់ពីរ', 13 => 'ដប់បី',
        14 => 'ដប់បួន', 15 => 'ដប់ប្រាំ', 16 => 'ដប់ប្រាំមួយ',
        17 => 'ដប់ប្រាំពីរ', 18 => 'ដប់ប្រាំបី', 19 => 'ដប់ប្រាំបួន',
        20 => 'ម្ភៃ', 30 => 'សាមសិប', 40 => 'សែសិប', 50 => 'ហាសិប',
        60 => 'ហុកសិប', 70 => 'ចិតសិប', 80 => 'ប៉ែតសិប', 90 => 'កៅសិប'
    );

    $units = ['', 'ពាន់', 'លាន', 'ប៊ីលាន', 'ទ្រីលាន'];

    if ($number == 0) return 'សូន្យ';

    $numStr = strval($number);
    $numGroups = array_reverse(str_split(str_pad($numStr, ceil(strlen($numStr) / 3) * 3, '0', STR_PAD_LEFT), 3));
    $textParts = [];

    foreach ($numGroups as $index => $group) {
        $num = intval($group);
        if ($num == 0) continue;

        $hundred = floor($num / 100);
        $remainder = $num % 100;
        $groupText = '';

        if ($hundred) {
            $groupText .= $khmerWords[$hundred] . ' រយ';
        }
        if ($remainder) {
            if ($remainder < 20) {
                $groupText .= ($groupText ? ' ' : '') . $khmerWords[$remainder];
            } else {
                $groupText .= ($groupText ? ' ' : '') . $khmerWords[floor($remainder / 10) * 10];
                if ($remainder % 10) {
                    $groupText .= ' ' . $khmerWords[$remainder % 10];
                }
            }
        }
        if ($units[$index]) {
            $groupText .= ' ' . $units[$index];
        }
        array_unshift($textParts, $groupText);
    }

    return implode(' ', $textParts);
}

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['number'])) {
    $inputNumber = $_POST['number'];

    if (!ctype_digit($inputNumber)) {
        $error = "Please enter a valid number.";
    } else {
        $inputNumber = intval($inputNumber);
        $englishWords = convertNumberToWords($inputNumber) . ' Riel';
        $khmerWords = convertNumberToKhmerWords($inputNumber) . ' រៀល';
        $dollarAmount = $inputNumber / 4000;
        $dollars = ($dollarAmount == floor($dollarAmount)) ? number_format($dollarAmount, 0) . ' $' : number_format($dollarAmount, 2) . ' $';
        $result = "$inputNumber : $englishWords : $dollars : $khmerWords\n";

        // Store result in session
        $_SESSION['conversion_results'][] = [
            'input_number' => $inputNumber,
            'english_words' => $englishWords,
            'khmer_words' => $khmerWords,
            'dollar_amount' => $dollars,
        ];

        // Save to file (retaining original functionality)
        $file = fopen("results.txt", "a");
        fwrite($file, $result);
        fclose($file);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number to Words Converter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            width: 70%;
            margin: auto;
            box-shadow: 0px 0px 10px #aaa;
            border-radius: 10px;
        }
        input, button {
            margin: 10px;
            padding: 10px;
            width: 80%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .result {
            margin-top: 20px;
            font-size: 18px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Number to Words Converter</h2>
    <form method="post">
        <input type="number" name="number" placeholder="Enter number" required>
        <button type="submit">Convert</button>
    </form>

    <?php if (isset($result)): ?>
        <div class="result">
            <p><?php echo nl2br(htmlspecialchars($result)); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <h3>Conversion History</h3>
    <table>
        <thead>
            <tr>
                <th>Number</th>
                <th>English Words</th>
                <th>Khmer Words</th>
                <th>Dollar Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($_SESSION['conversion_results'])): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No Data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($_SESSION['conversion_results'] as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['input_number']); ?></td>
                        <td><?php echo htmlspecialchars($record['english_words']); ?></td>
                        <td><?php echo htmlspecialchars($record['khmer_words']); ?></td>
                        <td><?php echo htmlspecialchars($record['dollar_amount']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>