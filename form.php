<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');


function validateFullName($name) {
    $name = trim($name);
    if (empty($name)) {
        return "ФИО обязательно для заполнения";
    }
    if (strlen($name) > 150) {
        return "ФИО не должно превышать 150 символов";
    }
    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name)) {
        return "ФИО может содержать только буквы, пробелы и дефисы";
    }
    return true;
}


function validatePhone($phone) {
    $phone = trim($phone);
    if (empty($phone)) {
        return "Телефон обязателен для заполнения";
    }
    $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
    if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
        return "Неверный формат телефона";
    }
    return true;
}


function validateEmail($email) {
    $email = trim($email);
    if (empty($email)) {
        return "Email обязателен для заполнения";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Неверный формат email";
    }
    if (strlen($email) > 100) {
        return "Email не должен превышать 100 символов";
    }
    return true;
}


function validateBirthDate($date) {
    if (empty($date)) {
        return "Дата рождения обязательна для заполнения";
    }
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return "Неверный формат даты";
    }
    $age = date('Y') - date('Y', $timestamp);
    if ($age < 16 || $age > 120) {
        return "Возраст должен быть от 16 до 120 лет";
    }
    return true;
}


function validateGender($gender) {
    $allowed = ['male', 'female', 'other'];
    if (empty($gender) || !in_array($gender, $allowed)) {
        return "Выберите корректный пол";
    }
    return true;
}

function validateLanguages($languages, $db) {
    if (empty($languages)) {
        return "Выберите хотя бы один язык программирования";
    }
    
    $placeholders = implode(',', array_fill(0, count($languages), '?'));
    $stmt = $db->prepare("SELECT id FROM programming_languages WHERE id IN ($placeholders)");
    $stmt->execute($languages);
    $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($existingIds) != count($languages)) {
        return "Один или несколько выбранных языков не существуют";
    }
    
    return true;
}


function validateContract($agreed) {
    if ($agreed != '1') {
        return "Вы должны ознакомиться с контрактом";
    }
    return true;
}


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}


$_SESSION['old'] = $_POST;


$errors = [];


$fullNameValid = validateFullName($_POST['full_name'] ?? '');
if ($fullNameValid !== true) $errors[] = $fullNameValid;

$phoneValid = validatePhone($_POST['phone'] ?? '');
if ($phoneValid !== true) $errors[] = $phoneValid;

$emailValid = validateEmail($_POST['email'] ?? '');
if ($emailValid !== true) $errors[] = $emailValid;

$birthDateValid = validateBirthDate($_POST['birth_date'] ?? '');
if ($birthDateValid !== true) $errors[] = $birthDateValid;

$genderValid = validateGender($_POST['gender'] ?? '');
if ($genderValid !== true) $errors[] = $genderValid;

$contractValid = validateContract($_POST['agreed_to_contract'] ?? '');
if ($contractValid !== true) $errors[] = $contractValid;


if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: index.php');
    exit();
}


try {
    $user = 'ваш_логин'; 
    $pass = 'ваш_пароль'; 
    $db = new PDO('mysql:host=localhost;dbname=ваша_бд', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $db->exec("SET NAMES utf8");
    
  
    $db->beginTransaction();
    
    
    $stmt = $db->prepare("
        INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, agreed_to_contract) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $biography = trim($_POST['biography'] ?? '');
    $agreed = isset($_POST['agreed_to_contract']) ? 1 : 0;
    
    $stmt->execute([
        trim($_POST['full_name']),
        trim($_POST['phone']),
        trim($_POST['email']),
        $_POST['birth_date'],
        $_POST['gender'],
        $biography,
        $agreed
    ]);
    
    
    $applicationId = $db->lastInsertId();
    
    
    $languages = $_POST['languages'] ?? [];
    $langStmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    
    foreach ($languages as $langId) {
        $langStmt->execute([$applicationId, $langId]);
    }
    
  
    $db->commit();
    
    
    unset($_SESSION['old']);
    $_SESSION['success_message'] = "Данные успешно сохранены! ID записи: " . $applicationId;
    
   
    header('Location: index.php');
    exit();
    
} catch (PDOException $e) {
   
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['errors'] = ["Ошибка базы данных: " . $e->getMessage()];
    header('Location: index.php');
    exit();
}
?>
