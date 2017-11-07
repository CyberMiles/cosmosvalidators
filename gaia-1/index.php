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
    url: "http://gaia-1-node0.testnets.interblock.io:46657/status",
    dataType: 'text',
    error: function(){
      $('#status').css('color', 'red');
      $('#status').html("Down (please check back later)");
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
    
    <h2>Prerequisite</h2>
    
    <p>You need to have GO, GCC, and git installed on your machine.</p>

    <p>You should not have $GOROOT and $GOPATH setup.</p>

    <h2>Build</h2>

<pre>
$ go get github.com/cosmos/gaia 
$ cd $GOPATH/src/github.com/cosmos/gaia
$ make all
</pre>
    
    <p>Upon success, the gaia and gaiacli binaries will be installed in the $GOPATH/bin directory.</p>

<pre>
$ gaia version
v0.3.0
$ gaiacli version
v0.3.0
</pre>
    
    <p>Next initialize your gaiacli utility to the gaia-1 test network.</p>

<pre>
gaiacli init --chain-id=gaia-1 --node=tcp://gaia-1-node0.testnets.interblock.io:46657
</pre>
    
    <p><i>Troubleshooting</i></p>

    <p>If you see errors, try the following re-run the make all.</p>

    <ul>
      <li>Try running a git pull in the $GOPATH/src/github.com/cosmos/gaia directory.</li>
      <li>Remove the .glide folder in the user's home directory.</li>
      <li>Install gilde by hand.</li>
      <li>Delete the $HOME/.cosmos-gaia-cli directory before init the gaiacli. You can also copy the keys files from your past setup into the $HOME/.cosmos-gaia-cli/keys directory.</li>
    </ul>
    
    <h2>Create your own wallet</h2>

    <p>We use the gaiacli utility to create public / private key pairs for the wallet.</p>

<pre>
$ ./gaiacli keys new MyAccount
Enter a passphrase:MyPassword
Repeat the passphrase:MyPassword
MyAccount		ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789
</pre>
    
    <h2>Get some tokens</h2>

    <p>Please use the form below to send some tokens to your newly created wallet (called fermions on the gaia-1 test network).</p>
    
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
    
    <p>You should now be able to see them in your account via gaiacli.</p>
    
<pre>
$ gaiacli query account ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789
{
  "height": 1473,
  "data": {
    "coins": [
      {
        "denom": "fermion",
        "amount": 10
      }
    ],
    "credit": []
  }
}
</pre>
    
    <h2>Run your own node</h2>

    <p>Check out the testnets configurations from github to your local directory, and point the $GAIANET environment variable to the gaia-1 network configuration files.</p>

<pre>
$ git clone https://github.com/tendermint/testnets $HOME/testnets
$ GAIANET=$HOME/testnets/gaia/gaia-1
$ cd $GAIANET
</pre>

    <p>Now you can run your own node. It will take some time to sync up. You can check the current status (including the latest block height) of the gaia-1 network here.</p>

<pre>
$ gaia start --home=$GAIANET
... ...
I[11-07|18:07:44.857] Committed state                              module=state height=1458 txs=0 hash=951D888E60F268E05AA7B87C0F45233479F37D25
... ...
</pre>

    <h2>Bond your node as a validator</h2>

    <p>To bond your node as a validator of the gaia-1 network, you need two pieces of information.</p>

    <ul>
      <li>The wallet that provides the tokens to bond. We already have it when we setup the wallet. It is MyAccount in our case.</li>
      <li>The the public key of your node. This can be found in the $GAIANET/priv_validator.json file. Look for pub_key/data JSON field.</li>
    </ul>
    
<pre>
$ gaiacli tx bond --amount 10fermion --name MyAccount --pubkey THE_PUB_KEY_OF_MY_NODE
</pre>
    
    <p>Now, you should be able to see your node (indentified by its public key) in the network validators end point.</p>
  </div>
    
  <div class="container">
    <p class="lead text-center">Happy validating!</p>
  </div>

  <p class="text-center author">This page is built by your fellow validator <a href="http://michaelyuan.com/">Michael Yuan</a> from <a href="https://cm.5miles.com/">CyberMiles</a>.</p>
  </body>
</html>
