<?php
session_start();

// Настройки подключения к БД (ЗАМЕНИТЕ НА СВОИ!)
$db_host = 'localhost';
$db_name = 'u82184';  // замените на имя вашей БД
$db_user = 'u82184';  // замените на вашего пользователя
$db_pass = '6010664';      // замените на ваш пароль

// Подключаемся к БД для получения списка языков
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, language_name FROM programming_languages ORDER BY language_name");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Ошибка БД: " . $e->getMessage();
    $languages = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика</title>
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
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .form-content {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .required:after {
            content: " *";
            color: red;
        }
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .radio-option input[type="radio"] {
            width: auto;
            margin: 0;
        }
        select[multiple] {
            height: 150px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .contract-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .contract-checkbox input {
            width: auto;
            margin: 0;
        }
        .contract-checkbox label {
            margin: 0;
            cursor: pointer;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .error-list {
            margin-top: 10px;
            padding-left: 20px;
        }
        @media (max-width: 600px) {
            .form-content {
                padding: 20px;
            }
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Анкета разработчика</h1>
            <p>Пожалуйста, заполните все поля формы</p>
        </div>
        
        <div class="form-content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="alert alert-error">
                    <strong>❌ Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul class="error-list">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Ошибка базы данных:</strong><br>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="full_name" class="required">ФИО</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($_SESSION['old']['full_name'] ?? '') ?>"
                           placeholder="Иванов Иван Иванович" required>
                    <small style="color: #666;">Только буквы, пробелы и дефисы. Максимум 150 символов.</small>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="required">Телефон</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($_SESSION['old']['phone'] ?? '') ?>"
                           placeholder="+7 (123) 456-78-90" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">E-mail</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>"
                           placeholder="example@mail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="birth_date" class="required">Дата рождения</label>
                    <input type="date" id="birth_date" name="birth_date" 
                           value="<?= htmlspecialchars($_SESSION['old']['birth_date'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="required">Пол</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="male" name="gender" value="male" 
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'male') ? 'checked' : '' ?>>
                            <label for="male">Мужской</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="female" name="gender" value="female"
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'female') ? 'checked' : '' ?>>
                            <label for="female">Женский</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="other" name="gender" value="other"
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'other') ? 'checked' : '' ?>>
                            <label for="other">Другой</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">Любимые языки программирования</label>
                    <select name="languages[]" multiple="multiple" size="6" required>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?= $lang['id'] ?>" 
                                <?= (isset($_SESSION['old']['languages']) && in_array($lang['id'], $_SESSION['old']['languages'])) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang['language_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666;">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких языков</small>
                </div>
                
                <div class="form-group">
                    <label for="biography">Биография</label>
                    <textarea id="biography" name="biography" 
                              placeholder="Расскажите немного о себе..."><?= htmlspecialchars($_SESSION['old']['biography'] ?? '') ?></textarea>
                </div>
                
                <div class="contract-checkbox">
                    <input type="checkbox" id="contract" name="agreed_to_contract" value="1" required
                           <?= (($_SESSION['old']['agreed_to_contract'] ?? '') == '1') ? 'checked' : '' ?>>
                    <label for="contract">Я ознакомлен(а) с условиями контракта</label>
                </div>
                
                <button type="submit" class="btn-submit">💾 Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php unset($_SESSION['old']); ?>
