<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}


$_SESSION['old'] = $_POST;


$full_name = trim($_POST['full_name'] ?? '');
if (empty($full_name)) {
    $_SESSION['errors'][] = "ФИО обязательно для заполнения";
} elseif (strlen($full_name) > 150) {
    $_SESSION['errors'][] = "ФИО не должно превышать 150 символов";
} elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $full_name)) {
    $_SESSION['errors'][] = "ФИО может содержать только буквы, пробелы и дефисы";
}

$phone = trim($_POST['phone'] ?? '');
if (empty($phone)) {
    $_SESSION['errors'][] = "Телефон обязателен для заполнения";
} else {
    $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
    if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
        $_SESSION['errors'][] = "Неверный формат телефона";
    }
}


$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    $_SESSION['errors'][] = "Email обязателен для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['errors'][] = "Неверный формат email";
} elseif (strlen($email) > 100) {
    $_SESSION['errors'][] = "Email не должен превышать 100 символов";
}

$birth_date = $_POST['birth_date'] ?? '';
if (empty($birth_date)) {
    $_SESSION['errors'][] = "Дата рождения обязательна для заполнения";
} else {
    $timestamp = strtotime($birth_date);
    if (!$timestamp) {
        $_SESSION['errors'][] = "Неверный формат даты";
    } else {
        $age = date('Y') - date('Y', $timestamp);
        if ($age < 16 || $age > 120) {
            $_SESSION['errors'][] = "Возраст должен быть от 16 до 120 лет";
        }
    }
}

$gender = $_POST['gender'] ?? '';
$allowed_genders = ['male', 'female', 'other'];
if (empty($gender) || !in_array($gender, $allowed_genders)) {
    $_SESSION['errors'][] = "Выберите корректный пол";
}

$agreed = $_POST['agreed_to_contract'] ?? '';
if ($agreed != '1') {
    $_SESSION['errors'][] = "Вы должны ознакомиться с контрактом";
}


if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
    header('Location: index.php');
    exit();
}


try {
    $db_host = 'localhost';
    $db_name = 'test';  
    $db_user = 'root'; 
    $db_pass = '';     
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
   
    $pdo->beginTransaction();
   
    $stmt = $pdo->prepare("
        INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, agreed_to_contract) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $biography = trim($_POST['biography'] ?? '');
    $agreed_value = isset($_POST['agreed_to_contract']) ? 1 : 0;
    
    $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $biography, $agreed_value]);
    $application_id = $pdo->lastInsertId();
    
    if (isset($_POST['languages']) && is_array($_POST['languages'])) {
        $lang_stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang_id) {
            $lang_stmt->execute([$application_id, $lang_id]);
        }
    }
    
    
    $pdo->commit();
    
  
    unset($_SESSION['old']);
    $_SESSION['success_message'] = "Данные успешно сохранены! ID записи: " . $application_id;
    
} catch (PDOException $e) {
  
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['errors'][] = "Ошибка базы данных: " . $e->getMessage();
}

header('Location: index.php');
exit();
?>
