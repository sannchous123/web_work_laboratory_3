<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


$db_host = 'localhost';
$db_name = 'u82184';        
$db_user = 'u82184';        
$db_pass = '6010664'; 


$languages = [];
$error_message = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    $tables_exist = $pdo->query("SHOW TABLES LIKE 'programming_languages'")->rowCount() > 0;
    
    if (!$tables_exist) {
       
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS programming_languages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                language_name VARCHAR(50) NOT NULL UNIQUE
            );
            
            INSERT INTO programming_languages (language_name) VALUES 
            ('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), 
            ('Python'), ('Java'), ('Haskell'), ('Clojure'), 
            ('Prolog'), ('Scala'), ('Go');
            
            CREATE TABLE IF NOT EXISTS applications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(150) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(100) NOT NULL,
                birth_date DATE NOT NULL,
                gender ENUM('male', 'female', 'other') NOT NULL,
                biography TEXT,
                agreed_to_contract TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS application_languages (
                application_id INT UNSIGNED NOT NULL,
                language_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (application_id, language_id),
                FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
                FOREIGN KEY (language_id) REFERENCES programming_languages(id) ON DELETE CASCADE
            );
        ");
    }
    

    $stmt = $pdo->query("SELECT id, language_name FROM programming_languages ORDER BY language_name");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error_message = "Ошибка подключения к БД: " . $e->getMessage();
    
    $languages = [
        ['id' => 1, 'language_name' => 'Pascal'],
        ['id' => 2, 'language_name' => 'C'],
        ['id' => 3, 'language_name' => 'C++'],
        ['id' => 4, 'language_name' => 'JavaScript'],
        ['id' => 5, 'language_name' => 'PHP'],
        ['id' => 6, 'language_name' => 'Python'],
        ['id' => 7, 'language_name' => 'Java'],
        ['id' => 8, 'language_name' => 'Haskell'],
        ['id' => 9, 'language_name' => 'Clojure'],
        ['id' => 10, 'language_name' => 'Prolog'],
        ['id' => 11, 'language_name' => 'Scala'],
        ['id' => 12, 'language_name' => 'Go'],
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Анкета разработчика</title>
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
        .form-content { padding: 40px; }
        .form-group { margin-bottom: 25px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .required:after { content: " *"; color: red; }
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
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
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
        .radio-option input[type="radio"] { width: auto; margin: 0; }
        select[multiple] { height: 150px; }
        textarea { resize: vertical; min-height: 100px; }
        .contract-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .contract-checkbox input { width: auto; margin: 0; }
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
        }
        .btn-submit:hover { opacity: 0.9; }
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
        @media (max-width: 600px) {
            .form-content { padding: 20px; }
            .radio-group { flex-direction: column; gap: 10px; }
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
                    <strong>❌ Ошибки:</strong>
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    ⚠️ <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form action="process.php" method="POST">
                <div class="form-group">
                    <label for="full_name" class="required">ФИО</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($_SESSION['old']['full_name'] ?? '') ?>"
                           placeholder="Иванов Иван Иванович" required>
                    <small style="color: #666; display: block; margin-top: 5px;">Только буквы, пробелы и дефисы. Максимум 150 символов.</small>
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
                        <label class="radio-option">
                            <input type="radio" name="gender" value="male" 
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'male') ? 'checked' : '' ?>> Мужской
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="gender" value="female"
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'female') ? 'checked' : '' ?>> Женский
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="gender" value="other"
                                   <?= (($_SESSION['old']['gender'] ?? '') == 'other') ? 'checked' : '' ?>> Другой
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required">Любимые языки программирования</label>
                    <select name="languages[]" multiple size="6" required>
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?= $lang['id'] ?>" 
                                <?= (isset($_SESSION['old']['languages']) && in_array($lang['id'], $_SESSION['old']['languages'])) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang['language_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: #666; display: block; margin-top: 5px;">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких языков</small>
                </div>
                
                <div class="form-group">
                    <label for="biography">Биография</label>
                    <textarea id="biography" name="biography" rows="4" 
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
