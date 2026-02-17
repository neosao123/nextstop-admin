<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | {{ config('app.name') }}</title>
</head>

<body>
    <p>Hello,</p>
    <p>We have received a request to reset password. If you did not make the request just ignore this email. Otherwise, you can reset your password using this link.</p>
    <p><a href="{{ $details['link'] }}" target="_blank" style="margin-bottom:8px;background:green; width:50%; padding: 8px 12px; border: 1px solid green;border-radius: 2px;font-family: Helvetica, Arial, sans-serif;font-size: 14px; color: #ffffff;text-decoration: none;font-weight:bold;display: inline-block; text-align:center;">RESET PASSWORD</a></p>
</body>

</html>