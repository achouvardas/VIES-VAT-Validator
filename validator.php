<?php
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vatNumber = $_POST['vat_number'];
    $countryCode = $_POST['country_code'];

    // VIES API endpoint
    $url = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    // Create SOAP client
    $client = new SoapClient($url);

    // Prepare the request parameters
    $params = [
        'countryCode' => $countryCode,
        'vatNumber' => $vatNumber,
    ];

    try {
        // Call the VIES API
        $response = $client->checkVat($params);

        // Extract the response
        $valid = $response->valid;
        $name = $response->name;
        $address = $response->address;

        // Split the name into Trade Name and Business Name if the separator exists
        $nameParts = explode('||', $name);
        $tradeName = trim($nameParts[0] ?? '');
        $businessName = trim($nameParts[1] ?? '');

        // Additional check for Greek VAT numbers
        $gemiInfo = null;
        $gemiLink = null;
        if ($countryCode === 'EL' && $valid) {
            $greekVat = ltrim($vatNumber, '0'); // Remove leading zeros if any
            $gemiApiUrl = "https://publicity.businessportal.gr/api/autocomplete/{$greekVat}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $gemiApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only, consider proper SSL verification in production
            $gemiResponse = curl_exec($ch);
            curl_close($ch);
            
            if ($gemiResponse) {
                $gemiData = json_decode($gemiResponse, true);
                if (isset($gemiData['payload']['autocomplete'][0]['arGemi'])) {
                    $gemiInfo = $gemiData['payload']['autocomplete'][0];
                    $gemiLink = "https://publicity.businessportal.gr/company/" . $gemiInfo['arGemi'];
                }
            }
        }
    } catch (SoapFault $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIES VAT Number Check</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold text-center mb-8">VIES VAT Number Check</h1>
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <form method="post">
                <div class="mb-4">
                    <label for="country_code" class="block text-sm font-medium text-gray-700">Country Code:</label>
                    <select id="country_code" name="country_code" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="EL" selected>EL - Greece</option>
                        <option value="AT">AT - Austria</option>
                        <option value="BE">BE - Belgium</option>
                        <option value="BG">BG - Bulgaria</option>
                        <option value="CY">CY - Cyprus</option>
                        <option value="CZ">CZ - Czech Republic</option>
                        <option value="DE">DE - Germany</option>
                        <option value="DK">DK - Denmark</option>
                        <option value="EE">EE - Estonia</option>
                        <option value="ES">ES - Spain</option>
                        <option value="FI">FI - Finland</option>
                        <option value="FR">FR - France</option>
                        <option value="HR">HR - Croatia</option>
                        <option value="HU">HU - Hungary</option>
                        <option value="IE">IE - Ireland</option>
                        <option value="IT">IT - Italy</option>
                        <option value="LT">LT - Lithuania</option>
                        <option value="LU">LU - Luxembourg</option>
                        <option value="LV">LV - Latvia</option>
                        <option value="MT">MT - Malta</option>
                        <option value="NL">NL - Netherlands</option>
                        <option value="PL">PL - Poland</option>
                        <option value="PT">PT - Portugal</option>
                        <option value="RO">RO - Romania</option>
                        <option value="SE">SE - Sweden</option>
                        <option value="SI">SI - Slovenia</option>
                        <option value="SK">SK - Slovakia</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="vat_number" class="block text-sm font-medium text-gray-700">VAT Number:</label>
                    <input type="text" id="vat_number" name="vat_number" required placeholder="e.g., 123456789"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Check VAT Number
                </button>
            </form>
        </div>

        <?php if (isset($valid)): ?>
            <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md mt-8">
                <h2 class="text-2xl font-bold mb-4">Result:</h2>
                <p class="mb-2"><strong class="text-gray-700">Valid:</strong> <span class="font-semibold <?php echo $valid ? 'text-green-600' : 'text-red-600'; ?>"><?php echo $valid ? 'Yes' : 'No'; ?></span></p>
                <?php if ($valid): ?>
                    <?php if (!empty($tradeName)): ?>
                        <p class="mb-2"><strong class="text-gray-700">Trade Name:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($tradeName); ?></span></p>
                    <?php endif; ?>
                    <?php if (!empty($businessName)): ?>
                        <p class="mb-2"><strong class="text-gray-700">Business Name:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($businessName); ?></span></p>
                    <?php endif; ?>
                    <p class="mb-2"><strong class="text-gray-700">Address:</strong> <span class="text-gray-900"><?php echo htmlspecialchars($address); ?></span></p>
                    
                    <?php if (isset($gemiInfo) && $gemiLink): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h3 class="text-lg font-semibold mb-2">Greek Business Registry (GEMI) Info:</h3>
                            <p class="mb-2"><strong class="text-gray-700">Company Name:</strong> <?php echo htmlspecialchars($gemiInfo['co_name'] ?? ''); ?></p>
                            <p class="mb-2"><strong class="text-gray-700">Type:</strong> <?php echo htmlspecialchars($gemiInfo['type'] ?? ''); ?></p>
                            <p class="mb-2"><strong class="text-gray-700">AFM:</strong> <?php echo htmlspecialchars($gemiInfo['afm'] ?? ''); ?></p>
                            <p class="mb-2"><strong class="text-gray-700">GEMI Registration Number:</strong> <?php echo htmlspecialchars($gemiInfo['arGemi'] ?? ''); ?></p>
                            <a href="<?php echo htmlspecialchars($gemiLink); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">View full details on Business Portal</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md mt-8">
                <h2 class="text-2xl font-bold mb-4">Error:</h2>
                <p class="text-red-600"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
