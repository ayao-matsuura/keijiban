<?php
session_start();
session_destroy(); //☜これで全てのsessionを削除できる。つまりログアウト。
header("Location:login.php");
?>