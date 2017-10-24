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
    if(ctype_xdigit($to) && strlen($to)==40){
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
    <title>Cosmos Testnet Validator Program</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="style.css">

    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
$(document).ready(function(){
  $.ajax({
    url: "http://atlas-node0.testnets.interblock.io:46657/status",
    dataType: 'text',
    error: function(){
      $('#status').css('color', 'red');
      $('#status').html("Down (Please check back later)");
    },
    success: function(data){
      json_x = $.parseJSON(data);
      if (json_x.error) {
        $('#status').css('color', 'red');
        $('#status').html("Error: " + json_x.error);
      }
      $('#status').css('color', 'green');
      $('#status').html("Last block: " + json_x.result.latest_block_height);
    },
    timeout: 5000
  });
});
    </script>
  </head>
  <body>
  <div class="container">
    <a href="https://cosmos.network/validators">
      <img class="logo" src="cosmos-validator-small.png" alt="Cosmos Validator logo">
    </a>
    <p class="lead text-center">Blockchain Status: <span id="status"></span></p>
    <h1>Cosmos Testnet Validator Program</h1>

    <h2>How to Get Fermions for the Gaia Testnet and Become A Testnet Validator</h2>

    <p>Refer to <a href="https://github.com/cosmos/gaia/blob/master/README.md">this page</a> to build your <code>gaia</code> and <code>gaiacli</code> binaries, and how to run and sync your gaia node to the atlas testnet blockchain.</p>

    <p>Now, you should have already created some accounts to receive fermion tokens from the atlas testnet.</p>

    <pre>
$ gaiacli keys list
All keys:
mytest          F75D0...2
    </pre>
</div>
    <div class="container">
    <h2>Requesting Fermions</h2>

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
<p class="error">Error: <code><?= $errormsg ?></code></p>
<?php
  }
?>
<?php
  if (!empty($res)) {
?>
<p><b>Testnet response follows.</b> If you see <code>deliver_tx</code> <code>"code": 0</code>, that indicates success.</p>
<pre>
<?= $res ?>
</pre>
<?php
  }
?>
</div>
    <div class="container">
      <h2>Becoming a Testnet Validator</h2>

      <p>Upon success, use the following command to check your account balance.</p>

      <pre>$ gaiacli query account F75D0...2</pre>

      <p>Then, you can bond your fermions and become a validator. The <code>$PUBKEY</code> refers to the public key of your node. It is in the <code>$GAIANET/priv_validator.json</code> file.</p>

      <pre>$ gaiacli tx bond --amount 10fermion --name mytest --pubkey $PUBKEY</pre>

      <p class="lead text-center">Happy validating!</p>
    </div>

    <p class="text-center author">This page is built by your fellow validator <a href="http://michaelyuan.com/">Michael Yuan</a> from <a href="https://cm.5miles.com/">CyberMiles</a>.</p>
  </body>
</html>
