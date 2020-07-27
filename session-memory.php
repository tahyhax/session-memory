<?php
ini_set('session.gc_maxlifetime', 120960);
ini_set('session.cookie_lifetime', 120960);

date_default_timezone_set("Europe/Kiev");

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (key_exists('form', $_POST)) {
        saveUserInfo($_POST['form']);
    }
    if (key_exists('sessionClear', $_POST)) {
        session_destroy();
        header("Location: " . $_SERVER['REQUEST_URI']);
    }

} else {
    $_SESSION['counter'] = key_exists('counter', $_SESSION) ? $_SESSION['counter'] + 1 : 1;
    $_SESSION['lastConnect'] = date('m/d/Y  H:i:s');
}



/**
 * save new  data  to session
 *
 * @param  array $params
 */
function saveUserInfo($params)
{

    $data = [];
    //todo  validate
    foreach ($params as $key => $value) {
        $data[$key] = trim(stripcslashes($value));
    }

    $yearOfBirth = getYearOfBirth($data['age']);

    $data['yearOfBirth'] = $yearOfBirth;
    $_SESSION['info'] = $data;

}

/**
 * calculate  Year Of Birth
 *
 * @param $age
 * @return false|string
 */
function getYearOfBirth($age)
{
    return date('Y', strtotime(date('Y-m-d') . " - $age years"));
}

/**
 * check if exist user info
 *
 * @param array $data
 * @return bool
 */
function isInfo($data)
{
    return key_exists('info', $data);
}

/**
 *  user info
 * @param array $info
 * @return string
 */
function getUserInfoStr($info)
{
    $template = 'You are %s %s and you were born in %s.';
    return sprintf($template, $info["firstName"], $info["lastName"], $info["yearOfBirth"]);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
  />
  <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
  <title>Document</title>
  <style>
    *,
    *::before,
    *::after {
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
    }

    ul[class],
    ol[class] {
      padding: 0;
    }

    body,
    h1,
    h2,
    h3,
    h4,
    p,
    ul[class],
    ol[class],
    figure,
    blockquote,
    dl,
    dd {
      margin: 0;
    }

    input,
    select {
      outline: none;
    }

    .wrap {
      position: relative;
      height: 100vh;
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-pack: center;
      -ms-flex-pack: center;
      justify-content: center;
      -webkit-box-align: center;
      -ms-flex-align: center;
      align-items: center;
    }

    .wrap__inner {
      -webkit-box-flex: 50%;
      -ms-flex: 50%;
      flex: 0 0 50%;
      max-width: 50%;
    }

    .session__info {
      text-align: center;
      margin-bottom: 15px;
    }

    .session__form {
      margin-bottom: 15px;
      max-width: 66.6666%;
      margin: 0 auto;
    }

    .session-form {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      -webkit-box-orient: vertical;
      -webkit-box-direction: normal;
      -ms-flex-direction: column;
      flex-direction: column;
      padding: 15px;
      font-size: 1rem;
    }

    .session-form__item {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      margin-bottom: 10px;
    }

    .session-form__item label {
      font-size: 0.9em;
      -webkit-box-flex: 0;
      -ms-flex: 0 0 25%;
      flex: 0 0 25%;
    }

    .session-form__item input {
      -webkit-box-flex: 1;
      -ms-flex: 1;
      flex: 1;
    }

    .session-form__button {
      margin-left: auto;
    }

    .form-input,
    .form-select {
      padding: 5px;
    }

    .form-label {
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      font-weight: bold;
    }

    .form-label--center {
      -webkit-box-align: center;
      -ms-flex-align: center;
      align-items: center;
    }

    .form-button {
      padding: 5px 10px;
      font-size: 0.9em;
      font-weight: bold;
      cursor: pointer;
    }

    .connection-count {
      font-size: 1.2rem;
      font-weight: bold;
      color: red;
    }

    .connection-last {
      font-style: italic;
      color: green;
    }

    .session-clear {
      position: absolute;
      bottom: 10%;
      right: 10%;
    }

    .session-clear .session-clear__button {
      cursor: pointer;
      padding: 15px;
      border: none;
      font-size: 1.1rem;
      font-weight: bold;
      background-color: #ffc107;
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="wrap__inner session">
    <div class="session__info session-info">
      <div class="session-info__text">
          <?php
            if (isInfo($_SESSION)) {
                echo getUserInfoStr($_SESSION['info']);
            }
          ?>
        You have been here
        <span class="session-info__connection-count connection-count"
        >
            <?= $_SESSION['counter'] ?>
        </span>
          <?= ngettext("time", "times", $_SESSION['counter']) ?>
        . Your the last visit was
        <time
            datetime="<?= $_SESSION['lastConnect'] ?>"
            class="session-info__connection-last connection-last"
        ><?= $_SESSION['lastConnect'] ?>
        </time
        >
        .
      </div>
    </div>
      <?php
      if (!isInfo($_SESSION)) { ?>
        <form class="session__form session-form" name="form" method="post" action=".<?= $_SERVER['PHP_SELF'] ?>">
          <div class="session-form__item">
            <label for="first-name" class="form-label form-label--center"
            >First name</label
            >
            <input
                required="true"
                type="text"
                name="form[firstName]"
                class="form-input"
                id="first-name"
            />
          </div>
          <div class="session-form__item">
            <label for="last-name" class="form-label form-label--center"
            >Last name</label
            >
            <input
                required="true"
                type="text"
                name="form[lastName]"
                class="form-input"
                id="last-name"
            />
          </div>
          <div class="session-form__item">
            <label for="age" class="form-label form-label--center">Age</label>
            <select name="form[age]" class="form-select" id="age">
                <?php
                for ($i = 1; $i <= 35; $i++) {
                    $html = $i == 15 ? "<option selected value='$i'>$i</option>" : "<option  value='$i'>$i</option>";
                    echo($html);
                }
                ?>
            </select>
          </div>

          <button type="submit" class="session-form__button form-button">
            Send
          </button>
        </form>

      <?php } ?>
  </div>
  <form class="session__form session-clear" name="sessionClear" method="post" action=".<?= $_SERVER['PHP_SELF'] ?>">
    <input type="hidden" name="sessionClear" value="1">
    <button type="submit" class="session-clear__button">Clear Session</button>
  </form>
</div>
</body>
</html>
