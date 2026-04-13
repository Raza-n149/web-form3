<?php
require_once 'config.php';
session_start();

$errors = [];

// Валидация ФИО
if (empty($_POST['full_name'])) {
    $errors[] = "ФИО обязательно для заполнения";
} elseif (strlen($_POST['full_name']) > 150) {
    $errors[] = "ФИО не должно превышать 150 символов";
} elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $_POST['full_name'])) {
    $errors[] = "ФИО должно содержать только буквы и пробелы";
}

// Валидация телефона
if (empty($_POST['phone'])) {
    $errors[] = "Телефон обязателен для заполнения";
}

// Валидация email
if (empty($_POST['email'])) {
    $errors[] = "E-mail обязателен для заполнения";
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Неверный формат email";
} else {
    // Проверка уникальности email
    $stmt = $pdo->prepare("SELECT id FROM application WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        $errors[] = "Пользователь с таким email уже существует";
    }
}

// Валидация даты
if (empty($_POST['birth_date'])) {
    $errors[] = "Дата рождения обязательна";
} else {
    $age = date_diff(date_create($_POST['birth_date']), date_create('today'))->y;
    if ($age < 16) {
        $errors[] = "Вам должно быть не менее 16 лет";
    }
}

// Валидация пола
$allowedGenders = ['male', 'female', 'other'];
if (!in_array($_POST['gender'], $allowedGenders)) {
    $errors[] = "Выберите корректный пол";
}

// Валидация языков
if (empty($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования";
}

// Валидация контракта
if (!isset($_POST['contract'])) {
    $errors[] = "Необходимо подтвердить ознакомление с контрактом";
}

// Если есть ошибки
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_data'] = $_POST;
    header('Location: index.php');
    exit;
}

// Сохранение в БД
try {
    $pdo->beginTransaction();
    
    // Вставка в application
    $sql = "INSERT INTO application (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'] ?? '',
        isset($_POST['contract']) ? 1 : 0
    ]);
    
    $applicationId = $pdo->lastInsertId();
    
    // Вставка языков
    $sqlLang = "INSERT INTO application_language (application_id, language_id) VALUES (?, ?)";
    $stmtLang = $pdo->prepare($sqlLang);
    
    foreach ($_POST['languages'] as $languageId) {
        $stmtLang->execute([$applicationId, $languageId]);
    }
    
    $pdo->commit();
    
    header('Location: index.php?success=1');
    exit;
    
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['errors'] = ["Ошибка базы данных: " . $e->getMessage()];
    $_SESSION['old_data'] = $_POST;
    header('Location: index.php');
    exit;
}
?>