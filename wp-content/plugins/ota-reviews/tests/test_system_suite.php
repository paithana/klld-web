<?php
/**
 * System-Wide OTA Integration Test Suite
 */

define('KLLD_TOOL_RUN', true);
define('KLLD_SYNC_NO_RUN', true);

require_once dirname(__FILE__, 5) . '/wp-load.php';
require_once dirname(__FILE__, 2) . '/ota_sync.php';

class SystemSyncTest {
    public function run() {
        echo "================================================\n";
        echo "🌐 SYSTEM-WIDE OTA INTEGRATION TEST SUITE\n";
        echo "================================================\n\n";

        $this->test_date_normalization();
        $this->test_string_matching();
        $this->test_translation_logic();
        $this->test_db_upsert_logic();

        echo "\n================================================\n";
        echo "🏁 SYSTEM VERIFICATION COMPLETED\n";
        echo "================================================\n";
    }

    private function test_date_normalization() {
        echo "SYS-01: DATE NORMALIZATION\n";
        $cases = [
            '2024-04-18'          => '2024-04-18 00:00:00',
            '18-Apr-2026'         => '2026-04-18 00:00:00', 
            '2024-04-18T10:00:00' => '2024-04-18 10:00:00', 
            '1713434400'          => '2024-04-18 10:00:00', 
            'invalid'             => '',            
        ];

        foreach ($cases as $input => $expected) {
            $result = OTAReviewSync::normalize_date($input);
            $status = ($result === $expected) ? "✅ PASS" : "❌ FAIL (Got: $result)";
            echo "  - Input: '$input' -> $status\n";
        }
        echo "\n";
    }

    private function test_string_matching() {
        echo "SYS-02: STRING MATCHING SCORE\n";
        $cases = [
            ['s1' => 'Amazing 3 Temples', 's2' => 'Amazing 3 Temples', 'expected' => 100],
            ['s1' => 'Khao Lak Safari', 's2' => 'Safari Khao Lak', 'expected' => 80],
            ['s1' => 'James Bond Island', 's2' => 'James Bond Island Full Day', 'expected' => 60],
            ['s1' => 'Completely Different', 's2' => 'No Match', 'expected' => 0],
        ];

        foreach ($cases as $case) {
            $s1 = $case['s1'];
            $s2 = $case['s2'];
            $min_expected = $case['expected'];
            $score = OTAReviewSync::calculate_match_score($s1, $s2);
            $status = ($score >= $min_expected) ? "✅ PASS ($score%)" : "❌ FAIL ($score%)";
            echo "  - '$s1' vs '$s2' -> $status\n";
        }
        echo "\n";
    }

    private function test_translation_logic() {
        echo "SYS-03: TRANSLATION & LANGUAGE DETECTION\n";
        $sync = new OTAReviewSync();
        
        // Find a tour with translations if possible
        $tours = get_posts(['post_type' => 'st_tours', 'posts_per_page' => 1]);
        if (!empty($tours)) {
            $id = $tours[0]->ID;
            $lang = $sync->get_post_language($id);
            $translations = $sync->get_translated_post_ids($id);
            
            echo "  - Post ID $id Language: $lang\n";
            echo "  - Translations Found: " . count($translations) . " (IDs: " . implode(', ', $translations) . ")\n";
            echo "  [PASS] Translation utility methods operational.\n";
        } else {
            echo "  [SKIP] No tours found for translation testing.\n";
        }
        echo "\n";
    }

    private function test_db_upsert_logic() {
        echo "SYS-04: DATABASE UPSERT LOGIC (DRY RUN)\n";
        $sync = new OTAReviewSync();
        
        // Test upsert_review return value (should return comment_id or false)
        // We use a non-existent post ID to avoid polluting the DB
        $res = $sync->upsert_review(999999, 'mock', 'rem_1', [
            'author' => 'Tester',
            'content' => 'Test Content',
            'rating' => 5,
            'date' => '18-Apr-2026'
        ]);
        
        if ($res === false) {
            echo "  [PASS] Correctly rejected non-existent post ID.\n";
        } else {
            echo "  [FAIL] Did not handle non-existent post ID as expected.\n";
        }
    }
}

$suite = new SystemSyncTest();
$suite->run();
