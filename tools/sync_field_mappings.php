<?php
require_once dirname(__DIR__) . '/db_pdo.php';

function normalizeFieldEvidence(string $value): string
{
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return strtolower((string)preg_replace('/[^a-z0-9]+/i', '', $value));
}

$categoryRules = [
    'computer-science' => [
        'title' => 'Computer Science & IT',
        'patterns' => ['computer science', 'computer technology', 'computer engineering', 'information technology', 'software engineering', 'computer communication', 'information systems', 'cyber security', 'cybersecurity', 'embedded systems', 'knowledge engineering'],
    ],
    'medicine' => [
        'title' => 'Medicine & Healthcare',
        'patterns' => ['medicine', 'mbbs', 'medical', 'nursing', 'pharmacy', 'dental', 'dentistry', 'public health', 'community health', 'midwifery', 'physiotherapy', 'health science'],
    ],
    'business' => [
        'title' => 'Business & Management',
        'patterns' => ['business', 'economics', 'commerce', 'accounting', 'management', 'banking', 'finance', 'marketing', 'statistics'],
    ],
    'foreign-languages' => [
        'title' => 'Foreign Languages',
        'patterns' => ['foreign language', 'translation', 'interpretation', 'chinese', 'japanese', 'korean', 'french', 'german', 'russian', 'thai', 'professional communication', 'business english'],
    ],
    'law' => [
        'title' => 'Law & Governance',
        'patterns' => ['law', 'llb', 'llb'],
    ],
    'agriculture' => [
        'title' => 'Agriculture & Forestry',
        'patterns' => ['agriculture', 'agronomy', 'crop science', 'veterinary', 'animal science', 'forestry', 'forest science', 'environmental science'],
    ],
    'education' => [
        'title' => 'Education & Pedagogy',
        'patterns' => ['education', 'teacher training', 'curriculum', 'pedagogy', 'bachelor of education', 'bed'],
    ],
    'arts-culture' => [
        'title' => 'Arts & Culture',
        'patterns' => ['music', 'dramatic arts', 'drama', 'painting', 'sculpture', 'cinema', 'theatre', 'theater', 'cultural heritage', 'arts and culture'],
    ],
    'arts-science' => [
        'title' => 'Arts & Science',
        'patterns' => ['myanmar', 'english', 'physics', 'chemistry', 'mathematics', 'history', 'philosophy', 'psychology', 'geography', 'geology', 'botany', 'zoology', 'biology', 'arts and science', 'bachelor of arts', 'bachelor of science', 'bsc'],
    ],
    'defense' => [
        'title' => 'Defense & Military',
        'patterns' => ['military engineering', 'defense studies', 'defence studies', 'military medicine', 'military science'],
    ],
];

function evidenceMatches(string $label, array $patterns): bool
{
    $normalizedLabel = normalizeFieldEvidence($label);
    foreach ($patterns as $pattern) {
        $normalizedPattern = normalizeFieldEvidence($pattern);
        if ($normalizedPattern !== '' && str_contains($normalizedLabel, $normalizedPattern)) {
            return true;
        }
    }
    return false;
}

try {
    $pdo = youruniPdo();
    $universities = $pdo->query('SELECT id, uni_name, page FROM uni ORDER BY uni_name')->fetchAll();
    $records = [];

    foreach ($universities as $university) {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $university['page'];
        $majorLabels = [];
        $degreeLabels = [];
        if (is_file($path)) {
            $document = new DOMDocument();
            $previousErrorMode = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">' . file_get_contents($path));
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
            $xpath = new DOMXPath($document);
            foreach ($xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' tag ')]") as $node) {
                $label = trim((string)preg_replace('/\s+/u', ' ', $node->textContent));
                if ($label !== '') $majorLabels[] = $label;
            }
            foreach ($xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' card-list ')]//li") as $node) {
                $label = trim((string)preg_replace('/\s+/u', ' ', $node->textContent));
                if ($label !== '') $degreeLabels[] = $label;
            }
        }

        foreach ($categoryRules as $slug => $category) {
            $matchingEvidence = [];
            foreach (array_merge($majorLabels, $degreeLabels) as $label) {
                if (evidenceMatches($label, $category['patterns'])) $matchingEvidence[] = $label;
            }
            $matchingEvidence = array_values(array_unique($matchingEvidence));
            $status = $matchingEvidence
                ? 'offered'
                : ($majorLabels ? 'not_offered' : 'unverified');
            $note = $matchingEvidence
                ? 'Matched local program evidence on ' . $university['page'] . ': ' . implode('; ', array_slice($matchingEvidence, 0, 5)) . '. This reflects the YourUni profile and is not an independent official-source verification.'
                : ($majorLabels
                    ? 'No matching major was listed in the available-majors card on ' . $university['page'] . '. Classified against the local YourUni profile only.'
                    : 'No usable major-card evidence is present on ' . $university['page'] . '; availability remains unverified.');

            $records[] = [
                'uni_id' => (int)$university['id'],
                'uni_name' => $university['uni_name'],
                'page' => $university['page'],
                'program_key' => $slug,
                'program_name' => $category['title'],
                'status' => $status,
                'notes' => $note,
            ];
        }
    }

    $totals = ['offered' => 0, 'not_offered' => 0, 'unverified' => 0];
    $byCategory = [];
    foreach ($records as $record) {
        $totals[$record['status']]++;
        $byCategory[$record['program_name']][$record['status']] = ($byCategory[$record['program_name']][$record['status']] ?? 0) + 1;
    }
    foreach ($byCategory as $title => $counts) {
        printf("%-34s %3d offered, %3d not listed, %3d unverified\n", $title, $counts['offered'] ?? 0, $counts['not_offered'] ?? 0, $counts['unverified'] ?? 0);
    }
    printf("\n%d records: %d offered, %d not listed, %d unverified.\n", count($records), $totals['offered'], $totals['not_offered'], $totals['unverified']);

    if (!in_array('--apply', $argv, true)) {
        echo "Preview only. Re-run with --apply to save these non-Engineering field mappings.\n";
        exit(0);
    }

    $save = $pdo->prepare('INSERT INTO university_programs (uni_id, program_key, program_name, status, notes, source_url, last_verified_at)
        VALUES (:uni_id, :program_key, :program_name, :status, :notes, NULL, :verified)
        ON DUPLICATE KEY UPDATE program_name=VALUES(program_name), status=VALUES(status), notes=VALUES(notes), source_url=NULL, last_verified_at=VALUES(last_verified_at)');
    $pdo->beginTransaction();
    foreach ($records as $record) {
        $save->execute([
            'uni_id' => $record['uni_id'],
            'program_key' => $record['program_key'],
            'program_name' => $record['program_name'],
            'status' => $record['status'],
            'notes' => $record['notes'],
            'verified' => $record['status'] === 'unverified' ? null : date('Y-m-d'),
        ]);
    }
    $pdo->commit();
    echo "Applied non-Engineering category mappings. Engineering records were not modified.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, 'Field mapping sync failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
