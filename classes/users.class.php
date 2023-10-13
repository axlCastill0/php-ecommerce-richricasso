<?php

class Users extends Dbh
{

    protected function setUser($uid, $email, $pwd)
    {
        $stmt = $this->connect()->prepare('INSERT INTO users (uid, user_email, user_pwd) VALUES (?, ?, ?);');

        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

        if (!$stmt->execute(array($uid, $email, $hashedPwd))) {
            $stmt = null;
            header("location: ../index.php?error=stmtfailed");
            exit();
        }

        $stmt = null;
    }

    protected function getUser($uid, $pwd) {
        $stmt = $this->connect()->prepare('SELECT user_pwd FROM users WHERE uid = ?;');
        
        if(!$stmt->execute(array($uid))) {
            $stmt = null;
            header("location: ../index.php?error=stmtfailed");
            exit();
        }

        if($stmt->rowCount() == 0) {
            $stmt = null;
            header("location: ../index.php?usernotfound");
            exit();
        }

        $pwdHashed = $stmt->fetchAll();
        $checkPwd = password_verify($pwd, $pwdHashed[0]["user_pwd"]);

        if($checkPwd == false) {
            $stmt = null;
            header("location: ../index.php?error=incorrectpwd");
            exit();
        } else if($checkPwd == true) {
            $stmt = $this->connect()->prepare('SELECT * FROM users WHERE uid = ?;');
            if(!$stmt->execute(array($uid))) {
                $stmt = null;
                header("location: ../index.php?error=stmtfailed");
                exit();
            }
            if($stmt->rowCount() == 0) {
                $stmt = null;
                header("location: ../index.php?usernotfound");
                exit();
            }
            $user = $stmt->fetchAll();

            session_start();
            $_SESSION["user_id"] = $user[0]["user_id"];
            $_SESSION["uid"] = $user[0]["uid"];

            $stmt = null;
        }

        $stmt = null;
    }

    protected function checkUser($uid, $email)
    {
        $stmt = $this->connect()->prepare('SELECT uid FROM users WHERE uid = ? OR user_email = ?;');
        if (!$stmt->execute(array($uid, $email))) {
            $stmt = null;
            header("location: ../index.php?error=stmtfailed");
            exit();
        }
        $resultCheck = true;
        if ($stmt->rowCount() > 0) {
            $resultCheck = false;
        }
        return $resultCheck;
    }
}