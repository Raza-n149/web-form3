<?php
// ============================================
// ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
// ============================================
$db_host = 'localhost';
$db_user = 'u82260';
$db_pass = '3052562';
$db_name = 'u82260';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// ============================================
// ПОЛУЧАЕМ СПИСОК ЯЗЫКОВ ИЗ БД
// ============================================
$stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// ПЕРЕМЕННЫЕ ДЛЯ ФОРМЫ
// ============================================
$errors = [];           // Массив ошибок
$form_data = [];        // Данные формы
$success_message = '';  // Сообщение об успехе

// ============================================
// ОБРАБОТКА POST-ЗАПРОСА
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ---------- 1. Валидация ФИО ----------
    $full_name = trim($_POST['full_name'] ?? '');
    if (empty($full_name)) {
        $errors['full_name'] = 'ФИО обязательно для заполнения';
    } elseif (mb_strlen($full_name) > 150) {
        $errors['full_name'] = 'ФИО не должно превышать 150 символов';
    } elseif (!preg_match('/^[а-яА-Яa-zA-Z\s\-]+$/u', $full_name)) {
        $errors['full_name'] = 'ФИО может содержать только буквы, пробелы и дефисы';
    } else {
        $form_data['full_name'] = $full_name;
    }
    
    // ---------- 2. Валидация телефона ----------
    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^[\d\+\-\(\)\s]{10,20}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать 10-20 цифр и знаки +, -, (, ), пробелы';
    } else {
        $form_data['phone'] = $phone;
    }
    
    // ---------- 3. Валидация email ----------
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    } else {
        $form_data['email'] = $email;
    }
    
    // ---------- 4. Валидация даты рождения ----------
    $birth_date = $_POST['birth_date'] ?? '';
    if (empty($birth_date)) {
        $errors['birth_date'] = 'Дата рождения обязательна';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
        $errors['birth_date'] = 'Неверный формат даты (ГГГГ-ММ-ДД)';
    } else {
        $form_data['birth_date'] = $birth_date;
    }
    
    // ---------- 5. Валидация пола ----------
    $gender = $_POST['gender'] ?? '';
    if (empty($gender)) {
        $errors['gender'] = 'Выберите пол';
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Неверное значение пола';
    } else {
        $form_data['gender'] = $gender;
    }
    
    // ---------- 6. Валидация языков программирования ----------
    $selected_languages = $_POST['languages'] ?? [];
    if (empty($selected_languages)) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    } else {
        $valid_languages = [];
        $allowed_languages = array_column($languages, 'name');
        foreach ($selected_languages as $lang) {
            if (in_array($lang, $allowed_languages)) {
                $valid_languages[] = $lang;
            }
        }
        if (empty($valid_languages)) {
            $errors['languages'] = 'Выбраны недопустимые языки программирования';
        } else {
            $form_data['languages'] = $valid_languages;
        }
    }
    
    // ---------- 7. Валидация биографии (необязательно) ----------
    $biography = trim($_POST['biography'] ?? '');
    if (mb_strlen($biography) > 5000) {
        $errors['biography'] = 'Биография не должна превышать 5000 символов';
    } else {
        $form_data['biography'] = $biography;
    }
    
    // ---------- 8. Валидация чекбокса ----------
    $contract_accepted = isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == '1';
    if (!$contract_accepted) {
        $errors['contract_accepted'] = 'Необходимо подтвердить, что вы ознакомлены с контрактом';
    }
    $form_data['contract_accepted'] = $contract_accepted ? 1 : 0;
    
    // ---------- 9. Сохранение в базу данных ----------
    if (empty($errors)) {
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Вставка в таблицу applications
            $stmt = $pdo->prepare("
                INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
                VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)
            ");
            
            $stmt->execute([
                ':full_name' => $form_data['full_name'],
                ':phone' => $form_data['phone'],
                ':email' => $form_data['email'],
                ':birth_date' => $form_data['birth_date'],
                ':gender' => $form_data['gender'],
                ':biography' => $form_data['biography'],
                ':contract_accepted' => $form_data['contract_accepted']
            ]);
            
            // Получаем ID новой заявки
            $application_id = $pdo->lastInsertId();
            
            // Подготавливаем запрос для вставки языков
            $stmt_lang = $pdo->prepare("
                INSERT INTO application_languages (application_id, language_id) 
                VALUES (:application_id, :language_id)
            ");
            
            // Создаем маппинг названий языков на ID
            $language_map = [];
            foreach ($languages as $lang) {
                $language_map[$lang['name']] = $lang['id'];
            }
            
            // Вставляем каждый выбранный язык
            foreach ($form_data['languages'] as $lang_name) {
                $lang_id = $language_map[$lang_name];
                $stmt_lang->execute([
                    ':application_id' => $application_id,
                    ':language_id' => $lang_id
                ]);
            }
            
            // Фиксируем транзакцию
            $pdo->commit();
            
            // Сообщение об успехе
            $success_message = "✅ Данные успешно сохранены! Номер заявки: " . $application_id;
            
            // Очищаем данные формы после успешной отправки
            $form_data = [];
            
        } catch (PDOException $e) {
            // Откатываем транзакцию при ошибке
            $pdo->rollBack();
            $errors['database'] = "Ошибка при сохранении: " . $e->getMessage();
        }
    }
}

// ============================================
// ПОДКЛЮЧАЕМ HTML ФОРМУ
// ============================================
include 'form.php';
?>