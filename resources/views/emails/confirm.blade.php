<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>注册确认链接</title>
</head>
<body>
<h1>感谢您在webpo App 网站上进行注册</h1>
<p>
    <a href="{{ route('confirm_email', $user->activation_token) }}">
        {{ route('confirm_email', $user->activation_token) }}
    </a>
</p>
<p>
    如果这不是本人的操作，请忽略此邮件
</p>
</body>
</html>
