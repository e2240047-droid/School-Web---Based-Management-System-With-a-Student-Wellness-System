<?php
session_start();

function require_login() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

function require_role($roles = []) {
    require_login();
    if (!in_array($_SESSION["role"], $roles)) {
        die("Access denied.");
    }
}

function require_admin() {
    require_role(["admin"]);
}