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
      // echo "FROM_ACCT_PASS" | /home/user/go/bin/gaia client tx send --to=TO_ACCT_KEY --name=FROM_ACCT_NAME --amount=10fermion
      $cmd = 'echo "' . $from_pass . '" | ' . $go_path . '/gaia client --home "' . $home_dir . '/.cosmos-gaia-cli" tx send --to=' . $to . ' --name=' . $from_acct . ' --amount=10fermion';
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
    url: "https://gaia-1-node0.testnets.interblock.io/status",
    dataType: 'text',
    error: function(){
      $('#status-box').css('background', 'rgba(216, 0, 0, 0.1)');
      $('#status').css('color', 'rgb(193, 0, 8)');
      $('#status').html("Down (please check back later)");
    },
    success: function(data){
      json_x = $.parseJSON(data);
      if (json_x.error) {
        $('#status-box').css('background', 'rgba(216, 0, 0, 0.1)');
        $('#status').css('color', 'rgb(193, 0, 8)');
        $('#status').html("Error: " + json_x.error);
      }
      $('#status-box').css('background', 'rgba(30, 186, 0, 0.1)');
      $('#status').css('color', 'rgb(19, 122, 0)');
      $('#status').html("Last block: " + json_x.result.latest_block_height);
    },
    timeout: 5000
  });
});
    </script>
  </head>
  <body>
  <div class="container">
    <nav>
      <a href="https://cosmos.network/validators">
        <img class="logo" src="cosmos-validator-small.png" alt="Cosmos Validator logo">
      </a>
      <div class="links">
        <a href="https://cosmos.network/validators/faq" target="_blank">FAQ</a>
        <a href="https://github.com/cosmos/gaia" target="_blank">GitHub</a>
      </div>
    </nav>
    <h1>Cosmos Testnet Validator Program</h1>
    <div class="box" id="status-box">
      <p class="lead text-center">Blockchain Status: <span id="status">Fetching data ...</span></p>
    </div>

    <h2>Prerequisites</h2>

    <p>You need to have <a href="https://golang.org/doc/install">GO</a>, <a href="https://gcc.gnu.org/install/">GCC</a>, and <a href="https://git-scm.com/book/en/v2/Getting-Started-Installing-Git">git</a> installed on your machine.</p>

    <p>You should now have <code>$GOROOT</code> and <code>$GOPATH</code> setup.</p>

    <h2>Build</h2>

<pre>
$ go get github.com/cosmos/gaia
$ cd $GOPATH/src/github.com/cosmos/gaia
$ make all
</pre>

    <p>Upon success, the <code>gaia</code> binary will be installed in the <code>$GOPATH/bin</code> directory.</p>

<pre>
$ gaia version
v0.4.0
</pre>

    <p>Next, initialize your <code>gaia client</code> utility to the <code>gaia-2</code> test network.</p>

<pre>
gaia client init --chain-id=gaia-2 --node=tcp://gaia-2-node0.testnets.interblock.io:46657
</pre>

    <h3><i>Troubleshooting</i></h3>

    <p>If you see errors, try the following, and then re-run <code>make all</code>.</p>

    <ul>
      <li>Try running a <code>git pull</code> in the <code>$GOPATH/src/github.com/cosmos/gaia</code> directory.</li>
      <li>Remove the <code>.glide</code> folder in the <code>$HOME</code> directory.</li>
      <li>Install <a href="https://glide.sh/">glide</a> by hand.</li>
      <li>Delete the <code>$HOME/.cosmos-gaia-cli</code> directory before initializing the <code>gaia client</code>. You can also copy the keys files from your past setup into the <code>$HOME/.cosmos-gaia-cli/keys</code> directory.</li>
    </ul>

    <h2>Create your own wallet</h2>

    <p>We use the <code>gaia client</code> utility to create public / private key pairs for the wallet.</p>

<pre>
$ ./gaia client keys new MyAccount
Enter a passphrase:MyPassword
Repeat the passphrase:MyPassword
MyAccount		ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789
</pre>

    <h2>Get some tokens</h2>
    <div class="box">
      <p class="text-center">Currently this faucet only works with the gaia-2 testnet. Your balance will not update if you are running a different version of the testnet.</p>
    </div>
    <p>Please use the form below to send some tokens to your newly created wallet (called fermions on the <code>gaia-1</code> test network).</p>

    <form method=POST>
      <div class="form-group">
        <label for="to">Account</label>
        <input type="text" class="form-control" name="to" id="to">
        <p class="help-block">It is the ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789 from the above example</p>
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

    <p>You should now be able to see them in your account via the <code>gaia client</code>.</p>

<pre>
$ gaia client query account ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789
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

    <p>Check out the testnets configurations from github to your local directory, and point the <code>$GAIANET</code> environment variable to the <code>gaia-1</code> network configuration files.</p>

<pre>
$ git clone https://github.com/tendermint/testnets $HOME/testnets
$ GAIANET=$HOME/testnets/gaia-1/gaia
$ cd $GAIANET
</pre>

    <p>Now you can run your own node. It will take some time to sync up. You can check the current status (including the latest block height) of the <code>gaia-1</code> network <a href="http://gaia-1-node0.testnets.interblock.io:46657/status">here</a>.</p>

<pre>
$ gaia start --home=$GAIANET
... ...
I[11-07|18:07:44.857] Committed state                              module=state height=1458 txs=0 hash=951D888E60F268E05AA7B87C0F45233479F37D25
... ...
</pre>

    <h2>Bond your node as a validator</h2>

    <p>To bond your node as a validator of the <code>gaia-1</code> network, you need two pieces of information.</p>

    <ul>
      <li>The wallet that provides the tokens to bond. We already have it when we setup the wallet. It is <code>MyAccount</code> in our case.</li>
      <li>The the public key of your node. This can be found in the <code>$GAIANET/priv_validator.json</code> file. Look for <code>pub_key/data</code> JSON field.</li>
    </ul>

<pre>
$ gaia client tx bond --amount 10fermion --name MyAccount --pubkey THE_PUB_KEY_OF_MY_NODE
</pre>

    <p>Now, you should be able to see your node (indentified by its public key) in the network <a href="http://gaia-1-node0.testnets.interblock.io:46657/validators">validators</a> end point.</p>

    <h2>Automated scripts</h2>

    <p>Once you understand how the process works, you can use the following community maintained scripts to automate the deployment of your testnet node so that you do not have to type in everything every time!</p>

    <ul>
      <li>User <a href="https://github.com/mdyring">mdyring</a> created a script to <a href="https://gist.github.com/mdyring/47545b1e03b6a4eb3b29dfa599e50151">setup a gaia-1 full node on EC2</a>. You can pass in <code>user-data</code> for automation.</li>
    </ul>

  </div>

  <div class="footer">
    <p class="lead text-center">Happy validating!</p>
    <p class="text-center author">This page is built by your fellow validator <a href="http://michaelyuan.com/">Michael Yuan</a> from <a href="https://cm.5miles.com/">CyberMiles</a>.</p>
  </div>

  </body>
</html>
