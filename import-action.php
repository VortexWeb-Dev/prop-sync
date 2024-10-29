<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');

function getLastName($fullName)
{
    $fullName = trim($fullName);

    $nameParts = explode(' ', $fullName);

    return end($nameParts);
}

function sendPropertiesToBitrix($properties)
{
    foreach ($properties as $property) {
        $agent_last_name = getLastName($property['agent_name']);

        $agent_response = CRest::call('user.get', [
            'filter' => [
                'LAST_NAME' => $agent_last_name
            ]
        ]);

        $agent = $agent_response['result'][0] ?? null;

        $result = CRest::call('crm.item.add', [
            'entityTypeId' => PROPERTY_LISTING_ENTITY_TYPE_ID,
            'fields' => [
                'TITLE' => $property['title_en'],
                'ufCrm83PermitNumber' => $property['permit_number'],
                'ufCrm83ReferenceNumber' => $property['reference_number'],
                'ufCrm83OfferingType' => $property['offering_type'],
                'ufCrm83PropertyType' => $property['property_type'],
                'ufCrm83Price' => $property['price'],
                'ufCrm83RentalPeriod' => $property['rental_period'],
                'ufCrm83City' => $property['city'],
                'ufCrm83Community' => $property['community'],
                'ufCrm83SubCommunity' => $property['sub_community'],
                'ufCrm83TitleEn' => $property['title_en'],
                'ufCrm83DescriptionEn' => $property['description_en'],
                'ufCrm83Amenities' => $property['amenities'],
                'ufCrm83Size' => $property['size'],
                'ufCrm83Bedroom' => $property['bedroom'],
                'ufCrm83Bathroom' => $property['bathroom'],
                'ufCrm83AgentId' => $agent['ID'] ?? '',
                'ufCrm83AgentName' => $property['agent_name'],
                'ufCrm83AgentEmail' => $property['agent_email'],
                'ufCrm83AgentPhone' => $property['agent_phone'],
                'ufCrm83AgentPhoto' => $agent['PERSONAL_PHOTO'] ?? '',
                'ufCrm83Parking' => $property['parking'],
                'ufCrm83Photos' => $property['photos'],
                'ufCrm83Geopoints' => $property['geopoints'],
            ]
        ]);

        usleep(500000);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['xmlFile']) && $_FILES['xmlFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['xmlFile']['tmp_name'];
        $fileName = $_FILES['xmlFile']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'xml') {
            echo "Invalid file type. Please upload an XML file.";
            exit;
        }

        $filePath = __DIR__ . '/xml/' . $fileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            echo "File imported successfully.<br>";

            // Load the XML content
            $xmlContent = file_get_contents($filePath);
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);

            // Handle XML parsing errors
            if ($xml === false) {
                echo "Failed to parse XML.<br>";
                foreach (libxml_get_errors() as $error) {
                    echo htmlspecialchars($error->message) . "<br>";
                }
                libxml_clear_errors();
            } else {
                echo "XML parsed successfully.<br>";
                $properties = [];

                foreach ($xml->property as $property) {
                    $propertyDetails = [
                        'permit_number' => (string) $property->permit_number,
                        'reference_number' => (string) $property->reference_number,
                        'offering_type' => (string) $property->offering_type,
                        'property_type' => (string) $property->property_type,
                        'price' => (string) $property->price,
                        'rental_period' => (string) $property->rental_period,
                        'city' => (string) $property->city,
                        'community' => (string) $property->community,
                        'sub_community' => (string) $property->sub_community,
                        'title_en' => (string) $property->title_en,
                        'description_en' => (string) $property->description_en,
                        'amenities' => (string) $property->amenities,
                        'size' => (string) $property->size,
                        'bedroom' => (string) $property->bedroom,
                        'bathroom' => (string) $property->bathroom,
                        'agent_name' => (string) $property->agent->name,
                        'agent_email' => (string) $property->agent->email,
                        'agent_phone' => (string) $property->agent->phone,
                        'parking' => (string) $property->parking,
                        'geopoints' => (string) $property->geopoints,
                        'photos' => [],
                    ];

                    foreach ($property->photo->url as $url) {
                        $propertyDetails['photos'][] = (string) $url;
                    }

                    $properties[] = $propertyDetails;
                }

                sendPropertiesToBitrix($properties);

                $successMessage = urlencode('Properties imported successfully.');
                header("Location: index.php?success_message=$successMessage");
                exit;
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        switch ($_FILES['xmlFile']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo "File is too large.";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "File was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "No temporary folder available.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "A PHP extension stopped the file upload.";
                break;
            default:
                echo "Unknown error occurred during file upload.";
                break;
        }
    }
} else {
    echo "Invalid request method.";
}
