<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
                <center>
                            <form action="/admin/do_create_mobile" method="post">
                            <!-- @csrf -->
                                由于中国工信部的告知，从即日起，我们需要对您的账号进行手机号绑定，谢谢配合，如果有任何疑问，请访问<a href="http://www.miit.gov.cn/">中国工信部官网</a><br><br><br>
                                请输入您的手机号：<input type="tel" name="mobile"><br><br>
                                <input type="submit" value="确认提交">
                            </form>
                </center> 
</body>
</html>