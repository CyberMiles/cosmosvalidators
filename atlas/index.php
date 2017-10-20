<?php
  $to = $_REQUEST['to'];
  $home_dir = "/home/myuser";
  $go_path = "/home/myuser/go/bin";
  $from_acct = "mytest";
  $from_pass = "thepassword";

  $recaptcha_secret = "google_recaptcha_secret";
  $recaptcha_key = "google_recaptcha_key";

  if (!empty($to)) {
    // check account key format
    if(ctype_alnum($to) && strlen($to)==40){
      // OK
    } else {
      $errormsg = "The account address is in the wrong format";
    }

    // Check human
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
      'secret' => $recaptcha_secret,
      'response' => $_REQUEST["g-recaptcha-response"]
    );
    $options = array(
      'http' => array (
        'method' => 'POST',
        'content' => http_build_query($data)
      )
    );
    $context  = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);
    $captcha_success=json_decode($verify);

    if ($captcha_success->success==false) {
      $errormsg = "Failed human test";
    } 

    // No error from above
    if (empty($errormsg)) {
      // The command is like this:
      // echo "FROM_ACCT_PASS" | /home/user/go/bin/gaiacli tx send --to=TO_ACCT_KEY --name=FROM_ACCT_NAME --amount=10fermion
      $cmd = 'echo "' . $from_pass . '" | ' . $go_path . '/gaiacli --home "' . $home_dir . '/.cosmos-gaia-cli" tx send --to=' . $to . ' --name=' . $from_acct . ' --amount=10fermion';
      $res = shell_exec( $cmd );
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <script src='https://www.google.com/recaptcha/api.js'></script>
  </head>
  <body>
  <div class="container">
    <h1>Bond myself to become a validator on the atlas test network</h1>

    <p>Refer to <a href="https://github.com/cosmos/gaia/blob/master/README.md">this page</a> to build your <code>gaia</code> and <code>gaiacli</code> binaries, and how to run and sync your gaia node to the atlas testnet blockchain.</p>

    <p>Now, you should have already created some accounts to receive fermion tokens from the atlas testnet.</p>

    <pre>
$ gaiacli keys list
All keys:
mytest          F75D0...2
    </pre>

    <hr/>
    <form method=POST>
      <div class="form-group">
        <label for="to">Account</label>
        <input type="text" class="form-control" name="to" id="to">
        <p class="help-block">It is the F75D0...2 from the above example</p>
      </div>
      <div class="g-recaptcha" data-sitekey="<?= $recaptcha_key ?>"></div>
      <div class="control-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Send me 10 fermions</button>
      </div>
    </form>
<?php
  if (!empty($errormsg)) {
?>
<p>Error: <code><?= $errormsg ?></code></p>
<?php
  }
?>
<?php
  if (!empty($res)) {
?>
<p>Testnet response follows. If you see <code>deliver_tx</code> <code>"code": 0</code>, that indicates success.</p>
<pre>
<?= $res ?>
</pre>
<?php
  }
?>
    <hr/>

    <p>Upon success, Use the following command to check your account balance.</p>

    <pre>$ gaiacli query account F75D0...2</pre>

    <p>Then, you can bond your fermions and become a validator. The <code>$PUBKEY</code> refers to the public key of your node. It is in the <code>$HOME/.atlas/priv_validator.json</code> file.</p>

    <pre>$ gaiacli tx bond --amount 10fermion --name mytest --pubkey $PUBKEY</pre>

    <p class="lead text-center">Happy validating!</p>

    <hr/>
    <p class="text-center">This page is developed by your fellow validator <a href="http://michaelyuan.com/">Michael Yuan</a>.</p>
  </div>
  </body>
</html>
