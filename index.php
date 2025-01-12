<?php

// Simulate your campaigns array for testing
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

// Function to handle bid requests
function handleBidRequest($bidRequestJson, $campaigns) {
    $bidRequest = json_decode($bidRequestJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return json_encode(["error" => "Invalid JSON format"]);
    }

    // Validate bid request parameters
    $imp = $bidRequest['imp'][0] ?? null;
    $device = $bidRequest['device'] ?? null;
    $geo = $device['geo'] ?? null;

    if (!$imp || !$device || !$geo) {
        return json_encode(["error" => "Missing required fields"]);
    }

    // Process the bid request
    foreach ($campaigns as $campaign) {
        if (
            $campaign['dimension'] === "{$imp['banner']['w']}x{$imp['banner']['h']}" &&
            $campaign['country'] === $geo['country'] &&
            strpos($campaign['hs_os'], $device['os']) !== false &&
            $campaign['price'] >= $imp['bidfloor']
        ) {
            return json_encode([
                "campaign_name" => $campaign['campaignname'],
                "advertiser" => $campaign['advertiser'],
                "price" => $campaign['price'],
                "image_url" => $campaign['image_url']
            ]);
        }
    }

    return json_encode(["error" => "No suitable campaign found"]);
}

// Read the POST data
$inputJson = '{
    "imp": [{
        "banner": {"w": 320, "h": 480},
        "bidfloor": 0.05
    }],
    "device": {
        "os": "Android",
        "geo": {"country": "Bangladesh"}
    }
}';

// Handle the request
$response = handleBidRequest($inputJson, $campaigns);

// Output the response as JSON
header('Content-Type: application/json');
echo $response;
?>
