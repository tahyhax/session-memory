<?php
ini_set('session.gc_maxlifetime', 120960);
ini_set('session.cookie_lifetime', 120960);

date_default_timezone_set("Europe/Kiev");

$_POST = json_decode(file_get_contents("php://input"), true);

session_start();

if (isset($_POST['form'])) {

    saveUserInfo($_POST['form']);

    sendResponse($_SESSION);


}

if (isset($_POST['sessionClear'])) {
    $clearSession = sessionClear();
    $data = [
        'message' => $clearSession
            ? 'The session has been cleared'
            : 'An error occurred while clearing the session'
    ];
    sendResponse($data);
}

if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 0;
}
$_SESSION['counter']++;
$_SESSION['lastConnect'] = date('m/d/Y  H:i:s');


/**
 * response data to js
 *
 * @param array $data data dor response
 * @param string $errorCode
 * @param string $errorMessage
 */
function sendResponse($data, $errorCode = null, $errorMessage = null)
{
    header('Content-Type: application/json; charset=utf-8');
    $response['data'] = $data;
    $response['error']['code'] = $errorCode;
    $response['error']['message'] = $errorMessage;

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * save new  data  to session
 *
 * @param  array $params
 * // * @param string $sessionId current  session id
 */
function saveUserInfo($params)
{

    $data = [];
    //todo  validate
    foreach ($params as $key => $value) {
        $data[$key] = trim(stripcslashes($value));
    }

    $yearOfBirth = getYearOfBirth($data['age']);

    $data['info']['yearOfBirth'] = $yearOfBirth;
    $_SESSION['info'] = $data;


//    add return $_SESSION

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
    return isset($data['info']);
}

/**
 * render  html  block  "user info"
 * @param array $info
 * @return string
 */
function getUseInfoHtml($info)
{
    $template = 'You are %s %s and you were born in %s.';
    return sprintf($template, $info["firstName"], $info["lastName"], $info["yearOfBirth"]);
}

/**
 * clear  current  session
 * @return bool
 */
function sessionClear()
{
    return session_destroy();
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
      cursor: pointer;
      bottom: 10%;
      right: 10%;
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
              echo getUseInfoHtml($_SESSION['info']);
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

        <div class="session__form session-form">
          <div class="session-form__item">
            <label for="first-name" class="form-label form-label--center"
            >First name</label
            >
            <input
                required="true"
                type="text"
                name="firstName"
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
                name="lastName"
                class="form-input"
                id="last-name"
            />
          </div>
          <div class="session-form__item">
            <label for="age" class="form-label form-label--center">Age</label>
            <select name="age" class="form-select" id="age">
                <?php
                for ($i = 1; $i <= 35; $i++) {
                    $html = $i == 15 ? "<option selected value='$i'>$i</option>" : "<option  value='$i'>$i</option>";
                    echo($html);
                }
                ?>
            </select>
          </div>

          <button type="button" class="session-form__button form-button">
            Send
          </button>
        </div>

      <?php } ?>
  </div>
  <button class="session-clear">Clear Session</button>
</div>
<script>
  const form = document.querySelector(".session-form");
  const buttonClear = document.querySelector(".session-clear");
  const sessionInfoText = document.querySelector('.session-info__text');

  if (form) {
    const button = form.querySelector(".form-button");
    const firstName = form.querySelector("input[name='firstName']");
    const lastName = form.querySelector("input[name='lastName']");
    const age = form.querySelector("select[name='age']");

    button.addEventListener("click", async (event) => {
      event.preventDefault;
      try {
        const params = {
          form: {
            firstName: firstName.value,
            lastName: lastName.value,
            age: age.value,
          },
        };
        const {data, error} = await sendForm(params);

        if (error.code) {
          console.log(error.message);
          return;
        }

        form.remove();

        renderUserInfo(data.info, sessionInfoText);

      } catch (e) {
        console.log(e);
      }

    });
  }

  buttonClear.addEventListener('click', async (event) => {
    try {
      const {data} = await  clearSession();
      console.info(data.message);
      location.reload();
    } catch (e) {
      console.log(e);
    }
  });


  async function sendForm(params) {
    try {
      const response = await fetch("./session-memory.php", {
        method: 'post',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(params),
      });
      const result = await response.json();
      return result;
    } catch (e) {
      return Promise.reject(e);
    }

  }

  async function clearSession() {

    try {
      const response = await fetch('./session-memory.php', {
        method: 'post',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({sessionClear: true}),
      });
      const result = await response.json();
      return result;
    } catch (e) {
      return Promise.reject(e);
    }

  }


  function renderUserInfo(data, rendreBlock) {
    const {firstName, lastName, yearOfBirth} = data;
    const template = `You are ${firstName} ${lastName} and you were born in ${yearOfBirth}.`;
    rendreBlock.prepend(template);
  }
</script>
</body>
</html>
