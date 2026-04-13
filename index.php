<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$oldData = $_SESSION['old_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_data']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета программиста</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .form-content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .required::after { content: " *"; color: red; }
        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }
        .radio-group label {
            display: inline-flex;
            align-items: center;
            font-weight: normal;
            margin-bottom: 0;
        }
        .radio-group input[type="radio"] {
            margin-right: 5px;
            width: auto;
        }
        select[multiple] {
            height: 150px;
        }
        .checkbox-group {
            margin: 20px 0;
        }
        .checkbox-group label {
            display: inline-flex;
            align-items: center;
            font-weight: normal;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: auto;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        button:hover { transform: translateY(-2px); }
        .error-message {
            background: #fee;
            border-left: 4px solid #f44336;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #c62828;
        }
        .success-message {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #2e7d32;
        }
        .hint { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Анкета программиста</h1>
            <p>Пожалуйста, заполните все поля формы</p>
        </div>
        <div class="form-content">
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">✅ Данные успешно сохранены!</div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <strong>Исправьте ошибки:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="full_name" class="required">ФИО</label>
                    <input type="text" id="full_name" name="full_name" required 
                           maxlength="150" value="<?php echo htmlspecialchars($oldData['full_name'] ?? ''); ?>">
                    <div class="hint">Только буквы и пробелы</div>
                </div>

                <div class="form-group">
                    <label for="phone" class="required">Телефон</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo htmlspecialchars($oldData['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email" class="required">E-mail</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($oldData['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="birth_date" class="required">Дата рождения</label>
                    <input type="date" id="birth_date" name="birth_date" required 
                           value="<?php echo htmlspecialchars($oldData['birth_date'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="required">Пол</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="male" <?php echo ($oldData['gender'] ?? '') == 'male' ? 'checked' : ''; ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" <?php echo ($oldData['gender'] ?? '') == 'female' ? 'checked' : ''; ?>> Женский</label>
                        <label><input type="radio" name="gender" value="other" <?php echo ($oldData['gender'] ?? '') == 'other' ? 'checked' : ''; ?>> Другой</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="languages" class="required">Любимый язык программирования</label>
                    <select name="languages[]" id="languages" multiple required>
                        <?php
                        // Получаем языки из БД
                        require_once 'config.php';
                        $stmt = $pdo->query("SELECT id, name FROM programming_language ORDER BY name");
                        $languages = $stmt->fetchAll();
                        $selectedLanguages = $oldData['languages'] ?? [];
                        foreach ($languages as $lang): ?>
                            <option value="<?php echo $lang['id']; ?>" 
                                <?php echo in_array($lang['id'], $selectedLanguages) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lang['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint">Удерживайте Ctrl для выбора нескольких</div>
                </div>

                <div class="form-group">
                    <label for="biography">Биография</label>
                    <textarea id="biography" name="biography" rows="5"><?php echo htmlspecialchars($oldData['biography'] ?? ''); ?></textarea>
                </div>

                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="contract" value="1" <?php echo isset($oldData['contract']) ? 'checked' : ''; ?> required>
                        Я ознакомлен с условиями контракта
                    </label>
                </div>

                <button type="submit">💾 Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>