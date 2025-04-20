# VIES VAT Number Validator with Greek GEMI Integration

A PHP script that validates European VAT numbers using the EU's VIES SOAP API and provides additional business information from the Greek Business Registry (GEMI) for Greek companies.

## Features

- Validates VAT numbers for all EU member states
- Displays company name and address information from VIES
- For Greek companies (country code EL):
  - Fetches additional business information from GEMI
  - Provides direct link to the company's registration details
  - Shows company status and type
- Clean, responsive interface built with Tailwind CSS
- Simple, self-contained implementation

## Requirements

- PHP 7.4 or higher
- SOAP extension enabled in PHP
- cURL extension enabled (for GEMI API calls)
- Internet access to reach VIES and GEMI APIs

## Usage

1. Select the country code from the dropdown (defaults to Greece/EL)
2. Enter the VAT number (without country prefix)
3. Click "Check VAT Number"
4. View validation results and company information

For Greek companies, you'll also see:
- GEMI registration number
- Company type
- Link to the official business portal record

## Technical Details

The script performs the following operations:

1. Receives user input (country code and VAT number)
2. Calls the EU VIES SOAP API (`checkVatService.wsdl`)
3. Processes the response:
   - Validates the VAT number
   - Extracts company name and address
   - Splits name into trade name and business name (if separated by '||')
4. For Greek VAT numbers:
   - Calls the GEMI autocomplete API (`publicity.businessportal.gr`)
   - Extracts business registration details
   - Generates a direct link to the company's registration
5. Displays all information in a clean, responsive interface


## Limitations

- VIES API may be occasionally unavailable
- GEMI information is only available for Greek companies
- Some older Greek VAT numbers might not be found in GEMI
- Results are only as accurate as the source databases

## Contributing

Contributions are welcome! Please open an issue or pull request for any improvements.

## Disclaimer

This script is provided as-is. The author is not responsible for any legal or financial consequences resulting from its use. Always verify critical VAT information through official channels.
