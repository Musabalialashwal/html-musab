<?php
// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

// إنشاء اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// تعريف المتغيرات وتعيين القيم الافتراضية
$name = $email = $password = $password2 = "";
$name_err = $email_err = $password_err = $password2_err = "";

// معالجة البيانات الواردة من النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // التحقق من اسم المستخدم
  if (empty($_POST["name"])) {
    $name_err = "اسم المستخدم مطلوب";
  } else {
    $name = test_input($_POST["name"]);
    // التحقق من صحة اسم المستخدم
    if (!preg_match("/^[a-zA-Z0-9]{6,12}$/",$name)) {
      $name_err = "اسم المستخدم يجب أن يتكون من 6 إلى 12 حرفًا أو رقمًا فقط";
    } else {
      // التحقق من تكرار اسم المستخدم
      $sql = "SELECT id FROM users WHERE name = '$name'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        $name_err = "اسم المستخدم موجود بالفعل";
      }
    }
  }

  // التحقق من البريد الإلكتروني
  if (empty($_POST["email"])) {
    $email_err = "البريد الإلكتروني مطلوب";
  } else {
    $email = test_input($_POST["email"]);
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email_err = "صيغة البريد الإلكتروني غير صالحة";
    } else {
      // التحقق من تكرار البريد الإلكتروني
      $sql = "SELECT id FROM users WHERE email = '$email'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        $email_err = "البريد الإلكتروني موجود بالفعل";
      }
    }
  }

  // التحقق من كلمة المرور
  if (empty($_POST["password"])) {
    $password_err = "كلمة المرور مطلوبة";
  } else {
    $password = test_input($_POST["password"]);
    // التحقق من صحة كلمة المرور
    if (!preg_match("/^[a-zA-Z0-9]{6,12}$/",$password)) {
      $password_err = "كلمة المرور يجب أن تتكون من 6 إلى 12 حرفًا أو رقمًا فقط";
    }
  }

  // التحقق من تأكيد كلمة المرور
  if (empty($_POST["password2"])) {
    $password2_err = "تأكيد كلمة المرور مطلوب";
  } else {
    $password2 = test_input($_POST["password2"]);
    // التحقق من مطابقة كلمتي المرور
    if ($password != $password2) {
      $password2_err = "كلمتا المرور غير متطابقتين";
    }
  }

  // إذا لم يكن هناك أخطاء ، فأدخل المستخدم الجديد في قاعدة البيانات
  if ($name_err == "" && $email_err == "" && $password_err == "" && $password2_err == "") {
    // تشفير كلمة المرور
    $password = md5($password);
    // إنشاء جملة SQL
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
    // تنفيذ جملة SQL
    if ($conn->query($sql) === TRUE) {
      // تحويل المستخدم إلى صفحة تسجيل الدخول
      header("Location: login.php");
    } else {
      // عرض رسالة خطأ
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
  }
}

// تنظيف البيانات
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// إغلاق الاتصال
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>صفحة التسجيل</title>
  <style>
    /* تنسيق النموذج */
    form {
      width: 400px;
      margin: 50px auto;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    form h1 {
      padding: 20px;
      text-align: center;
      color: #333;
      border-bottom: 1px solid #e6e6e6;
    }

    form div {
      padding: 10px;
    }

    form label {
      display: block;
      font-weight: bold;
    }

    form input {
      display: block;
      width: 100%;
      height: 40px;
      margin: 10px 0;
      border: 1px solid #e6e6e6;
      border-radius: 5px;
      outline: none;
      font-size: 16px;
      padding: 0 10px;
    }

    form button {
      display: block;
      width: 100%;
      height: 40px;
      margin: 10px 0;
      background-color: #333;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }

    form button:hover {
      background-color: #444;
    }

    form p {
      text-align: center;
      color: #999;
    }

    form a {
      color: #333;
      text-decoration: none;
    }

    form a:hover {
      text-decoration: underline;
    }

    /* تنسيق رسائل الخطأ */
    .error {
      color: red;
      font-size: 14px;
    }

    .input-error {
      border-color: red;
    }
  </style>
</head>
<body>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <h1>التسجيل</h1>
    <div>
      <label for="name">اسم المستخدم:</label>
      <input type="text" name="name" id="name" value="<?php echo $name;?>">
      <span class="error"><?php echo $name_err;?></span>
    </div>
    <div>
      <label for="email">البريد الإلكتروني:</label>
      <input type="email" name="email" id="email" value="<?php echo $email;?>">
      <span class="error"><?php echo $email_err;?></span>
    </div>
    <div>
      <label for="password">كلمة المرور:</label>
      <input type="password" name="password" id="password" value="<?php echo $password;?>">
