<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');

// Function to fetch properties from the SPA
function fetchProperties()
{
    $response = CRest::call('crm.item.list', [
        'entityTypeId' => PROPERTY_LISTING_ENTITY_TYPE_ID
    ]);

    $properties = $response['result']['items'] ?? [];
    return $properties;
}

function fetchPropertyDetails($id)
{
    $response = CRest::call('crm.item.get', [
        'entityTypeId' => PROPERTY_LISTING_ENTITY_TYPE_ID,
        'id' => $id
    ]);

    return $response['result']['item'] ?? [];
}

$properties = fetchProperties();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['property_ids']) && !empty($_POST['property_ids']) && isset($_POST['portal'])) {
        $propertyIds = $_POST['property_ids'];
        $portal = $_POST['portal'];

        // Start output buffering
        ob_start();

        // Fetch property details
        $properties = [];
        foreach ($propertyIds as $id) {
            try {
                $property = fetchPropertyDetails($id);
                if ($property) {
                    $properties[] = $property;
                }
            } catch (Exception $e) {
                echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
            }
        }

        // Generate XML
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><list/>');
        $xml->addAttribute('last_update', date('y-m-d H:i:s')); // Current date-time
        $xml->addAttribute('listing_count', count($properties));

        foreach ($properties as $property) {
            $propertyNode = $xml->addChild('property');
            $propertyNode->addAttribute('last_update', date('y-m-d H:i:s', strtotime($property['updatedTime'] ?? '')));
            $propertyNode->addAttribute('id', $property['id'] ?? '');

            addCDataElement($propertyNode, 'reference_number', $property['ufCrm83ReferenceNumber'] ?? '');
            addCDataElement($propertyNode, 'permit_number', $property['ufCrm83PermitNumber'] ?? '');

            if (isset($property['ufCrm83RentalPeriod']) && $property['ufCrm83RentalPeriod'] === 'M') {
                addCDataElement($propertyNode->addChild('price'), 'monthly', $property['ufCrm83Price'] ?? '');
            }

            addCDataElement($propertyNode, 'offering_type', $property['ufCrm83OfferingType'] ?? '');
            addCDataElement($propertyNode, 'property_type', $property['ufCrm83PropertyType'] ?? '');

            addCDataElement($propertyNode, 'geopoints', $property['ufCrm83Geopoints'] ?? '');
            addCDataElement($propertyNode, 'city', $property['ufCrm83City'] ?? '');
            addCDataElement($propertyNode, 'community', $property['ufCrm83Community'] ?? '');
            addCDataElement($propertyNode, 'sub_community', $property['ufCrm83SubCommunity'] ?? '');
            addCDataElement($propertyNode, 'title_en', $property['ufCrm83TitleEn'] ?? '');
            addCDataElement($propertyNode, 'description_en', $property['ufCrm83DescriptionEn'] ?? '');
            addCDataElement($propertyNode, 'size', $property['ufCrm83Size'] ?? '');
            addCDataElement($propertyNode, 'bedroom', $property['ufCrm83Bedroom'] ?? '');
            addCDataElement($propertyNode, 'bathroom', $property['ufCrm83Bathroom'] ?? '');

            $agentNode = $propertyNode->addChild('agent');
            addCDataElement($agentNode, 'id', $property['ufCrm83AgentId'] ?? '');
            addCDataElement($agentNode, 'name', $property['ufCrm83AgentName'] ?? '');
            addCDataElement($agentNode, 'email', $property['ufCrm83AgentEmail'] ?? '');
            addCDataElement($agentNode, 'phone', $property['ufCrm83AgentPhone'] ?? '');
            addCDataElement($agentNode, 'photo', $property['ufCrm83AgentPhoto'] ?? '');

            $photoNode = $propertyNode->addChild('photo');
            foreach ($property['ufCrm83Photos'] as $photo) {

                $urlNode = addCDataElement($photoNode, 'url', $photo);
                $urlNode->addAttribute('last_update', date('Y-m-d H:i:s'));
                $urlNode->addAttribute('watermark', 'Yes');
            }

            addCDataElement($propertyNode, 'parking', $property['ufCrm83Parking'] ?? '');
            addCDataElement($propertyNode, 'furnished', $property['ufCrm83Furnished'] ?? '');
            addCDataElement($propertyNode, 'price_on_application', $property['ufCrm83PriceOnApplication'] ?? '');
        }

        // End output buffering and get content
        $content = ob_get_clean();
        $fileName = $portal . '_properties_' . date('y-m-d_H-i-s') . '.xml';

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $xml->asXML();
        exit;
    }
}

// Helper function to add CDATA
function addCDataElement(SimpleXMLElement $node, $name, $value)
{
    $child = $node->addChild($name);
    $dom = dom_import_simplexml($child);
    $dom->appendChild($dom->ownerDocument->createCDATASection($value));

    return $child;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Properties</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Style for the loading message */
        #loadingMessage {
            display: none;
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
        }

        .loader {
            width: 50px;
            aspect-ratio: 1;
            --_c: no-repeat radial-gradient(farthest-side, #25b09b 92%, #0000);
            background:
                var(--_c) top,
                var(--_c) left,
                var(--_c) right,
                var(--_c) bottom;
            background-size: 12px 12px;
            animation: l7 1s infinite;
            margin: 10px auto;
        }

        @keyframes l7 {
            to {
                transform: rotate(.5turn)
            }
        }
    </style>

    <script>
        function toggleCheckboxes(source) {
            const checkboxes = document.querySelectorAll('input[name="property_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }


        // function showLoadingMessage() {
        //     document.getElementById('loadingMessage').style.display = 'block';
        //     // document.getElementById('exportForm').style.display = 'none';
        //     // document.getElementById('pageTitle').style.display = 'none';
        //     // document.getElementById('backBtn').style.display = 'none';
        // }
    </script>
</head>

<body>
    <div class="container my-4">
        <div id='pageTitle'>
            <h2>Export Properties</h2>
            <p class="text-muted">Export properties to XML file for Bayut and Dubizzle</p>
        </div>

        <div class="mb-3 text-left" id='backBtn'>
            <a href="index.php" class="btn btn-sm btn-secondary">Back</a>
        </div>

        <!-- Form for exporting properties -->
        <form id="exportForm" action="export.php" method="POST" onsubmit="showLoadingMessage()">
            <div class="form-group">
                <label for="portal">Select Portal:</label>
                <select id="portal" name="portal" class="form-control">
                    <option value="property_finder">Property Finder</option>
                    <option value="bayut">Bayut</option>
                    <option value="dubizzle">Dubizzle</option>
                </select>
            </div>

            <div class="form-group">
                <label for="properties">Select Properties:</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" onclick="toggleCheckboxes(this)">
                            </th>
                            <th>Title</th>
                            <th>Offering Type</th>
                            <th>Property Type</th>
                            <th>Price</th>
                            <th>City</th>
                            <th>Agent Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="property_ids[]" value="<?php echo htmlspecialchars($property['id']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($property['ufCrm83TitleEn']); ?></td>
                                <td><?php echo htmlspecialchars($property['ufCrm83OfferingType']); ?></td>
                                <td><?php echo htmlspecialchars($property['ufCrm83PropertyType']); ?></td>
                                <td><?php echo htmlspecialchars($property['ufCrm83Price']); ?></td>
                                <td><?php echo htmlspecialchars($property['ufCrm83City']); ?></td>
                                <td><?php echo htmlspecialchars($property['ufCrm83AgentName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Export Selected Properties</button>
        </form>

        <!-- Loading message section -->
        <div id="loadingMessage">
            <p>Exporting properties... Please wait.</p>
            <div class="loader"></div>
        </div>
    </div>
</body>

</html>