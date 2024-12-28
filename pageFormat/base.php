<?php

// For General Functions Here
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

//$_SESSION['user_id']=2;
//$_SESSION['admin'] = 1;

// Define the login page URL
$loginPage = '../user/login.php'; // Adjust the path as needed

// Get the current URL path
$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check if the user is not logged in and not on the login page
if (empty($_SESSION['user_id']) && ($currentUrl !== '/user/login.php' && $currentUrl !== '/user/signup.php' && $currentUrl !== '/user/forgotPassword.php' && $currentUrl !== '/user/changePassword.php' && $currentUrl !== '/user/verifyOtp.php')) {
    // Redirect to the login page
        header("Location: $loginPage");
    exit();
}

function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// GET and POST parameter (REQUEST)
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirecting URL
function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// set/get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

function is_email($value){
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// HTML Helpers

// helpers.php
function createNavItem($href, $label)
{
    // Determine if the current page matches the link to add an 'active' class
    $currentPage = basename($_SERVER['PHP_SELF']); // Get the current file name
    $activeClass = ($currentPage == basename($href)) ? 'active' : '';

    return "<li><a href='$href' class='header__a $activeClass'>$label</a></li>";
}

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// Security
$_user = $_SESSION['user'] ?? null;

// User login
function login($user, $url = '/')
{
    session_start();
    $_SESSION['user'] = $user;
    redirect($url);
}

// User logout
function logout($url = '/')
{
    session_start();
    unset($_SESSION['user']);
    session_destroy();
    redirect($url);
}

// Authorization
function auth(...$roles)
{
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return;
            }
        } else {
            return;
        }
    }
    redirect('../loginSide/login.php');
}

// email function
function get_mail() {
    require_once '../lib/PHPMailer.php';
    require_once '../lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'tequilaguey777@gmail.com';
    $m->Password = 'dbkh ijmy mjda ohkj';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, 'BoundlessBooks');

    return $m;
}

// Database setup

// Global PDO Object
$_db = new PDO('mysql:dbname=ebookdb', 'root', '', [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,]);

// Is unique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}
