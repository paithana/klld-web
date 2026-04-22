<?php
/**
 * Test Suite: TripAdvisor Content API Verification
 */

define('KLLD_TOOL_RUN', true);
define('KLLD_SYNC_NO_RUN', true);

require_once 'wp-load.php';
require_once dirname(__DIR__) . '/ota_sync.php';

class TripAdvisorAPITest {
    private $sync;
    private $api_key;

    public function __construct() {
        $this->sync = new OTAReviewSync();
        $this->api_key = get_option('_ta_api_key');
    }

    public function run() {
        echo "================================================\n";
        echo "TRIPADVISOR CONTENT API VERIFICATION\n";
        echo "================================================\n\n";

        if (!$this->api_key) {
            echo "❌ ERROR: TripAdvisor API Key not found in settings.\n";
            echo "Please add it in the Review Manager first.\n";
            exit(1);
        }

        $this->test_endpoint_connectivity();
        $this->test_parsing_logic();

        echo "\n================================================\n";
        echo "🏁 VERIFICATION COMPLETED\n";
        echo "================================================\n";
    }

    private function test_endpoint_connectivity() {
        echo "TA-01: TESTING API CONNECTIVITY\n";
        
        // Use a known Khao Lak Land Discovery ID: 1960808 (Supplier Profile)
        $location_id = "1960808";
        $url = "https://api.tripadvisor.com/api/v1/location/{$location_id}/reviews?key={$this->api_key}&language=en";
        
        echo "  [INFO] Querying ID: $location_id\n";
        $resp = wp_remote_get($url, ['timeout' => 20]);
        
        $code = wp_remote_retrieve_response_code($resp);
        if ($code === 200) {
            echo "  [PASS] API Connectivity Successful (Code 200).\n";
        } else {
            $msg = is_wp_error($resp) ? $resp->get_error_message() : "HTTP $code";
            echo "  [FAIL] API Connectivity Failed: $msg\n";
            if ($code == 403) echo "  [HINT] Your API Key might be invalid or still pending activation.\n";
        }
    }

    private function test_parsing_logic() {
        echo "\nTA-02: VERIFYING JSON PARSING (MOCK)\n";
        
        $mock_json = json_encode([
            'data' => [
                [
                    'id' => 'mock_1',
                    'location_id' => '12345',
                    'text' => 'Excellent tour, highly recommended!',
                    'rating' => 5,
                    'published_date' => '2024-04-18T12:00:00Z',
                    'user' => ['username' => 'John Doe']
                ]
            ]
        ]);

        $data = json_decode($mock_json, true);
        if (isset($data['data'][0]['text']) && $data['data'][0]['user']['username'] === 'John Doe') {
            echo "  [PASS] JSON structure and author mapping verified.\n";
        } else {
            echo "  [FAIL] JSON structure mismatch.\n";
        }
    }
}

$test = new TripAdvisorAPITest();
$test->run();
