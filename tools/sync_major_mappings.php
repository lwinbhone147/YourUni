<?php
require_once dirname(__DIR__) . '/db_pdo.php';

function normalizeMajorLabel(string $value): string
{
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return strtolower((string)preg_replace('/[^a-z0-9]+/i', '', $value));
}

$aliases = [
    'civil-engineering' => ['Civil'],
    'electronic-engineering' => ['Electronics Engineering'],
    'electrical-power-engineering' => ['Electrical Power', 'Power Engineering'],
    'telecommunication-engineering' => ['Telecommunications Engineering', 'Telecommunication'],
    'computer-engineering-and-it' => ['Computer Engineering and Information Technology', 'Computer Engineering and IT'],
    'mechatronic-engineering' => ['Mechatronics Engineering', 'Mechatronic', 'Mechatronics'],
    'metallurgical-engineering-and-materials-science' => ['Metallurgy Engineering', 'Metallurgical Engineering and Materials Science'],
    'port-and-harbour-engineering' => ['Port and Harbor Engineering'],
    'marine-electrical-and-electronics-engineering' => ['Marine Electrical and Electronics Engineering', 'Marine Electrical Systems & Electronics Engineering'],
    'electrical-systems-and-instrumentation' => ['Electrical Systems and Instrumentation'],
    'fuel-and-propellant-engineering' => ['Fuel and Propellant'],
];

try {
    $pdo = youruniPdo();
    $specializations = $pdo->query('SELECT id, slug, title FROM specializations WHERE is_active = 1')->fetchAll();
    $universities = $pdo->query('SELECT id, uni_name, page FROM uni')->fetchAll();

    $records = [];
    foreach ($universities as $university) {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $university['page'];
        $majorLabels = [];
        if (is_file($path)) {
            $document = new DOMDocument();
            $previousErrorMode = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">' . file_get_contents($path));
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
            $xpath = new DOMXPath($document);
            $majorNodes = $xpath->query("//*[contains(concat(' ',normalize-space(@class),' '),' tag ')]");
            foreach ($majorNodes as $node) {
                $label = trim((string)preg_replace('/\s+/u', ' ', $node->textContent));
                if ($label !== '') {
                    $majorLabels[normalizeMajorLabel($label)] = $label;
                }
            }
        }

        foreach ($specializations as $specialization) {
            $candidateLabels = array_merge([$specialization['title']], $aliases[$specialization['slug']] ?? []);
            $matchedMajor = null;
            foreach ($candidateLabels as $candidateLabel) {
                $key = normalizeMajorLabel($candidateLabel);
                if (isset($majorLabels[$key])) {
                    $matchedMajor = $majorLabels[$key];
                    break;
                }
            }

            $status = $matchedMajor !== null ? 'offered' : ($majorLabels ? 'not_offered' : 'unverified');
            $note = $matchedMajor !== null
                ? 'Listed as "' . $matchedMajor . '" in the available-majors card on ' . $university['page'] . ' (YourUni local profile).'
                : ($majorLabels
                    ? 'Not listed in the major card on ' . $university['page'] . '; classified as not offered per the project owner\'s instruction to use that card as the supported-major list.'
                    : 'No available-majors list is present on ' . $university['page'] . '; availability remains unverified.');

            $pair = (int)$specialization['id'] . ':' . (int)$university['id'];
            $records[$pair] = [
                'specialization_id' => (int)$specialization['id'],
                'specialization' => $specialization['title'],
                'university_id' => (int)$university['id'],
                'university' => $university['uni_name'],
                'page' => $university['page'],
                'listed_major' => $matchedMajor,
                'status' => $status,
                'notes' => $note,
            ];
        }
    }

    $bySpecialization = [];
    $totals = ['offered' => 0, 'not_offered' => 0, 'unverified' => 0];
    foreach ($records as $record) {
        $bySpecialization[$record['specialization']][$record['status']] = ($bySpecialization[$record['specialization']][$record['status']] ?? 0) + 1;
        $totals[$record['status']]++;
    }
    foreach ($bySpecialization as $title => $counts) {
        printf("%-52s %3d offered, %3d not offered, %3d unverified\n", $title, $counts['offered'] ?? 0, $counts['not_offered'] ?? 0, $counts['unverified'] ?? 0);
    }
    printf("\n%d total mappings: %d offered, %d not offered, %d unverified.\n", count($records), $totals['offered'], $totals['not_offered'], $totals['unverified']);

    if (!in_array('--apply', $argv, true)) {
        echo "Preview only. Re-run with --apply to save these mappings.\n";
        exit(0);
    }

    $save = $pdo->prepare('INSERT INTO specialization_universities (specialization_id, uni_id, status, notes, last_verified_at)
        VALUES (:specialization_id, :uni_id, :status, :notes, :verified)
        ON DUPLICATE KEY UPDATE status=VALUES(status), notes=VALUES(notes), last_verified_at=VALUES(last_verified_at)');
    $pdo->beginTransaction();
    foreach ($records as $record) {
        $save->execute([
            'specialization_id' => $record['specialization_id'],
            'uni_id' => $record['university_id'],
            'status' => $record['status'],
            'notes' => $record['notes'],
            'verified' => $record['status'] === 'unverified' ? null : date('Y-m-d'),
        ]);
    }
    $pdo->commit();
    echo "Applied complete mappings from each available local major list. Universities without a major list remain unverified.\n";
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Major mapping sync failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}