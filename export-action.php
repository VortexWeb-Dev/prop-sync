<?php
require_once(__DIR__ . '/crest/crest.php');
require_once(__DIR__ . '/crest/settings.php');

// Function to fetch property details by ID from the SPA
function fetchPropertyDetails($id)
{
    $response = CRest::call('crm.item.get', [
        'entityTypeId' => PROPERTY_LISTING_ENTITY_TYPE_ID,
        'id' => $id
    ]);

    return $response['result']['item'] ?? [];
}

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
    } else {
        echo "No properties selected or portal not specified.";
    }
} else {
    echo "Invalid request method.";
}

// Helper function to add CDATA
function addCDataElement(SimpleXMLElement $node, $name, $value)
{
    $child = $node->addChild($name);
    $dom = dom_import_simplexml($child);
    $dom->appendChild($dom->ownerDocument->createCDATASection($value));

    return $child;
}
