<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/opportunities_data.php';

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['admin_csrf_token'];
$catalogDb = null;
$catalogError = '';
$opportunitiesReady = false;

try {
    $catalogDb = youruniPdo();
    // Create the new opportunities table on the first admin visit and seed it with
    // the existing hard-coded Opportunities cards. Existing rows are never overwritten.
    youruniEnsureOpportunityTable($catalogDb);
    $opportunitiesReady = true;
} catch (Throwable $e) {
    error_log('YourUni catalog database: ' . $e->getMessage());
    $catalogError = 'Could not connect to the shared YourUni database. Check that MySQL is running and that the shared YourUni database exists.';
}

function adminRedirect(string $tab, string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
    header('Location: admin_dashboard.php?tab=' . rawurlencode($tab));
    exit;
}

function adminPostString(string $key, int $maxLength = 5000): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength, 'UTF-8') : substr($value, 0, $maxLength);
}

function adminImageUpload(?array $file): ?string
{
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image could not be uploaded. Try a smaller image (up to 5 MB).');
    }
    if ((int)$file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Images must be 5 MB or smaller.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Please upload a JPG, PNG, GIF, or WEBP image.');
    }
    $directory = __DIR__ . DIRECTORY_SEPARATOR . 'photos';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The photos directory could not be created.');
    }
    $filename = 'admin_' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The image could not be saved to the photos folder.');
    }
    return 'photos/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $postedToken)) {
        adminRedirect((string)($_POST['tab'] ?? 'featured'), 'error', 'Your session expired. Refresh the page and try again.');
    }

    $action = (string)($_POST['action'] ?? '');
    $tab = (string)($_POST['tab'] ?? 'featured');

    try {
        if ($action === 'featured_save') {
            $id = (int)($_POST['id'] ?? 0);
            $title = adminPostString('title', 255);
            $universityName = adminPostString('university_name', 255);
            $major = adminPostString('major', 100);
            $zone = adminPostString('zone', 100);
            $minMarks = (int)($_POST['min_marks'] ?? 0);
            $pageLink = adminPostString('page_link', 255);
            $imageUrl = adminPostString('image_url', 255) ?: 'photos/default.jpg';
            $uploadedImage = adminImageUpload($_FILES['image_file'] ?? null);
            if ($uploadedImage !== null) {
                $imageUrl = $uploadedImage;
            }
            if ($title === '' || $universityName === '' || $major === '' || $zone === '' || $pageLink === '') {
                throw new RuntimeException('Please complete all required fields.');
            }
            if ($minMarks < 0 || $minMarks > 1000) {
                throw new RuntimeException('Enter a valid minimum mark value.');
            }
            if (!youruniSafePageLink($pageLink) || (!preg_match('~^https?://~i', $imageUrl) && !preg_match('~^[a-zA-Z0-9_./#-]+$~', $imageUrl))) {
                throw new RuntimeException('Use a valid relative file path or an http/https URL.');
            }
            if ($id > 0) {
                $stmt = mysqli_prepare($conn, 'UPDATE courses SET title=?, university_name=?, major=?, zone=?, min_marks=?, image_url=?, page_link=? WHERE id=?');
                mysqli_stmt_bind_param($stmt, 'ssssissi', $title, $universityName, $major, $zone, $minMarks, $imageUrl, $pageLink, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                adminRedirect('featured', 'success', 'Homepage featured card updated.');
            }
            $stmt = mysqli_prepare($conn, 'INSERT INTO courses (title, university_name, major, zone, min_marks, image_url, page_link) VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssssiss', $title, $universityName, $major, $zone, $minMarks, $imageUrl, $pageLink);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            adminRedirect('featured', 'success', 'Homepage featured card added.');
        }

        if ($action === 'featured_delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new RuntimeException('Choose a valid featured card.');
            }
            $stmt = mysqli_prepare($conn, 'DELETE FROM courses WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            adminRedirect('featured', 'success', 'Homepage featured card deleted.');
        }

        if ($action === 'university_save') {
            if (!$catalogDb) {
                throw new RuntimeException('The shared YourUni database is unavailable.');
            }
            $id = (int)($_POST['id'] ?? 0);
            $name = adminPostString('uni_name', 255);
            $major = adminPostString('uni_major', 100);
            $region = adminPostString('region', 100);
            $marks = (int)($_POST['uni_min_marks'] ?? 0);
            $image = adminPostString('image_path', 255);
            $page = adminPostString('uni_page', 255);
            $popularity = (int)($_POST['popularity'] ?? 100);
            if ($name === '' || $major === '' || $region === '' || $image === '' || $page === '') {
                throw new RuntimeException('Complete the university name, major, region, image path, and detail page.');
            }
            if ($marks < 0 || $marks > 1000 || $popularity < 0) {
                throw new RuntimeException('Minimum marks must be 0–1000 and popularity must be 0 or higher.');
            }
            if (!youruniSafePageLink($page) || (!preg_match('~^https?://~i', $image) && !preg_match('~^[a-zA-Z0-9_./#-]+$~', $image))) {
                throw new RuntimeException('Use valid relative paths or http/https URLs for the image and detail page.');
            }
            if ($id > 0) {
                $admissionStatus = (string)($_POST['admission_status'] ?? 'unknown');
            if (!in_array($admissionStatus, ['known','pass_based','unknown'], true)) $admissionStatus = 'unknown';
            $sourceUrl = trim((string)($_POST['source_url'] ?? ''));
            $verifiedDate = trim((string)($_POST['last_verified_at'] ?? ''));
            if ($sourceUrl !== '' && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) throw new RuntimeException('Source URL must be a valid URL.');
            if ($admissionStatus !== 'unknown' && $sourceUrl === '') throw new RuntimeException('Add an official source URL before marking admission information as known or pass-based.');
            if ($verifiedDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $verifiedDate)) throw new RuntimeException('Verification date must use YYYY-MM-DD.');
            if ($admissionStatus === 'known' && $marks < 1) throw new RuntimeException('A known minimum-mark requirement must be greater than zero.');
            if ($admissionStatus === 'pass_based') $marks = 0;
            $stmt = $catalogDb->prepare('UPDATE uni SET uni_name=:name, major=:major, region=:region, min_marks=:marks, admission_status=:admission_status, image_path=:image, page=:page, popularity=:popularity, source_url=:source_url, last_verified_at=:verified WHERE id=:id');
                $stmt->execute(['name' => $name, 'major' => $major, 'region' => $region, 'marks' => $marks, 'admission_status' => $admissionStatus, 'image' => $image, 'page' => $page, 'popularity' => $popularity, 'source_url' => ($sourceUrl !== '' ? $sourceUrl : null), 'verified' => ($verifiedDate !== '' ? $verifiedDate : null), 'id' => $id]);
                adminRedirect('universities', 'success', 'University catalogue record updated.');
            }
            $admissionStatus = (string)($_POST['admission_status'] ?? 'unknown');
            if (!in_array($admissionStatus, ['known','pass_based','unknown'], true)) $admissionStatus = 'unknown';
            $sourceUrl = trim((string)($_POST['source_url'] ?? ''));
            $verifiedDate = trim((string)($_POST['last_verified_at'] ?? ''));
            if ($sourceUrl !== '' && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) throw new RuntimeException('Source URL must be a valid URL.');
            if ($admissionStatus !== 'unknown' && $sourceUrl === '') throw new RuntimeException('Add an official source URL before marking admission information as known or pass-based.');
            if ($verifiedDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $verifiedDate)) throw new RuntimeException('Verification date must use YYYY-MM-DD.');
            if ($admissionStatus === 'known' && $marks < 1) throw new RuntimeException('A known minimum-mark requirement must be greater than zero.');
            if ($admissionStatus === 'pass_based') $marks = 0;
            $stmt = $catalogDb->prepare('INSERT INTO uni (uni_name, major, region, min_marks, admission_status, image_path, page, popularity, source_url, last_verified_at) VALUES (:name, :major, :region, :marks, :admission_status, :image, :page, :popularity, :source_url, :verified)');
            $stmt->execute(['name' => $name, 'major' => $major, 'region' => $region, 'marks' => $marks, 'admission_status' => $admissionStatus, 'image' => $image, 'page' => $page, 'popularity' => $popularity, 'source_url' => ($sourceUrl !== '' ? $sourceUrl : null), 'verified' => ($verifiedDate !== '' ? $verifiedDate : null)]);
            adminRedirect('universities', 'success', 'University added to the full catalogue.');
        }

        if ($action === 'university_delete') {
            if (!$catalogDb) {
                throw new RuntimeException('The shared YourUni database is unavailable.');
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new RuntimeException('Choose a valid university.');
            }
            $stmt = $catalogDb->prepare('DELETE FROM uni WHERE id = :id');
            $stmt->execute(['id' => $id]);
            adminRedirect('universities', 'success', 'University removed from the full catalogue.');
        }

        if ($action === 'opportunity_save') {
            if (!$catalogDb || !$opportunitiesReady) {
                throw new RuntimeException('The opportunities table is not available.');
            }
            $id = (int)($_POST['id'] ?? 0);
            $title = adminPostString('opp_title', 180);
            $icon = adminPostString('opp_icon', 32) ?: '🎓';
            $description = adminPostString('opp_description', 4000);
            $examples = adminPostString('university_examples', 255);
            $page = adminPostString('opp_page_link', 255);
            $style = adminPostString('css_class', 40);
            $sortOrder = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;
            $allowedStyles = ['card-eng', 'card-cs', 'card-med', 'card-biz', 'card-lang', 'card-law', 'card-agri', 'card-edu', 'card-art', 'card-science', 'card-defense'];
            if ($title === '' || $description === '' || $page === '') {
                throw new RuntimeException('Opportunity title, description, and destination page are required.');
            }
            if (!youruniSafePageLink($page)) {
                throw new RuntimeException('Enter a valid relative detail-page path or http/https URL.');
            }
            if (!in_array($style, $allowedStyles, true)) {
                $style = 'card-cs';
            }
            if ($id > 0) {
                $slugStmt = $catalogDb->prepare('SELECT slug FROM opportunities WHERE id = :id');
                $slugStmt->execute(['id' => $id]);
                $slug = $slugStmt->fetchColumn();
                if (!$slug) {
                    throw new RuntimeException('That opportunity record no longer exists.');
                }
                $stmt = $catalogDb->prepare('UPDATE opportunities SET title=:title, icon=:icon, description=:description, university_examples=:examples, page_link=:page, css_class=:style, sort_order=:sort_order, is_active=:active WHERE id=:id');
                $stmt->execute(['title' => $title, 'icon' => $icon, 'description' => $description, 'examples' => $examples, 'page' => $page, 'style' => $style, 'sort_order' => $sortOrder, 'active' => $active, 'id' => $id]);
                adminRedirect('opportunities', 'success', 'Opportunity card updated.');
            }
            $baseSlug = youruniMakeSlug($title);
            $slug = $baseSlug;
            $suffix = 2;
            $check = $catalogDb->prepare('SELECT COUNT(*) FROM opportunities WHERE slug = :slug');
            while (true) {
                $check->execute(['slug' => $slug]);
                if ((int)$check->fetchColumn() === 0) {
                    break;
                }
                $slug = substr($baseSlug, 0, 90) . '-' . $suffix++;
            }
            $stmt = $catalogDb->prepare('INSERT INTO opportunities (slug, title, icon, description, university_examples, page_link, css_class, sort_order, is_active) VALUES (:slug, :title, :icon, :description, :examples, :page, :style, :sort_order, :active)');
            $stmt->execute(['slug' => $slug, 'title' => $title, 'icon' => $icon, 'description' => $description, 'examples' => $examples, 'page' => $page, 'style' => $style, 'sort_order' => $sortOrder, 'active' => $active]);
            adminRedirect('opportunities', 'success', 'Opportunity card added.');
        }

        if ($action === 'opportunity_delete') {
            if (!$catalogDb || !$opportunitiesReady) {
                throw new RuntimeException('The opportunities table is not available.');
            }
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                throw new RuntimeException('Choose a valid opportunity.');
            }
            $stmt = $catalogDb->prepare('DELETE FROM opportunities WHERE id = :id');
            $stmt->execute(['id' => $id]);
            adminRedirect('opportunities', 'success', 'Opportunity card deleted.');
        }

        if ($action === 'specialization_save') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $id = (int)($_POST['id'] ?? 0);
            $categoryId = (int)($_POST['specialization_category_id'] ?? 0);
            $title = adminPostString('specialization_title', 180);
            $titleMy = adminPostString('specialization_title_my', 180);
            $slugInput = adminPostString('specialization_slug', 120);
            $slug = youruniMakeSlug($slugInput !== '' ? $slugInput : $title);
            $description = adminPostString('specialization_description', 3000);
            $descriptionMy = adminPostString('specialization_description_my', 3000);
            $workContext = adminPostString('specialization_work_context', 3000);
            $workContextMy = adminPostString('specialization_work_context_my', 3000);
            $icon = adminPostString('specialization_icon', 32) ?: '🎓';
            $badge = adminPostString('specialization_badge', 80) ?: 'Major / Specialization';
            $sortOrder = (int)($_POST['specialization_sort_order'] ?? 0);
            $active = isset($_POST['specialization_is_active']) ? 1 : 0;
            if ($categoryId < 1 || $title === '' || $description === '') {
                throw new RuntimeException('Choose a category and enter a title and description.');
            }
            $catCheck = $catalogDb->prepare('SELECT COUNT(*) FROM opportunities WHERE id=:id');
            $catCheck->execute(['id' => $categoryId]);
            if ((int)$catCheck->fetchColumn() === 0) throw new RuntimeException('That category no longer exists.');
            if ($id > 0) {
                $exists = $catalogDb->prepare('SELECT COUNT(*) FROM specializations WHERE id=:id');
                $exists->execute(['id' => $id]);
                if ((int)$exists->fetchColumn() === 0) throw new RuntimeException('That specialization no longer exists.');
                $stmt = $catalogDb->prepare('UPDATE specializations SET opportunity_id=:category_id, slug=:slug, title=:title, title_my=:title_my, description=:description, description_my=:description_my, work_context=:work_context, work_context_my=:work_context_my, icon=:icon, badge_text=:badge, sort_order=:sort_order, is_active=:active WHERE id=:id');
                $stmt->execute(['category_id'=>$categoryId,'slug'=>$slug,'title'=>$title,'title_my'=>($titleMy!==''?$titleMy:null),'description'=>$description,'description_my'=>($descriptionMy!==''?$descriptionMy:null),'work_context'=>($workContext!==''?$workContext:null),'work_context_my'=>($workContextMy!==''?$workContextMy:null),'icon'=>$icon,'badge'=>$badge,'sort_order'=>$sortOrder,'active'=>$active,'id'=>$id]);
                adminRedirect('programs', 'success', 'Specialization updated. Public category pages will use the saved version.');
            }
            $stmt = $catalogDb->prepare('INSERT INTO specializations (opportunity_id,slug,title,title_my,description,description_my,work_context,work_context_my,icon,badge_text,sort_order,is_active) VALUES (:category_id,:slug,:title,:title_my,:description,:description_my,:work_context,:work_context_my,:icon,:badge,:sort_order,:active)');
            $stmt->execute(['category_id'=>$categoryId,'slug'=>$slug,'title'=>$title,'title_my'=>($titleMy!==''?$titleMy:null),'description'=>$description,'description_my'=>($descriptionMy!==''?$descriptionMy:null),'work_context'=>($workContext!==''?$workContext:null),'work_context_my'=>($workContextMy!==''?$workContextMy:null),'icon'=>$icon,'badge'=>$badge,'sort_order'=>$sortOrder,'active'=>$active]);
            adminRedirect('programs', 'success', 'Specialization added. Add its university availability mapping below.');
        }

        if ($action === 'specialization_delete') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) throw new RuntimeException('Choose a valid specialization.');
            $stmt = $catalogDb->prepare('DELETE FROM specializations WHERE id=:id');
            $stmt->execute(['id'=>$id]);
            adminRedirect('programs', 'success', 'Specialization deleted with its associated mappings.');
        }

        if ($action === 'specialization_mapping_save') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $mappingId = (int)($_POST['mapping_id'] ?? 0);
            $specializationId = (int)($_POST['mapping_specialization_id'] ?? 0);
            $uniId = (int)($_POST['mapping_uni_id'] ?? 0);
            $status = (string)($_POST['mapping_status'] ?? 'unverified');
            $source = trim((string)($_POST['mapping_source_url'] ?? ''));
            $verified = trim((string)($_POST['mapping_verified_at'] ?? ''));
            $notes = adminPostString('mapping_notes', 2000);
            if ($specializationId < 1 || $uniId < 1) throw new RuntimeException('Choose both a specialization and a university.');
            if (!in_array($status, ['offered','not_offered','unverified'], true)) throw new RuntimeException('Choose a valid availability status.');
            if ($source !== '' && (!filter_var($source, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $source))) throw new RuntimeException('Source URL must begin with http:// or https://.');
            if ($verified !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $verified)) throw new RuntimeException('Verification date must use YYYY-MM-DD.');
            $exists = $catalogDb->prepare('SELECT (SELECT COUNT(*) FROM specializations WHERE id=:sid) + (SELECT COUNT(*) FROM uni WHERE id=:uid)');
            $exists->execute(['sid'=>$specializationId,'uid'=>$uniId]);
            if ((int)$exists->fetchColumn() !== 2) throw new RuntimeException('The selected specialization or university no longer exists.');
            if ($mappingId > 0) {
                $stmt = $catalogDb->prepare('UPDATE specialization_universities SET specialization_id=:sid, uni_id=:uid, status=:status, source_url=:source, last_verified_at=:verified, notes=:notes WHERE id=:id');
                $stmt->execute(['sid'=>$specializationId,'uid'=>$uniId,'status'=>$status,'source'=>($source!==''?$source:null),'verified'=>($verified!==''?$verified:null),'notes'=>($notes!==''?$notes:null),'id'=>$mappingId]);
                adminRedirect('programs', 'success', 'University availability mapping updated.');
            }
            $stmt = $catalogDb->prepare('INSERT INTO specialization_universities (specialization_id,uni_id,status,source_url,last_verified_at,notes) VALUES (:sid,:uid,:status,:source,:verified,:notes) ON DUPLICATE KEY UPDATE status=VALUES(status), source_url=VALUES(source_url), last_verified_at=VALUES(last_verified_at), notes=VALUES(notes)');
            $stmt->execute(['sid'=>$specializationId,'uid'=>$uniId,'status'=>$status,'source'=>($source!==''?$source:null),'verified'=>($verified!==''?$verified:null),'notes'=>($notes!==''?$notes:null)]);
            adminRedirect('programs', 'success', 'University availability mapping saved.');
        }

        if ($action === 'specialization_mapping_delete') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $id = (int)($_POST['mapping_id'] ?? 0);
            if ($id < 1) throw new RuntimeException('Choose a valid university mapping.');
            $stmt = $catalogDb->prepare('DELETE FROM specialization_universities WHERE id=:id');
            $stmt->execute(['id'=>$id]);
            adminRedirect('programs', 'success', 'University mapping deleted.');
        }

        if ($action === 'program_save') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $id = (int)($_POST['id'] ?? 0);
            $uniId = (int)($_POST['program_uni_id'] ?? 0);
            $programName = adminPostString('program_name', 180);
            $programKey = youruniMakeSlug(adminPostString('program_key', 120));
            $status = (string)($_POST['program_status'] ?? 'unverified');
            $notes = adminPostString('program_notes', 2000);
            $source = trim((string)($_POST['program_source_url'] ?? ''));
            $verified = trim((string)($_POST['program_verified_at'] ?? ''));
            if ($uniId < 1 || $programName === '' || $programKey === '') throw new RuntimeException('Choose a university and enter a program name/key.');
            if (!in_array($status, ['offered','not_offered','unverified'], true)) throw new RuntimeException('Choose a valid availability status.');
            if ($source !== '' && (!filter_var($source, FILTER_VALIDATE_URL) || !preg_match('~^https?://~i', $source))) throw new RuntimeException('Source URL must be a valid http/https URL.');
            if ($status !== 'unverified' && $source === '') throw new RuntimeException('Add an official source URL before confirming this program mapping.');
            if ($verified !== '' && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $verified)) throw new RuntimeException('Verification date must use YYYY-MM-DD.');
            $exists = $catalogDb->prepare('SELECT COUNT(*) FROM uni WHERE id = :id'); $exists->execute(['id' => $uniId]);
            if ((int)$exists->fetchColumn() === 0) throw new RuntimeException('That university no longer exists.');
            if ($id > 0) {
                $stmt = $catalogDb->prepare('UPDATE university_programs SET uni_id=:uni_id, program_key=:program_key, program_name=:program_name, status=:status, notes=:notes, source_url=:source_url, last_verified_at=:verified WHERE id=:id');
                $stmt->execute(['uni_id'=>$uniId,'program_key'=>$programKey,'program_name'=>$programName,'status'=>$status,'notes'=>($notes!==''?$notes:null),'source_url'=>($source!==''?$source:null),'verified'=>($verified!==''?$verified:null),'id'=>$id]);
                adminRedirect('programs','success','Program availability record updated.');
            }
            $stmt = $catalogDb->prepare('INSERT INTO university_programs (uni_id,program_key,program_name,status,notes,source_url,last_verified_at) VALUES (:uni_id,:program_key,:program_name,:status,:notes,:source_url,:verified) ON DUPLICATE KEY UPDATE program_name=VALUES(program_name), status=VALUES(status), notes=VALUES(notes), source_url=VALUES(source_url), last_verified_at=VALUES(last_verified_at)');
            $stmt->execute(['uni_id'=>$uniId,'program_key'=>$programKey,'program_name'=>$programName,'status'=>$status,'notes'=>($notes!==''?$notes:null),'source_url'=>($source!==''?$source:null),'verified'=>($verified!==''?$verified:null)]);
            adminRedirect('programs','success','Program availability saved.');
        }
        if ($action === 'program_delete') {
            if (!$catalogDb) throw new RuntimeException('The shared database is unavailable.');
            $id=(int)($_POST['id'] ?? 0); if($id<1) throw new RuntimeException('Choose a valid program record.');
            $stmt=$catalogDb->prepare('DELETE FROM university_programs WHERE id=:id'); $stmt->execute(['id'=>$id]);
            adminRedirect('programs','success','Program availability record deleted.');
        }

        throw new RuntimeException('Unknown action. Refresh the page and try again.');
    } catch (Throwable $e) {
        error_log('YourUni admin action (' . $action . '): ' . $e->getMessage());
        $safeMessage = $e instanceof RuntimeException ? $e->getMessage() : 'The change could not be saved. Please check the submitted information and database setup.';
        adminRedirect(in_array($tab, ['featured', 'universities', 'opportunities', 'programs'], true) ? $tab : 'featured', 'error', $safeMessage);
    }
}

$tab = (string)($_GET['tab'] ?? 'featured');
if (!in_array($tab, ['featured', 'universities', 'opportunities', 'programs'], true)) {
    $tab = 'featured';
}
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
$editId = (int)($_GET['edit_id'] ?? 0);

$editFeatured = null;
$featuredRows = [];
$totalFeatured = 0;
$stmt = mysqli_query($conn, 'SELECT * FROM courses ORDER BY id ASC');
if ($stmt) {
    while ($row = mysqli_fetch_assoc($stmt)) {
        $featuredRows[] = $row;
    }
}
$totalFeatured = count($featuredRows);
if ($tab === 'featured' && $editId > 0) {
    $stmtEdit = mysqli_prepare($conn, 'SELECT * FROM courses WHERE id = ?');
    mysqli_stmt_bind_param($stmtEdit, 'i', $editId);
    mysqli_stmt_execute($stmtEdit);
    $resEdit = mysqli_stmt_get_result($stmtEdit);
    $editFeatured = mysqli_fetch_assoc($resEdit) ?: null;
    mysqli_stmt_close($stmtEdit);
}

$universityRows = [];
$editUniversity = null;
$totalUniversities = 0;
$universitySearch = trim((string)($_GET['q'] ?? ''));
if ($catalogDb) {
    try {
        $totalUniversities = (int)$catalogDb->query('SELECT COUNT(*) FROM uni')->fetchColumn();
        if ($universitySearch !== '') {
            $searchStmt = $catalogDb->prepare('SELECT * FROM uni WHERE uni_name LIKE :q1 OR major LIKE :q2 OR region LIKE :q3 ORDER BY uni_name ASC LIMIT 250');
            $needle = '%' . $universitySearch . '%';
            $searchStmt->execute(['q1' => $needle, 'q2' => $needle, 'q3' => $needle]);
            $universityRows = $searchStmt->fetchAll();
        } else {
            $universityRows = $catalogDb->query('SELECT * FROM uni ORDER BY uni_name ASC LIMIT 250')->fetchAll();
        }
        if ($tab === 'universities' && $editId > 0) {
            $editStmt = $catalogDb->prepare('SELECT * FROM uni WHERE id = :id');
            $editStmt->execute(['id' => $editId]);
            $editUniversity = $editStmt->fetch() ?: null;
        }
    } catch (Throwable $e) {
        error_log('YourUni university list: ' . $e->getMessage());
        $catalogError = 'The universities table could not be read. Check your imported youruni_complete.sql database.';
    }
}

$opportunityRows = [];
$editOpportunity = null;
$totalOpportunities = 0;
if ($catalogDb && $opportunitiesReady) {
    try {
        $opportunityRows = $catalogDb->query('SELECT * FROM opportunities ORDER BY sort_order ASC, title ASC')->fetchAll();
        $totalOpportunities = count($opportunityRows);
        if ($tab === 'opportunities' && $editId > 0) {
            $editStmt = $catalogDb->prepare('SELECT * FROM opportunities WHERE id = :id');
            $editStmt->execute(['id' => $editId]);
            $editOpportunity = $editStmt->fetch() ?: null;
        }
    } catch (Throwable $e) {
        error_log('YourUni opportunities list: ' . $e->getMessage());
    }
}

$specializationRows = [];
$editSpecialization = null;
$mappingRows = [];
$editSpecializationMapping = null;
$totalSpecializations = 0;
$totalSpecializationMappings = 0;
$specializationSearch = trim((string)($_GET['specialization_q'] ?? ''));
$mappingSearch = trim((string)($_GET['mapping_q'] ?? ''));
$categoryOptions = [];
$specializationOptions = [];
$universityOptions = [];
if ($catalogDb) {
    try {
        $categoryOptions = $catalogDb->query('SELECT id,title,slug,is_active FROM opportunities ORDER BY sort_order,title')->fetchAll();
        $specializationOptions = $catalogDb->query('SELECT id,title,slug,is_active FROM specializations ORDER BY title')->fetchAll();
        $specSql = 'SELECT s.*, o.title AS category_title, o.slug AS category_slug, (SELECT COUNT(*) FROM specialization_universities su WHERE su.specialization_id=s.id) AS mapping_count FROM specializations s JOIN opportunities o ON o.id=s.opportunity_id';
        if ($specializationSearch !== '') {
            $needle = '%' . $specializationSearch . '%';
            $st = $catalogDb->prepare($specSql . ' WHERE s.title LIKE :q1 OR s.slug LIKE :q2 OR o.title LIKE :q3 OR s.description LIKE :q4 ORDER BY o.sort_order,s.sort_order,s.title LIMIT 500');
            $st->execute(['q1'=>$needle,'q2'=>$needle,'q3'=>$needle,'q4'=>$needle]);
            $specializationRows = $st->fetchAll();
        } else {
            $specializationRows = $catalogDb->query($specSql . ' ORDER BY o.sort_order,s.sort_order,s.title LIMIT 500')->fetchAll();
        }
        $totalSpecializations = (int)$catalogDb->query('SELECT COUNT(*) FROM specializations')->fetchColumn();
        $totalSpecializationMappings = (int)$catalogDb->query('SELECT COUNT(*) FROM specialization_universities')->fetchColumn();
        if ($tab === 'programs' && $editId > 0) {
            $st = $catalogDb->prepare('SELECT * FROM specializations WHERE id=:id');
            $st->execute(['id'=>$editId]);
            $editSpecialization = $st->fetch() ?: null;
        }
        $mappingSql = 'SELECT su.*, s.title AS specialization_title, s.slug AS specialization_slug, o.title AS category_title, u.uni_name, u.page FROM specialization_universities su JOIN specializations s ON s.id=su.specialization_id JOIN opportunities o ON o.id=s.opportunity_id JOIN uni u ON u.id=su.uni_id';
        if ($mappingSearch !== '') {
            $needle = '%' . $mappingSearch . '%';
            $st = $catalogDb->prepare($mappingSql . ' WHERE s.title LIKE :q1 OR o.title LIKE :q2 OR u.uni_name LIKE :q3 OR su.status LIKE :q4 ORDER BY s.title,u.uni_name LIMIT 500');
            $st->execute(['q1'=>$needle,'q2'=>$needle,'q3'=>$needle,'q4'=>$needle]);
            $mappingRows = $st->fetchAll();
        } else {
            $mappingRows = $catalogDb->query($mappingSql . ' ORDER BY s.title,u.uni_name LIMIT 500')->fetchAll();
        }
        $mappingEditId = (int)($_GET['mapping_edit_id'] ?? 0);
        if ($tab === 'programs' && $mappingEditId > 0) {
            $st = $catalogDb->prepare('SELECT * FROM specialization_universities WHERE id=:id');
            $st->execute(['id'=>$mappingEditId]);
            $editSpecializationMapping = $st->fetch() ?: null;
        }
        $universityOptions = $catalogDb->query('SELECT id,uni_name FROM uni ORDER BY uni_name')->fetchAll();
    } catch (Throwable $e) {
        error_log('YourUni specialization dashboard: ' . $e->getMessage());
    }
}

function adminH($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function adminValue(array $row, string $key, string $default = ''): string
{
    return adminH($row[$key] ?? $default);
}
function adminSelected(string $current, string $option): string
{
    return $current === $option ? ' selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Center - YourUni</title>
    <style>
        :root { --navy:#121c2d; --blue:#0b5ee7; --blue-dark:#084bb8; --ink:#172033; --muted:#64748b; --line:#e2e8f0; --bg:#f4f7fb; --white:#fff; --green:#15803d; --red:#b91c1c; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Plus Jakarta Sans',Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; background:var(--bg); color:var(--ink); }
        a { color:inherit; }
        .shell { width:min(1500px,94%); margin:28px auto 50px; }
        .header { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:22px 26px; background:var(--navy); color:#fff; border-radius:16px; box-shadow:0 12px 35px rgba(18,28,45,.12); }
        .brand { display:flex; gap:14px; align-items:center; } .brand-icon { width:48px;height:48px;display:grid;place-items:center;border-radius:13px;background:rgba(59,130,246,.2);font-size:25px; }
        h1 { margin:0; font-size:clamp(1.15rem,2.2vw,1.7rem); } h1 span { color:#60a5fa; } .subtitle { margin:5px 0 0;color:#cbd5e1;font-size:.88rem; }
        .header-actions { display:flex; gap:9px; flex-wrap:wrap; } .header-actions a { text-decoration:none;padding:10px 14px;border-radius:9px;font-weight:700;font-size:.88rem;background:var(--blue);color:#fff; } .header-actions a.logout { background:#dc2626; }
        .stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin:20px 0; }
        .stat { background:#fff;border:1px solid var(--line);border-radius:13px;padding:18px 20px; } .stat small { display:block;color:var(--muted);font-weight:700;margin-bottom:8px; } .stat strong { font-size:1.8rem;letter-spacing:-.03em; }
        .tabs { display:flex;gap:7px; flex-wrap:wrap; padding:7px; background:#e8edf5;border-radius:13px;margin:18px 0; }
        .tabs a { text-decoration:none;padding:12px 16px;border-radius:9px;color:#475569;font-weight:800;font-size:.91rem; }
        .tabs a.active { background:#fff;color:var(--blue);box-shadow:0 2px 7px rgba(15,23,42,.07); }
        .notice { padding:13px 16px;border-radius:10px;margin:14px 0;font-weight:650; } .notice.success { background:#dcfce7;color:#166534;border:1px solid #bbf7d0; } .notice.error { background:#fee2e2;color:#991b1b;border:1px solid #fecaca; }
        .mapping-note-dialog { width:min(520px,calc(100% - 32px)); max-height:calc(100% - 32px); padding:0; border:1px solid var(--line); border-radius:14px; background:var(--white); color:var(--ink); box-shadow:0 24px 70px rgba(15,23,42,.28); }
        .mapping-note-dialog::backdrop { background:rgba(15,23,42,.56); backdrop-filter:blur(3px); }
        .mapping-note-head { padding:20px 22px 14px; border-bottom:1px solid var(--line); }
        .mapping-note-head h2 { margin:0; font-size:1.15rem; }
        .mapping-note-context { margin:7px 0 0; color:var(--muted); font-size:.86rem; line-height:1.45; }
        .mapping-note-body { padding:18px 22px; }
        .mapping-note-body label { display:block; margin-bottom:7px; font-size:.82rem; font-weight:800; }
        .mapping-note-body textarea { min-height:130px; }
        .mapping-note-actions { display:flex; justify-content:flex-end; gap:9px; padding:0 22px 20px; }
        @media(max-width:520px) { .mapping-note-head,.mapping-note-body { padding-left:16px;padding-right:16px; } .mapping-note-actions { padding:0 16px 16px; } }
        .layout { display:grid;grid-template-columns:minmax(320px,390px) minmax(0,1fr);gap:18px;align-items:start; }
        .panel { background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;box-shadow:0 4px 16px rgba(15,23,42,.025); }
        .panel-head { padding:19px 21px;border-bottom:1px solid var(--line); } .panel-head h2,.panel-head h3 { margin:0;font-size:1.1rem; } .panel-head p { color:var(--muted);font-size:.86rem;margin:6px 0 0;line-height:1.5; }
        .panel-body { padding:20px; } .form-grid { display:grid;grid-template-columns:1fr;gap:13px; } .field label { display:block;font-size:.82rem;font-weight:800;color:#475569;margin:0 0 6px; }
        input,select,textarea { width:100%;border:1px solid #cbd5e1;border-radius:9px;padding:11px 12px;background:#fff;color:var(--ink);font:inherit;font-size:.91rem;outline:none; } input:focus,select:focus,textarea:focus { border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.12); } textarea { min-height:105px;resize:vertical;line-height:1.5; }
        .helper { color:var(--muted);font-size:.77rem;line-height:1.45;margin-top:5px; } .checkrow { display:flex;align-items:center;gap:9px;font-size:.88rem;font-weight:700; } .checkrow input { width:auto; }
        .btn { display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:9px;padding:11px 14px;font:inherit;font-size:.88rem;font-weight:800;text-decoration:none;cursor:pointer; } .btn-primary { background:var(--blue);color:#fff; } .btn-primary:hover { background:var(--blue-dark); } .btn-secondary { background:#e2e8f0;color:#334155; } .btn-danger { background:#fee2e2;color:#991b1b; } .btn-small { padding:7px 10px;font-size:.8rem; }
        .form-actions { display:flex;gap:8px;flex-wrap:wrap;margin-top:17px; } .full { width:100%; }
        .table-panel { min-width:0; } .table-toolbar { display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 18px;border-bottom:1px solid var(--line); } .table-toolbar h3 { margin:0;font-size:1rem; } .table-toolbar .count { color:var(--muted);font-size:.83rem;white-space:nowrap; }
        .search-form { display:flex;gap:8px;width:min(100%,440px); } .search-form input { min-width:0; } .table-scroll { overflow:auto;max-height:680px; } table { width:100%;border-collapse:collapse;white-space:nowrap;font-size:.85rem; } th { text-align:left;background:#f8fafc;color:#64748b;font-size:.74rem;text-transform:uppercase;letter-spacing:.045em;position:sticky;top:0;z-index:1; } th,td { padding:12px 14px;border-bottom:1px solid #edf1f6;vertical-align:middle; } tbody tr:hover { background:#fbfdff; }
        .thumb { width:54px;height:42px;object-fit:cover;border-radius:7px;background:#eef2f7;border:1px solid var(--line); } .primary-text { font-weight:800;white-space:normal;min-width:170px;max-width:260px;line-height:1.35; } .secondary-text { display:block;color:var(--muted);font-size:.78rem;margin-top:4px;white-space:normal;max-width:250px; } .actions { display:flex;gap:6px;align-items:center; } .inline-form { display:inline; }
        .empty { padding:32px 18px;color:var(--muted);text-align:center; } .tag { display:inline-block;padding:4px 7px;border-radius:20px;background:#eff6ff;color:#1d4ed8;font-size:.74rem;font-weight:800; } .tag.off { background:#f1f5f9;color:#64748b; }
        .tip { margin-top:16px;color:var(--muted);font-size:.82rem;line-height:1.55; } .mobile-search { padding:14px 18px;border-bottom:1px solid var(--line); }
        @media (max-width:1050px) { .layout { grid-template-columns:1fr; } .form-grid.two { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:650px) { .shell { width:94%;margin:14px auto 30px; } .header { align-items:flex-start;flex-direction:column;padding:18px; } .stats { grid-template-columns:1fr;gap:9px; } .stat { padding:14px 16px; } .stat strong { font-size:1.5rem; } .layout { display:block; } .panel { margin-bottom:14px; } .form-grid.two { grid-template-columns:1fr; } .tabs a { flex:1;text-align:center;padding:10px 8px;font-size:.8rem; } .table-toolbar { align-items:flex-start;flex-direction:column; } .search-form { width:100%; } }

        /* Admin theme repair: darken every legacy hard-coded dashboard surface. */
        html[data-theme="dark"] body { background:#0b1220 !important; color:#e5edf7 !important; }
        html[data-theme="dark"] .stat, html[data-theme="dark"] .panel, html[data-theme="dark"] .panel-head, html[data-theme="dark"] .panel-body { background:#111c2d !important; color:#e5edf7 !important; border-color:#2b3a50 !important; }
        html[data-theme="dark"] .tabs { background:#0e1929 !important; border:1px solid #2b3a50; }
        html[data-theme="dark"] .tabs a { color:#cbd5e1 !important; }
        html[data-theme="dark"] .tabs a.active { background:#1e3a5f !important; color:#bfdbfe !important; }
        html[data-theme="dark"] .stat small, html[data-theme="dark"] .panel-head p, html[data-theme="dark"] .helper, html[data-theme="dark"] .secondary-text, html[data-theme="dark"] .tip { color:#a7b4c7 !important; }
        html[data-theme="dark"] .field label { color:#dbe7f5 !important; }
        html[data-theme="dark"] input, html[data-theme="dark"] select, html[data-theme="dark"] textarea { background:#0b1423 !important; color:#e5edf7 !important; border-color:#34445c !important; }
        html[data-theme="dark"] table { color:#e5edf7 !important; }
        html[data-theme="dark"] th { background:#172337 !important; color:#dbeafe !important; border-color:#2b3a50 !important; }
        html[data-theme="dark"] td { border-color:#2b3a50 !important; }
        html[data-theme="dark"] tr:hover td { background:#162337 !important; }
        html[data-theme="dark"] .table-toolbar { background:#0e1929 !important; color:#e5edf7 !important; border-color:#2b3a50 !important; }
        html[data-theme="dark"] .notice.success { background:#123525 !important; color:#bbf7d0 !important; border-color:#166534 !important; }
        html[data-theme="dark"] .notice.error { background:#421a24 !important; color:#fecaca !important; border-color:#7f1d1d !important; }
    </style>
    <link rel="stylesheet" href="assets/youruni-global.css">
    <style>
        /* Admin dashboard dark-theme overrides. Light mode remains unchanged. */
        html[data-theme="dark"] body .shell {
            --ink: var(--yu-text, #e5edf7);
            --muted: var(--yu-muted, #a7b4c7);
            --line: var(--yu-border, #2b3a50);
            --bg: var(--yu-bg, #0b1220);
            --white: var(--yu-surface, #111c2d);
            color: var(--yu-text, #e5edf7) !important;
        }
        html[data-theme="dark"] body .stat {
            background: var(--yu-surface, #111c2d) !important;
            color: var(--yu-text, #e5edf7) !important;
            border-color: var(--yu-border, #2b3a50) !important;
            box-shadow: 0 6px 20px rgba(0,0,0,.12) !important;
        }
        html[data-theme="dark"] body .stats .stat small,
        html[data-theme="dark"] body .stats .stat strong {
            color: var(--yu-text, #e5edf7) !important;
        }
        html[data-theme="dark"] body .stats .stat small {
            color: var(--yu-muted, #a7b4c7) !important;
        }
        html[data-theme="dark"] body nav.tabs {
            background: #080f1c !important;
            border: 1px solid #1b2a40 !important;
        }
        html[data-theme="dark"] body nav.tabs a {
            color: #c2cede !important;
            border: 1px solid transparent !important;
        }
        html[data-theme="dark"] body nav.tabs a:hover {
            background: #132239 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] body nav.tabs a.active {
            background: #17345b !important;
            color: #93c5fd !important;
            border-color: #2b5b91 !important;
            box-shadow: none !important;
        }
        html[data-theme="dark"] body .panel,
        html[data-theme="dark"] body .table-panel {
            background: var(--yu-surface, #111c2d) !important;
            color: var(--yu-text, #e5edf7) !important;
            border-color: var(--yu-border, #2b3a50) !important;
        }
        html[data-theme="dark"] body .panel-head,
        html[data-theme="dark"] body .table-toolbar,
        html[data-theme="dark"] body .mobile-search {
            background: #111c2d !important;
            color: #e5edf7 !important;
            border-color: #2b3a50 !important;
        }
        html[data-theme="dark"] body .panel-head p,
        html[data-theme="dark"] body .table-toolbar .count,
        html[data-theme="dark"] body .helper,
        html[data-theme="dark"] body .tip,
        html[data-theme="dark"] body .secondary-text {
            color: var(--yu-muted, #a7b4c7) !important;
        }
        html[data-theme="dark"] body .field label,
        html[data-theme="dark"] body .checkrow {
            color: #dbe6f4 !important;
        }
        html[data-theme="dark"] body input,
        html[data-theme="dark"] body select,
        html[data-theme="dark"] body textarea {
            background: #0b1626 !important;
            color: #e5edf7 !important;
            border-color: #34445c !important;
            color-scheme: dark;
        }
        html[data-theme="dark"] body input::placeholder,
        html[data-theme="dark"] body textarea::placeholder {
            color: #91a0b5 !important;
            opacity: 1;
        }
        html[data-theme="dark"] body .btn.btn-secondary,
        html[data-theme="dark"] body a.btn.btn-secondary {
            background: #243449 !important;
            color: #e5edf7 !important;
            border: 1px solid #3a4c63 !important;
        }
        html[data-theme="dark"] body .btn.btn-danger {
            background: #4a202b !important;
            color: #fecaca !important;
            border: 1px solid #7f3545 !important;
        }
        html[data-theme="dark"] body table {
            background: #111c2d !important;
            color: #e5edf7 !important;
        }
        html[data-theme="dark"] body .table-panel table th {
            background: #19273a !important;
            color: #aebfd4 !important;
            border-color: #2b3a50 !important;
        }
        html[data-theme="dark"] body .table-panel table td {
            background: #111c2d !important;
            color: #e5edf7 !important;
            border-color: #2b3a50 !important;
        }
        html[data-theme="dark"] body .table-panel table tbody tr:hover td {
            background: #17263b !important;
        }
        html[data-theme="dark"] body .table-panel .primary-text,
        html[data-theme="dark"] body .table-panel td strong {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] body .tag {
            background: #17345b !important;
            color: #bfdbfe !important;
            border-color: #2b5b91 !important;
        }
        html[data-theme="dark"] body .tag.off {
            background: #253246 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] body .notice.success {
            background: #123324 !important;
            color: #bbf7d0 !important;
            border-color: #24583c !important;
        }
        html[data-theme="dark"] body .notice.error {
            background: #3a1d26 !important;
            color: #fecaca !important;
            border-color: #71303d !important;
        }
        html[data-theme="dark"] body .empty {
            color: #a7b4c7 !important;
        }
        html[data-theme="dark"] body code {
            color: #bfdbfe !important;
            background: #0b1626 !important;
        }
    </style>
        <link rel="stylesheet" href="assets/admin.css">
    </head>
    <body class="admin-dashboard-page">
<div class="shell">
    <header class="header">
        <div class="brand"><div class="brand-icon">🎓</div><div><h1>Your<span>Uni</span> Admin Control Center</h1><p class="subtitle">Manage featured cards, universities, opportunity categories, and database-driven specializations.</p></div></div>
        <div class="header-actions"><a href="admin_accounts.php">Manage admins</a><a href="change_password.php">Change password</a><a href="index.php" target="_blank" rel="noopener">View live site ↗</a><a class="logout" href="logout.php">Log out</a></div>
    </header>

    <section class="stats" aria-label="Content totals">
        <div class="stat"><small>Homepage featured cards</small><strong><?= (int)$totalFeatured ?></strong></div>
        <div class="stat"><small>Universities in catalogue</small><strong><?= (int)$totalUniversities ?></strong></div>
        <div class="stat"><small>Opportunity cards</small><strong><?= (int)$totalOpportunities ?></strong></div>
    </section>

    <nav class="tabs" aria-label="Admin sections">
        <a class="<?= $tab === 'featured' ? 'active' : '' ?>" href="admin_dashboard.php?tab=featured">🏠 Homepage Featured</a>
        <a class="<?= $tab === 'universities' ? 'active' : '' ?>" href="admin_dashboard.php?tab=universities">🏛️ University Catalogue</a>
        <a class="<?= $tab === 'opportunities' ? 'active' : '' ?>" href="admin_dashboard.php?tab=opportunities">🧭 Opportunities</a>
        <a class="<?= $tab === 'programs' ? 'active' : '' ?>" href="admin_dashboard.php?tab=programs">🧩 Majors & Specializations</a>
    </nav>

    <?php if ($flash): ?><div class="notice <?= adminH($flash['type'] ?? 'success') ?>"><?= adminH($flash['message'] ?? '') ?></div><?php endif; ?>
    <?php if ($catalogError !== ''): ?><div class="notice error"><?= adminH($catalogError) ?></div><?php endif; ?>

    <?php if ($tab === 'featured'): ?>
        <div class="layout">
            <section class="panel" id="editor">
                <div class="panel-head"><h2><?= $editFeatured ? 'Edit homepage card' : 'Add homepage featured card' ?></h2><p>Add a curated university pick here. These cards appear first on Home; the remaining slots are filled from the popularity-sorted university catalogue.</p></div>
                <div class="panel-body">
                    <form action="admin_dashboard.php?tab=featured#editor" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="featured"><input type="hidden" name="action" value="featured_save"><input type="hidden" name="id" value="<?= (int)($editFeatured['id'] ?? 0) ?>">
                        <div class="form-grid">
                            <div class="field"><label>Card / Program title *</label><input name="title" required maxlength="255" value="<?= adminValue($editFeatured ?? [], 'title') ?>" placeholder="e.g. Top Engineering Universities"></div>
                            <div class="field"><label>University name *</label><input name="university_name" required maxlength="255" value="<?= adminValue($editFeatured ?? [], 'university_name') ?>" placeholder="e.g. Yangon Technological University"></div>
                            <div class="field"><label>Major / category *</label><input name="major" required maxlength="100" value="<?= adminValue($editFeatured ?? [], 'major') ?>" placeholder="e.g. Engineering"></div>
                            <div class="field"><label>Region / state *</label><input name="zone" required maxlength="100" value="<?= adminValue($editFeatured ?? [], 'zone') ?>" placeholder="e.g. Yangon"></div>
                            <div class="field"><label>Minimum marks *</label><input type="number" name="min_marks" required min="0" max="1000" value="<?= adminValue($editFeatured ?? [], 'min_marks', '400') ?>"></div>
                            <div class="field"><label>Detail page link *</label><input name="page_link" required maxlength="255" value="<?= adminValue($editFeatured ?? [], 'page_link', 'YTU.html') ?>" placeholder="YTU.html"></div>
                            <div class="field"><label>Current image path or image URL</label><input name="image_url" maxlength="255" value="<?= adminValue($editFeatured ?? [], 'image_url', 'photos/default.jpg') ?>" placeholder="photos/ytu.jpg"><div class="helper">You can use an existing path from your photos folder, or upload a new image below.</div></div>
                            <div class="field"><label>Upload a new image (optional, max 5 MB)</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp"></div>
                        </div>
                        <div class="featured-preview-wrap" aria-live="polite">
                            <span class="preview-label">Homepage card preview</span>
                            <article class="uni-card featured-preview">
                                <img class="card-img" id="featured-preview-image" src="<?= adminValue($editFeatured ?? [], 'image_url', 'photos/default.jpg') ?>" alt="">
                                <div class="card-content"><div class="uni-info">
                                    <h5 id="featured-preview-university"><?= adminValue($editFeatured ?? [], 'university_name', 'University name') ?></h5>
                                    <p><span id="featured-preview-region"><?= adminValue($editFeatured ?? [], 'zone', 'Region') ?></span> · <span id="featured-preview-title"><?= adminValue($editFeatured ?? [], 'title', 'Featured title') ?></span></p>
                                    <span class="tag" id="featured-preview-major"><?= adminValue($editFeatured ?? [], 'major', 'Study field') ?></span>
                                    <span class="preview-marks">Minimum marks: <strong id="featured-preview-marks"><?= adminValue($editFeatured ?? [], 'min_marks', '400') ?></strong></span>
                                </div></div>
                            </article>
                            <p class="helper">Featured cards appear first in the homepage university section. The remaining catalogue universities follow them.</p>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editFeatured ? 'Save changes' : 'Add featured card' ?></button><?php if ($editFeatured): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=featured#editor">Cancel edit</a><?php endif; ?></div>
                    </form>
                </div>
            </section>
            <section class="panel table-panel">
                <div class="table-toolbar"><h3>Homepage featured cards</h3><span class="count"><?= count($featuredRows) ?> records</span></div>
                <div class="table-scroll"><table><thead><tr><th>Image</th><th>Title / university</th><th>Major</th><th>Region</th><th>Marks</th><th>Actions</th></tr></thead><tbody>
                <?php if (!$featuredRows): ?><tr><td colspan="6" class="empty">No homepage cards yet. Use the form to add one.</td></tr><?php endif; ?>
                <?php foreach ($featuredRows as $row): ?><tr><td><img class="thumb" src="<?= adminH($row['image_url'] ?: 'photos/default.jpg') ?>" alt=""></td><td><div class="primary-text"><?= adminH($row['title']) ?></div><span class="secondary-text"><?= adminH($row['university_name']) ?></span></td><td><?= adminH($row['major']) ?></td><td><?= adminH($row['zone']) ?></td><td><?= (int)$row['min_marks'] ?></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="admin_dashboard.php?tab=featured&edit_id=<?= (int)$row['id'] ?>#editor">Edit</a><form class="inline-form" method="POST" action="admin_dashboard.php?tab=featured"><input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="featured"><input type="hidden" name="action" value="featured_delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger btn-small" onclick="return confirm('Delete this homepage card?')">Delete</button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </section>
        </div>

    <?php elseif ($tab === 'universities'): ?>
        <div class="layout">
            <section class="panel" id="editor">
                <div class="panel-head"><h2><?= $editUniversity ? 'Edit catalogue university' : 'Add to university catalogue' ?></h2><p>This section controls the university catalogue used by Universities.php and Smart Search. Use Majors & Specializations to manage study areas and university offering records.</p></div>
                <div class="panel-body">
                    <?php if (!$catalogDb): ?><p class="helper">Import <code>youruni_complete.sql</code> and check the shared YourUni database connection before editing catalogue records.</p><?php else: ?>
                    <form action="admin_dashboard.php?tab=universities#editor" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="universities"><input type="hidden" name="action" value="university_save"><input type="hidden" name="id" value="<?= (int)($editUniversity['id'] ?? 0) ?>">
                        <div class="form-grid">
                            <div class="field"><label>University name *</label><input name="uni_name" required maxlength="255" value="<?= adminValue($editUniversity ?? [], 'uni_name') ?>" placeholder="e.g. Yangon Technological University"></div>
                            <div class="field"><label>Major / category *</label><input name="uni_major" required maxlength="100" value="<?= adminValue($editUniversity ?? [], 'major') ?>" placeholder="e.g. Engineering"></div>
                            <div class="field"><label>Region / state *</label><input name="region" required maxlength="100" value="<?= adminValue($editUniversity ?? [], 'region') ?>" placeholder="e.g. Yangon"></div>
                            <div class="field"><label>Admission requirement status *</label><select name="admission_status" required><option value="known"<?= adminSelected((string)($editUniversity['admission_status'] ?? ((int)($editUniversity['min_marks'] ?? 0)>0?'known':'unknown')), 'known') ?>>Known minimum marks</option><option value="pass_based"<?= adminSelected((string)($editUniversity['admission_status'] ?? 'unknown'), 'pass_based') ?>>Pass-based (confirmed)</option><option value="unknown"<?= adminSelected((string)($editUniversity['admission_status'] ?? 'unknown'), 'unknown') ?>>Not verified / unknown</option></select><div class="helper">Do not choose pass-based unless official information confirms it.</div></div>
                            <div class="field"><label>Minimum marks</label><input type="number" name="uni_min_marks" min="0" max="1000" value="<?= adminValue($editUniversity ?? [], 'min_marks', '400') ?>"><div class="helper">Required for Known minimum marks; ignored for Pass-based/Unknown.</div></div>
                            <div class="field"><label>Official source URL</label><input type="url" name="source_url" maxlength="500" value="<?= adminValue($editUniversity ?? [], 'source_url') ?>" placeholder="https://official-university-site..."></div>
                            <div class="field"><label>Last verified date</label><input type="date" name="last_verified_at" value="<?= adminValue($editUniversity ?? [], 'last_verified_at') ?>"></div>
                            <div class="field"><label>Image path *</label><input name="image_path" required maxlength="255" value="<?= adminValue($editUniversity ?? [], 'image_path', 'photos/ytu.jpg') ?>" placeholder="photos/ytu.jpg"></div>
                            <div class="field"><label>Detail page filename *</label><input name="uni_page" required maxlength="255" value="<?= adminValue($editUniversity ?? [], 'page', 'YTU.html') ?>" placeholder="YTU.html"></div>
                            <div class="field"><label>Sort order / popularity</label><input type="number" name="popularity" min="0" value="<?= adminValue($editUniversity ?? [], 'popularity', '100') ?>"><div class="helper">Lower values appear earlier in the current directory ordering.</div></div>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editUniversity ? 'Save university changes' : 'Add university' ?></button><?php if ($editUniversity): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=universities#editor">Cancel edit</a><?php endif; ?></div>
                    </form><?php endif; ?>
                </div>
            </section>
            <section class="panel table-panel">
                <div class="table-toolbar"><h3>Full university catalogue</h3><span class="count"><?= (int)$totalUniversities ?> total</span></div>
                <div class="mobile-search"><form class="search-form" action="admin_dashboard.php" method="GET"><input type="hidden" name="tab" value="universities"><input name="q" value="<?= adminH($universitySearch) ?>" placeholder="Search name, major, or region"><button class="btn btn-primary" type="submit">Search</button><?php if ($universitySearch !== ''): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=universities">Clear</a><?php endif; ?></form></div>
                <div class="table-scroll"><table><thead><tr><th>University</th><th>Major</th><th>Region</th><th>Marks</th><th>Detail page</th><th>Actions</th></tr></thead><tbody>
                <?php if (!$catalogDb): ?><tr><td colspan="6" class="empty">The shared YourUni database is unavailable.</td></tr><?php elseif (!$universityRows): ?><tr><td colspan="6" class="empty">No matching universities were found.</td></tr><?php endif; ?>
                <?php foreach ($universityRows as $row): ?><tr><td><div class="primary-text"><?= adminH($row['uni_name']) ?></div><span class="secondary-text">Image: <?= adminH($row['image_path']) ?></span></td><td><?= adminH($row['major']) ?></td><td><?= adminH($row['region']) ?></td><td><?= (int)$row['min_marks'] ?></td><td><code><?= adminH($row['page']) ?></code></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="admin_dashboard.php?tab=universities&edit_id=<?= (int)$row['id'] ?>#editor">Edit</a><form class="inline-form" method="POST" action="admin_dashboard.php?tab=universities"><input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="universities"><input type="hidden" name="action" value="university_delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger btn-small" onclick="return confirm('Remove this university from the full catalogue?')">Delete</button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </section>
        </div>

    <?php elseif ($tab === 'opportunities'): ?>
        <div class="layout">
            <section class="panel" id="editor">
                <div class="panel-head"><h2><?= $editOpportunity ? 'Edit opportunity card' : 'Add opportunity card' ?></h2><p>These records drive the public Opportunities page. You can edit the title, description, destination, sample universities, order, and visibility.</p></div>
                <div class="panel-body">
                    <?php if (!$catalogDb || !$opportunitiesReady): ?><p class="helper">The opportunities table could not be initialized. Check your MySQL connection and import youruni_complete.sql.</p><?php else: ?>
                    <form action="admin_dashboard.php?tab=opportunities#editor" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="opportunities"><input type="hidden" name="action" value="opportunity_save"><input type="hidden" name="id" value="<?= (int)($editOpportunity['id'] ?? 0) ?>">
                        <div class="form-grid">
                            <div class="field"><label>Opportunity / field title *</label><input name="opp_title" required maxlength="180" value="<?= adminValue($editOpportunity ?? [], 'title') ?>" placeholder="e.g. Architecture & Design"></div>
                            <div class="field"><label>Icon (emoji)</label><input name="opp_icon" maxlength="32" value="<?= adminValue($editOpportunity ?? [], 'icon', '🎓') ?>" placeholder="🎓"></div>
                            <div class="field"><label>Description *</label><textarea name="opp_description" required maxlength="4000" placeholder="Describe this field or opportunity..."><?= adminValue($editOpportunity ?? [], 'description') ?></textarea></div>
                            <div class="field"><label>Example universities</label><input name="university_examples" maxlength="255" value="<?= adminValue($editOpportunity ?? [], 'university_examples') ?>" placeholder="YTU • MTU • UTYCC"></div>
                            <div class="field"><label>Destination page *</label><input name="opp_page_link" required maxlength="255" value="<?= adminValue($editOpportunity ?? [], 'page_link') ?>" placeholder="engineering.html"></div>
                            <div class="field"><label>Card color/style</label><select name="css_class"><?php $styles = ['card-eng'=>'Engineering / amber','card-cs'=>'Computer science / blue','card-med'=>'Healthcare / green','card-biz'=>'Business / purple','card-lang'=>'Languages / rose','card-law'=>'Law / teal','card-agri'=>'Agriculture / lime','card-edu'=>'Education / amber','card-art'=>'Arts / rose','card-science'=>'Science / cyan','card-defense'=>'Defense / slate']; $currentStyle = (string)($editOpportunity['css_class'] ?? 'card-cs'); foreach ($styles as $value => $label): ?><option value="<?= adminH($value) ?>"<?= adminSelected($currentStyle, $value) ?>><?= adminH($label) ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label>Display order</label><input type="number" name="sort_order" value="<?= adminValue($editOpportunity ?? [], 'sort_order', (string)($totalOpportunities + 1)) ?>"></div>
                            <label class="checkrow"><input type="checkbox" name="is_active" value="1"<?= !isset($editOpportunity['is_active']) || (int)$editOpportunity['is_active'] === 1 ? ' checked' : '' ?>> Show this card on the public page</label>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editOpportunity ? 'Save opportunity changes' : 'Add opportunity' ?></button><?php if ($editOpportunity): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=opportunities#editor">Cancel edit</a><?php endif; ?></div>
                    </form><?php endif; ?>
                </div>
            </section>
            <section class="panel table-panel">
                <div class="table-toolbar"><h3>Opportunities page cards</h3><span class="count"><?= (int)$totalOpportunities ?> records</span></div>
                <div class="table-scroll"><table><thead><tr><th>Icon</th><th>Title / description</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                <?php if (!$catalogDb || !$opportunitiesReady): ?><tr><td colspan="5" class="empty">Import youruni_complete.sql before managing majors and specializations.</td></tr><?php elseif (!$opportunityRows): ?><tr><td colspan="5" class="empty">No opportunity cards found. Add the first one using the form.</td></tr><?php endif; ?>
                <?php foreach ($opportunityRows as $row): ?><tr><td style="font-size:1.45rem"><?= adminH($row['icon']) ?></td><td><div class="primary-text"><?= adminH($row['title']) ?></div><span class="secondary-text"><?= adminH($row['description']) ?></span></td><td><?= (int)$row['sort_order'] ?></td><td><span class="tag <?= (int)$row['is_active'] === 1 ? '' : 'off' ?>"><?= (int)$row['is_active'] === 1 ? 'Published' : 'Hidden' ?></span></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="admin_dashboard.php?tab=opportunities&edit_id=<?= (int)$row['id'] ?>#editor">Edit</a><form class="inline-form" method="POST" action="admin_dashboard.php?tab=opportunities"><input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="opportunities"><input type="hidden" name="action" value="opportunity_delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger btn-small" onclick="return confirm('Delete this opportunity card?')">Delete</button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </section>
        </div>
    <?php else: ?>
        <div class="layout">
            <section class="panel" id="editor">
                <div class="panel-head"><h2><?= $editSpecialization ? 'Edit major / specialization' : 'Add a major / specialization' ?></h2><p>Create a program entry under an Opportunities category. Publishing the entry does not claim a university offers it; university availability is managed separately below.</p></div>
                <div class="panel-body">
                    <?php if (!$catalogDb): ?><p class="helper">The shared database is unavailable. Check the database connection and import youruni_complete.sql.</p><?php else: ?>
                    <form action="admin_dashboard.php?tab=programs#editor" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="programs"><input type="hidden" name="action" value="specialization_save"><input type="hidden" name="id" value="<?= (int)($editSpecialization['id'] ?? 0) ?>">
                        <div class="form-grid">
                            <div class="field"><label>Opportunities category *</label><select name="specialization_category_id" required><?php foreach ($categoryOptions as $category): ?><option value="<?= (int)$category['id'] ?>"<?= ((int)($editSpecialization['opportunity_id'] ?? 0)===(int)$category['id'])?' selected':'' ?>><?= adminH($category['title']) ?><?= (int)$category['is_active']===1?'':' (hidden)' ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label>Specialization name *</label><input name="specialization_title" required maxlength="180" value="<?= adminValue($editSpecialization ?? [], 'title') ?>" placeholder="Precision Engineering"></div>
                            <div class="field"><label>Specialization name — Burmese (optional)</label><input name="specialization_title_my" maxlength="180" value="<?= adminValue($editSpecialization ?? [], 'title_my') ?>" placeholder="မြန်မာဘာသာဖြင့် အမည်"></div>
                            <div class="field"><label>URL / program key</label><input name="specialization_slug" maxlength="120" value="<?= adminValue($editSpecialization ?? [], 'slug') ?>" placeholder="precision-engineering"><div class="helper">Leave blank when adding to generate a key from the name. Avoid changing it after pages or links depend on it.</div></div>
                            <div class="field"><label>Short description *</label><textarea name="specialization_description" maxlength="3000" required placeholder="Explain what this study area covers."><?= adminValue($editSpecialization ?? [], 'description') ?></textarea></div>
                            <div class="field"><label>Short description — Burmese (optional)</label><textarea name="specialization_description_my" maxlength="3000" placeholder="မြန်မာဘာသာဖြင့် ဖော်ပြချက်"><?= adminValue($editSpecialization ?? [], 'description_my') ?></textarea></div>
                            <div class="field"><label>Related study / career context</label><textarea name="specialization_work_context" maxlength="3000" placeholder="Optional detail shown on the category page."><?= adminValue($editSpecialization ?? [], 'work_context') ?></textarea></div>
                            <div class="field"><label>Related context — Burmese (optional)</label><textarea name="specialization_work_context_my" maxlength="3000" placeholder="ဆက်စပ်အချက်အလက်ကို မြန်မာဘာသာဖြင့် ဖော်ပြပါ"><?= adminValue($editSpecialization ?? [], 'work_context_my') ?></textarea></div>
                            <div class="field"><label>Icon (emoji or short symbol)</label><input name="specialization_icon" maxlength="32" value="<?= adminValue($editSpecialization ?? [], 'icon', '🎓') ?>" placeholder="🎯"></div>
                            <div class="field"><label>Badge label</label><input name="specialization_badge" maxlength="80" value="<?= adminValue($editSpecialization ?? [], 'badge_text', 'Major / Specialization') ?>" placeholder="Major / Specialization"></div>
                            <div class="field"><label>Display order</label><input name="specialization_sort_order" type="number" min="0" max="9999" value="<?= adminH($editSpecialization['sort_order'] ?? 100) ?>"></div>
                            <label class="checkrow"><input type="checkbox" name="specialization_is_active" value="1"<?= !isset($editSpecialization['is_active']) || (int)$editSpecialization['is_active']===1?' checked':'' ?>> Publish this specialization on its category page</label>
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editSpecialization ? 'Save specialization' : 'Add specialization' ?></button><?php if ($editSpecialization): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=programs#editor">Cancel edit</a><?php endif; ?></div>
                    </form><?php endif; ?>
                </div>
            </section>
            <section class="panel table-panel">
                <div class="table-toolbar"><h3>Majors & specializations</h3><span class="count"><?= (int)$totalSpecializations ?> entries</span></div>
                <div class="panel-body"><form class="search-form" action="admin_dashboard.php" method="get"><input type="hidden" name="tab" value="programs"><input name="specialization_q" value="<?= adminH($specializationSearch) ?>" placeholder="Search name or category"><button class="btn btn-primary" type="submit">Search</button></form></div>
                <div class="table-scroll"><table><thead><tr><th>Specialization</th><th>Category</th><th>Universities linked</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                <?php if (!$specializationRows): ?><tr><td colspan="5" class="empty">No specialization records found. Add your first entry using the form.</td></tr><?php endif; ?>
                <?php foreach ($specializationRows as $row): ?><tr><td><div class="primary-text"><?= adminH($row['icon']) ?> <?= adminH($row['title']) ?></div><span class="secondary-text"><?= adminH($row['description']) ?></span><span class="secondary-text"><code><?= adminH($row['slug']) ?></code></span></td><td><?= adminH($row['category_title']) ?></td><td><?= (int)$row['mapping_count'] ?></td><td><span class="tag <?= (int)$row['is_active']===1?'':'off' ?>"><?= (int)$row['is_active']===1?'Published':'Hidden' ?></span></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="admin_dashboard.php?tab=programs&edit_id=<?= (int)$row['id'] ?>#editor">Edit</a><form class="inline-form" method="POST" action="admin_dashboard.php?tab=programs"><input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="programs"><input type="hidden" name="action" value="specialization_delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger btn-small" onclick="return confirm('Delete this specialization and its university mappings?')">Delete</button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </section>
        </div>

        <div class="layout" style="margin-top:18px;">
            <section class="panel" id="mapping-editor">
                <div class="panel-head"><h2><?= $editSpecializationMapping ? 'Edit university mapping' : 'Link a university to a specialization' ?></h2><p>Select a university and record whether the specialization is confirmed, confirmed unavailable, or still unverified. A published specialization and a confirmed university offering are different things.</p></div>
                <div class="panel-body">
                    <form action="admin_dashboard.php?tab=programs#mapping-editor" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="programs"><input type="hidden" name="action" value="specialization_mapping_save"><input type="hidden" name="mapping_id" value="<?= (int)($editSpecializationMapping['id'] ?? 0) ?>">
                        <div class="form-grid">
                            <div class="field"><label>Specialization *</label><select name="mapping_specialization_id" required><option value="">Choose specialization</option><?php foreach ($specializationOptions as $option): ?><option value="<?= (int)$option['id'] ?>"<?= ((int)($editSpecializationMapping['specialization_id'] ?? 0)===(int)$option['id'])?' selected':'' ?>><?= adminH($option['title']) ?><?= (int)$option['is_active']===1?'':' (hidden)' ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label>University *</label><select name="mapping_uni_id" required><option value="">Choose university</option><?php foreach ($universityOptions as $u): ?><option value="<?= (int)$u['id'] ?>"<?= ((int)($editSpecializationMapping['uni_id'] ?? 0)===(int)$u['id'])?' selected':'' ?>><?= adminH($u['uni_name']) ?></option><?php endforeach; ?></select></div>
                            <div class="field"><label>Availability *</label><select name="mapping_status" required><option value="unverified"<?= adminSelected((string)($editSpecializationMapping['status'] ?? 'unverified'),'unverified') ?>>Unverified — needs confirmation</option><option value="offered"<?= adminSelected((string)($editSpecializationMapping['status'] ?? 'unverified'),'offered') ?>>Offered — confirmed</option><option value="not_offered"<?= adminSelected((string)($editSpecializationMapping['status'] ?? 'unverified'),'not_offered') ?>>Not offered — confirmed</option></select></div>
                            <div class="field"><label>Official source URL</label><input type="url" name="mapping_source_url" maxlength="500" value="<?= adminValue($editSpecializationMapping ?? [], 'source_url') ?>" placeholder="https://official university source"></div>
                            <div class="field"><label>Last verified date</label><input type="date" name="mapping_verified_at" value="<?= adminValue($editSpecializationMapping ?? [], 'last_verified_at') ?>"></div>
                            <input type="hidden" name="mapping_notes" value="<?= adminValue($editSpecializationMapping ?? [], 'notes') ?>">
                        </div>
                        <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editSpecializationMapping ? 'Save mapping' : 'Save university mapping' ?></button><?php if ($editSpecializationMapping): ?><a class="btn btn-secondary" href="admin_dashboard.php?tab=programs#mapping-editor">Cancel edit</a><?php endif; ?></div>
                    </form>
                    <dialog class="mapping-note-dialog" id="mapping-note-dialog" aria-labelledby="mapping-note-title">
                        <div class="mapping-note-head"><h2 id="mapping-note-title">Add a verification note</h2><p class="mapping-note-context" id="mapping-note-context">Add context for this university and specialization.</p></div>
                        <div class="mapping-note-body"><label for="mapping-note-input">Your note (optional)</label><textarea id="mapping-note-input" maxlength="2000" placeholder="Add a detail, source description, or reminder..."></textarea></div>
                        <div class="mapping-note-actions"><button class="btn btn-secondary" type="button" id="mapping-note-cancel">Cancel</button><button class="btn btn-primary" type="button" id="mapping-note-save">Save mapping</button></div>
                    </dialog>
                </div>
            </section>
            <section class="panel table-panel">
                <div class="table-toolbar"><h3>University availability mappings</h3><span class="count"><?= (int)$totalSpecializationMappings ?> records</span></div>
                <div class="panel-body"><form class="search-form" action="admin_dashboard.php" method="get"><input type="hidden" name="tab" value="programs"><input name="mapping_q" value="<?= adminH($mappingSearch) ?>" placeholder="Search specialization or university"><button class="btn btn-primary" type="submit">Search</button></form></div>
                <div class="table-scroll"><table><thead><tr><th>Specialization / category</th><th>University</th><th>Status</th><th>Source / verified</th><th>Note</th><th>Actions</th></tr></thead><tbody>
                <?php if (!$mappingRows): ?><tr><td colspan="6" class="empty">No university mappings yet. Add a mapping using the form.</td></tr><?php endif; ?>
                <?php foreach ($mappingRows as $row): ?><tr><td><div class="primary-text"><?= adminH($row['specialization_title']) ?></div><span class="secondary-text"><?= adminH($row['category_title']) ?></span></td><td><?= adminH($row['uni_name']) ?></td><td><span class="tag <?= $row['status']==='offered'?'':($row['status']==='unverified'?'off':'off') ?>"><?= adminH(str_replace('_',' ',$row['status'])) ?></span></td><td><?php if (!empty($row['source_url'])): ?><a href="<?= adminH($row['source_url']) ?>" target="_blank" rel="noopener">Source ↗</a><?php else: ?><span class="secondary-text">No source recorded</span><?php endif; ?><br><span class="secondary-text"><?= adminH($row['last_verified_at'] ?: 'Not dated') ?></span></td><td><span class="secondary-text"><?= adminH($row['notes'] ?: '—') ?></span></td><td><div class="actions"><a class="btn btn-secondary btn-small" href="admin_dashboard.php?tab=programs&mapping_edit_id=<?= (int)$row['id'] ?>#mapping-editor">Edit</a><form class="inline-form" method="POST" action="admin_dashboard.php?tab=programs"><input type="hidden" name="csrf_token" value="<?= adminH($csrfToken) ?>"><input type="hidden" name="tab" value="programs"><input type="hidden" name="action" value="specialization_mapping_delete"><input type="hidden" name="mapping_id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger btn-small" onclick="return confirm('Delete this university mapping?')">Delete</button></form></div></td></tr><?php endforeach; ?>
                </tbody></table></div>
            </section>
        </div>
    <?php endif; ?>


    <p class="tip">Tip: Homepage Featured controls the homepage cards; University Catalogue controls the public directory and search; Opportunities controls category cards; Majors & Specializations manages study areas and university availability records. Confirm information from official sources before marking a program offered or not offered.</p>
</div>
    <script>
        (() => {
            const form = document.querySelector('#mapping-editor form[action*="mapping-editor"]');
            const dialog = document.getElementById('mapping-note-dialog');
            if (!form || !dialog) return;

            const noteValue = form.querySelector('[name="mapping_notes"]');
            const noteInput = document.getElementById('mapping-note-input');
            const context = document.getElementById('mapping-note-context');
            const statusField = form.querySelector('[name="mapping_status"]');
            const specializationField = form.querySelector('[name="mapping_specialization_id"]');
            const universityField = form.querySelector('[name="mapping_uni_id"]');

            form.addEventListener('submit', event => {
                if (form.dataset.noteConfirmed === 'true') {
                    delete form.dataset.noteConfirmed;
                    return;
                }

                event.preventDefault();
                noteInput.value = noteValue.value;
                const specialization = specializationField.selectedOptions[0]?.textContent.trim() || 'Specialization';
                const university = universityField.selectedOptions[0]?.textContent.trim() || 'University';
                const status = statusField.selectedOptions[0]?.textContent.trim() || 'Availability';
                context.textContent = `${specialization} · ${university} · ${status}`;
                dialog.showModal();
                noteInput.focus();
            });

            document.getElementById('mapping-note-cancel').addEventListener('click', () => dialog.close());
            document.getElementById('mapping-note-save').addEventListener('click', () => {
                noteValue.value = noteInput.value.trim();
                form.dataset.noteConfirmed = 'true';
                dialog.close();
                form.requestSubmit();
            });
        })();
    </script>
    <script>
        (() => {
            const form = document.querySelector('#editor form[action*="tab=featured"]');
            if (!form) return;
            const fields = {
                title: form.querySelector('[name="title"]'),
                university: form.querySelector('[name="university_name"]'),
                major: form.querySelector('[name="major"]'),
                region: form.querySelector('[name="zone"]'),
                marks: form.querySelector('[name="min_marks"]'),
                image: form.querySelector('[name="image_url"]'),
                upload: form.querySelector('[name="image_file"]')
            };
            const preview = {
                title: document.getElementById('featured-preview-title'),
                university: document.getElementById('featured-preview-university'),
                major: document.getElementById('featured-preview-major'),
                region: document.getElementById('featured-preview-region'),
                marks: document.getElementById('featured-preview-marks'),
                image: document.getElementById('featured-preview-image')
            };
            const update = () => {
                preview.title.textContent = fields.title.value.trim() || 'Featured title';
                preview.university.textContent = fields.university.value.trim() || 'University name';
                preview.major.textContent = fields.major.value.trim() || 'Study field';
                preview.region.textContent = fields.region.value.trim() || 'Region';
                preview.marks.textContent = fields.marks.value || '0';
                preview.image.src = fields.image.value.trim() || 'photos/default.jpg';
            };
            Object.values(fields).forEach(field => {
                if (field && field.type !== 'file') field.addEventListener('input', update);
            });
            fields.upload.addEventListener('change', () => {
                if (fields.upload.files && fields.upload.files[0]) {
                    preview.image.src = URL.createObjectURL(fields.upload.files[0]);
                }
            });
            update();
        })();
    </script>
    <script src="assets/youruni-global.js" defer></script>
</body>
</html>
