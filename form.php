<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 3 - Анкета</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .form-body {
            padding: 35px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .error-field {
            border-color: #e74c3c !important;
            background-color: #fff5f5 !important;
        }

        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            padding: 8px 0;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        select[multiple] {
            min-height: 130px;
        }

        select[multiple] option {
            padding: 8px;
            cursor: pointer;
        }

        select[multiple] option:hover {
            background-color: #667eea20;
        }

        .checkbox-label {
            display: flex !important;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: normal !important;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }

        .error-summary {
            background: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #e74c3c;
        }

        .error-summary ul {
            margin-left: 20px;
            margin-top: 10px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        button:active {
            transform: translateY(0);
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Анкета участника</h1>
            <p>Пожалуйста, заполните все обязательные поля</p>
        </div>

        <div class="form-body">
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-summary">
                    <strong>❌ Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul>
                        <?php foreach ($errors as $field => $error): ?>
                            <?php if ($field !== 'database'): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Поле 1: ФИО -->
                <div class="form-group">
                    <label>ФИО <span class="required">*</span></label>
                    <input type="text" 
                           name="full_name" 
                           value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>"
                           class="<?= isset($errors['full_name']) ? 'error-field' : '' ?>"
                           placeholder="Иванов Иван Иванович">
                    <div class="help-text">Только буквы, пробелы и дефисы. Не более 150 символов.</div>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="error-message"><?= $errors['full_name'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 2: Телефон -->
                <div class="form-group">
                    <label>Телефон <span class="required">*</span></label>
                    <input type="tel" 
                           name="phone" 
                           value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>"
                           class="<?= isset($errors['phone']) ? 'error-field' : '' ?>"
                           placeholder="+7 (123) 456-78-90">
                    <div class="help-text">10-20 цифр, допустимы знаки: +, -, (, ), пробелы</div>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message"><?= $errors['phone'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 3: Email -->
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" 
                           name="email" 
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                           class="<?= isset($errors['email']) ? 'error-field' : '' ?>"
                           placeholder="example@mail.ru">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 4: Дата рождения -->
                <div class="form-group">
                    <label>Дата рождения <span class="required">*</span></label>
                    <input type="date" 
                           name="birth_date" 
                           value="<?= htmlspecialchars($form_data['birth_date'] ?? '') ?>"
                           class="<?= isset($errors['birth_date']) ? 'error-field' : '' ?>">
                    <div class="help-text">Формат: ГГГГ-ММ-ДД</div>
                    <?php if (isset($errors['birth_date'])): ?>
                        <div class="error-message"><?= $errors['birth_date'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 5: Пол -->
                <div class="form-group">
                    <label>Пол <span class="required">*</span></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male" 
                                   <?= (($form_data['gender'] ?? '') == 'male') ? 'checked' : '' ?>>
                            👨 Мужской
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" 
                                   <?= (($form_data['gender'] ?? '') == 'female') ? 'checked' : '' ?>>
                            👩 Женский
                        </label>
                    </div>
                    <?php if (isset($errors['gender'])): ?>
                        <div class="error-message"><?= $errors['gender'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 6: Любимые языки программирования (множественный выбор) -->
                <div class="form-group">
                    <label>Любимые языки программирования <span class="required">*</span></label>
                    <select name="languages[]" multiple="multiple" 
                            class="<?= isset($errors['languages']) ? 'error-field' : '' ?>">
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang['name']) ?>"
                                <?php if (isset($form_data['languages']) && in_array($lang['name'], $form_data['languages'])): ?>
                                    selected
                                <?php endif; ?>>
                                <?= htmlspecialchars($lang['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">Для выбора нескольких языков: зажмите Ctrl (Cmd) и кликайте</div>
                    <?php if (isset($errors['languages'])): ?>
                        <div class="error-message"><?= $errors['languages'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 7: Биография -->
                <div class="form-group">
                    <label>Биография</label>
                    <textarea name="biography" 
                              rows="5" 
                              class="<?= isset($errors['biography']) ? 'error-field' : '' ?>"
                              placeholder="Расскажите немного о себе..."><?= htmlspecialchars($form_data['biography'] ?? '') ?></textarea>
                    <div class="help-text">Необязательное поле. Максимум 5000 символов.</div>
                    <?php if (isset($errors['biography'])): ?>
                        <div class="error-message"><?= $errors['biography'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Поле 8: Чекбокс -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="contract_accepted" value="1"
                               <?= (($form_data['contract_accepted'] ?? 0) == 1) ? 'checked' : '' ?>>
                        Я ознакомлен(а) с условиями контракта <span class="required">*</span>
                    </label>
                    <?php if (isset($errors['contract_accepted'])): ?>
                        <div class="error-message"><?= $errors['contract_accepted'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Кнопка отправки -->
                <button type="submit">💾 Сохранить анкету</button>
            </form>
        </div>
    </div>
</body>
</html>