<?php

// Sample campaigns array (loaded from JSON or database in real-world applications)
$campaigns = [
    [
        "campaignname" => "Test_Banner_13th-31st_march_Developer",
        "advertiser" => "TestGP",
        "code" => "118965F12BE33FB7E",
        "appid" => "20240313103027",
        "tld" => "https://adplaytechnology.com/",
        "creative_type" => "1",
        "creative_id" => 167629,
        "dimension" => "320x480",
        "url" => "https://adplaytechnology.com/",
        "image_url" => "https://s3-ap-southeast-1.amazonaws.com/.../e63324c6f222208f1dc66d3e2daaaf06.png",
        "price" => 0.1,
        "country" => "Bangladesh",
        "hs_os" => "Android,iOS,Desktop",
    ],
];

// Function to handle and validate the bid request
function handleBidRequest($bidRequestJson, $campaigns) {
    $bidRequest = json_decode($bidRequestJson, true);

    // Validate JSON parsing
    if (json_last_error() !== JSON_ERROR_NONE) {
        return json_encode(["error" => "Invalid JSON format"]);
    }

    // Validate essential fields
    $imp = $bidRequest['imp'][0] ?? null;
    $device = $bidRequest['device'] ?? null;
    $geo = $device['geo'] ?? null;
    $bidFloor = $imp['bidfloor'] ?? 0;

    if (!$imp || !$device || !$geo) {
        return json_encode(["error" => "Missing required fields in bid request"]);
    }

    $width = $imp['banner']['w'] ?? null;
    $height = $imp['banner']['h'] ?? null;
    $country = $geo['country'] ?? null;
    $os = $device['os'] ?? null;

    if (!$width || !$height || !$country || !$os) {
        return json_encode(["error" => "Incomplete or invalid bid request parameters"]);
    }

    // Find the most suitable campaign
    $selectedCampaign = null;

    foreach ($campaigns as $campaign) {
        $campaignWidth = intval(explode('x', $campaign['dimension'])[0]);
        $campaignHeight = intval(explode('x', $campaign['dimension'])[1]);

        // Check dimension, location, OS, and bid floor compatibility
        if (
            $width == $campaignWidth &&
            $height == $campaignHeight &&
            $country == $campaign['country'] &&
            stripos($campaign['hs_os'], $os) !== false &&
            $campaign['price'] >= $bidFloor
        ) {
            // Select the highest paying campaign
            if (!$selectedCampaign || $campaign['price'] > $selectedCampaign['price']) {
                $selectedCampaign = $campaign;
            }
        }
    }

    // Generate response
    if ($selectedCampaign) {
        return json_encode([
            "campaign_name" => $selectedCampaign['campaignname'],
            "advertiser" => $selectedCampaign['advertiser'],
            "creative_type" => $selectedCampaign['creative_type'],
            "creative_id" => $selectedCampaign['creative_id'],
            "image_url" => $selectedCampaign['image_url'],
            "landing_page" => $selectedCampaign['url'],
            "bid_price" => $selectedCampaign['price']
        ]);
    }

    return json_encode(["error" => "No suitable campaign found"]);
}

// Sample bid request JSON
$bidRequestJson = '{
    "imp": [{
        "banner": {"w": 320, "h": 480},
        "bidfloor": 0.05
    }],
    "device": {
        "os": "Android",
        "geo": {"country": "Bangladesh"}
    }
}';

// Process the bid request
$response = handleBidRequest($bidRequestJson, $campaigns);

// Output response
header('Content-Type: application/json');
echo $response;

?>
