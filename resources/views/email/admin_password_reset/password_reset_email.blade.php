<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot Password</title>
    <style>
        .font{
            font-size: 16px;
        }
    </style>
</head>
<body class="font">

Dear {{$data['name']}},
<br><br>
<p class="font">You've successfully updated your password for {{ $data['email'] }} Account</p>
<br/>
This email has been sent automatically. Please do not reply!
<br/><br/>

</body>
</html>
