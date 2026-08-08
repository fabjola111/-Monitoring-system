<?php
require_once "configuration.php";
include("auth.php");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, emri, mbiemri, email, roli, prind_id, foto_profil FROM perdorues WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("Përdoruesi nuk u gjet.");
}

function upload_error_message($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "Fotoja është shumë e madhe për limitin e serverit.";
        case UPLOAD_ERR_PARTIAL:
            return "Fotoja u ngarkua pjesërisht. Provo përsëri.";
        case UPLOAD_ERR_NO_FILE:
            return "Nuk u zgjodh asnjë foto.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Mungon folderi i përkohshëm në server.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Serveri nuk mund ta ruajë foton.";
        case UPLOAD_ERR_EXTENSION:
            return "Ngarkimi u ndalua nga një extension i PHP.";
        default:
            return "Dështoi ngarkimi i fotos.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emri = trim($_POST['emri'] ?? '');
    $mbiemri = trim($_POST['mbiemri'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$emri || !$mbiemri || !$email) {
        $error = "Plotësoni të gjitha fushat!";
    } elseif (!preg_match('/^[a-zA-ZëËçÇ\s]{2,50}$/u', $emri)) {
        $error = "Emri nuk është i vlefshëm.";
    } elseif (!preg_match('/^[a-zA-ZëËçÇ\s]{2,50}$/u', $mbiemri)) {
        $error = "Mbiemri nuk është i vlefshëm.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email-i nuk është i vlefshëm.";
    } else {
        $foto_path = $user['foto_profil'];

        if (!empty($_FILES['foto']['name'])) {

            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $error = upload_error_message($_FILES['foto']['error']);
            } else {
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];

                $allowed_mime = [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'image/heic',
                    'image/heif',
                    'image/heic-sequence',
                    'image/heif-sequence'
                ];

                $original_name = $_FILES['foto']['name'];
                $tmp_name = $_FILES['foto']['tmp_name'];
                $file_size = $_FILES['foto']['size'];

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                $mime = '';
                if (function_exists('mime_content_type')) {
                    $mime = mime_content_type($tmp_name);
                }

                if (!in_array($ext, $allowed_ext)) {
                    $error = "Formati i fotos nuk lejohet! Lejohen vetëm JPG, JPEG, PNG, GIF, WEBP, HEIC dhe HEIF.";
                } elseif ($mime !== '' && !in_array($mime, $allowed_mime)) {
                    $error = "Tipi i fotos nuk lejohet! Tipi i zbuluar: " . htmlspecialchars($mime);
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Fotoja është shumë e madhe! Madhësia maksimale është 5MB.";
                } else {
                    $target_dir = "uploads/";

                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    if (!is_writable($target_dir)) {
                        $error = "Folderi uploads nuk ka leje shkrimi në server.";
                    } else {
                        $new_name = 'user_' . $user_id . '_' . time() . '.' . $ext;
                        $target_file = $target_dir . $new_name;

                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $foto_path = $target_file;
                        } else {
                            $error = "Dështoi ngarkimi i fotos. Kontrollo lejet e folderit uploads në hosting.";
                        }
                    }
                }
            }
        }

        if (!isset($error)) {
            $stmt2 = $conn->prepare("UPDATE perdorues SET emri=?, mbiemri=?, email=?, foto_profil=? WHERE id=?");
            $stmt2->bind_param("ssssi", $emri, $mbiemri, $email, $foto_path, $user_id);
            $stmt2->execute();

            $_SESSION['emri'] = $emri;
            $success = "Të dhënat u përditësuan me sukses!";

            $user['emri'] = $emri;
            $user['mbiemri'] = $mbiemri;
            $user['email'] = $email;
            $user['foto_profil'] = $foto_path;
        }
    }
}

$foto = !empty($user['foto_profil']) ? $user['foto_profil'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Profili Juaj</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if ($user['roli'] === 'femije'): ?>
        <link rel="stylesheet" href="assets/child.css">
    <?php else: ?>
        <link rel="stylesheet" href="assets/parent.css">
    <?php endif; ?>
</head>

<body class="<?= $user['roli'] === 'femije' ? 'child-ui' : 'parent-ui' ?>">

<div class="profile-center-page">
    <div class="profile-box">
        <h2>Profili juaj</h2>

        <?php if (isset($error)): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success-msg"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <img 
    class="profile-photo"
    src="<?= htmlspecialchars($foto) ?>"
    alt="Foto profili"
    onerror="this.onerror=null; this.src='uploads/default.png';"
>

        <form method="post" enctype="multipart/form-data">
            <div class="form-row">
                <label>Emri</label>
                <input type="text" name="emri" value="<?= htmlspecialchars($user['emri']) ?>" required>
            </div>

            <div class="form-row">
                <label>Mbiemri</label>
                <input type="text" name="mbiemri" value="<?= htmlspecialchars($user['mbiemri']) ?>" required>
            </div>

            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-row">
                <?php if ($user['roli'] === 'prind'): ?>
                    <label>ID Prindi</label>
                    <input type="text" value="<?= (int)$user['id'] ?>" readonly>
                <?php else: ?>
                    <label>ID Prindi</label>
                    <input type="text" value="<?= (int)$user['prind_id'] ?>" readonly>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <label>Foto profili</label>
                <input 
                    type="file" 
                    name="foto" 
                    accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif,image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif"
                >
            </div>

            <button class="btn" type="submit">Ruaj ndryshimet</button>
        </form>

        <div class="bottom-actions">
            <a class="btn btn-light" href="<?= $user['roli'] === 'femije' ? 'femija/f_dashboard.php' : 'p_dashboard.php' ?>">Shko në menu</a>
            <a class="btn btn-danger" href="logout.php">Dil</a>
        </div>
    </div>
</div>

</body>
</html>