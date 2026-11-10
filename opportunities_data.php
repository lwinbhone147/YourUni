<?php
/**
 * Shared setup helpers for YourUni's Opportunities section.
 * The table is created and seeded when the authenticated admin dashboard opens.
 */

function youruniDefaultOpportunities(): array
{
    return [
        ['engineering', 'Engineering & Tech', '⚙️', 'Designing, building, and maintaining structures, machines, and technological systems.', 'YTU • MTU • UTYCC', 'Engineering.html', 'card-eng', 1],
        ['computer-science', 'Computer Science & IT', '💻', 'Software development, artificial intelligence, cybersecurity, and data science.', 'UIT • UCSY • MIIT', 'Computerscience.html', 'card-cs', 2],
        ['medicine', 'Medicine & Healthcare', '⚕️', 'Clinical practice, surgical medicine, nursing, public health, and pharmacy.', 'UM1 • UM2 • UMM', 'medicine.html', 'card-med', 3],
        ['business', 'Business & Management', '📊', 'Financial management, digital marketing, economics, and business administration.', 'Yangon Eco • Meiktila Eco', 'business.html', 'card-biz', 4],
        ['foreign-languages', 'Foreign Languages', '🌐', 'International relations, translation, diplomacy, and global business trade.', 'YUFL • MUFL', 'language.html', 'card-lang', 5],
        ['law', 'Law & Governance', '⚖️', 'Legal frameworks, public policy, human rights, and corporate law compliance.', 'Yangon University • Mandalay University', 'law.html', 'card-law', 6],
        ['agriculture', 'Agriculture & Forestry', '🌱', 'Cultivate the future through agritech, animal husbandry, forest management, and environmental conservation.', 'YAU • UVS • UFES', 'agriculture.html', 'card-agri', 7],
        ['education', 'Education & Pedagogy', '📚', 'Shape the next generation with specialized teacher training, curriculum development, and educational administration.', 'Yangon Edu • Sagaing Edu', 'education.html', 'card-edu', 8],
        ['arts-culture', 'Arts & Culture', '🎨', 'Explore creative disciplines including visual arts, traditional performance, theater, cinematography, and heritage.', 'NUAC Yangon • NUAC Mandalay', 'arts.html', 'card-art', 9],
        ['arts-science', 'Arts & Science', '🔬', 'Study foundational pure sciences like Physics and Chemistry alongside vital humanities like History and Psychology.', 'Yangon University • Mandalay University', 'arts-science.html', 'card-science', 10],
        ['defense', 'Defense & Military', '🛡️', 'Engage in specialized military training tracks focusing on command, military engineering, and military healthcare.', 'DSA • DSTA • DSMA', 'defense.html', 'card-defense', 11],
    ];
}

function youruniEnsureOpportunityTable(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS opportunities (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(120) NOT NULL UNIQUE,
        title VARCHAR(180) NOT NULL,
        icon VARCHAR(32) NOT NULL DEFAULT '🎓',
        description TEXT NOT NULL,
        university_examples VARCHAR(255) NOT NULL DEFAULT '',
        page_link VARCHAR(255) NOT NULL DEFAULT '#',
        css_class VARCHAR(40) NOT NULL DEFAULT 'card-cs',
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $count = (int)$pdo->query('SELECT COUNT(*) FROM opportunities')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO opportunities
        (slug, title, icon, description, university_examples, page_link, css_class, sort_order, is_active)
        VALUES (:slug, :title, :icon, :description, :examples, :page_link, :css_class, :sort_order, 1)");

    foreach (youruniDefaultOpportunities() as $item) {
        $stmt->execute([
            'slug' => $item[0],
            'title' => $item[1],
            'icon' => $item[2],
            'description' => $item[3],
            'examples' => $item[4],
            'page_link' => $item[5],
            'css_class' => $item[6],
            'sort_order' => $item[7],
        ]);
    }
}

function youruniMakeSlug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? substr($value, 0, 100) : 'opportunity-' . bin2hex(random_bytes(4));
}

function youruniSafePageLink(string $value): bool
{
    $value = trim($value);
    if ($value === '' || preg_match('/[\r\n]/', $value)) {
        return false;
    }
    if (preg_match('~^https?://[^\s]+$~i', $value)) {
        return true;
    }
    return (bool)preg_match('~^[a-zA-Z0-9_./#-]+$~', $value);
}
