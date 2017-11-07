# Build and install

Check the [live status](http://www.cosmosvalidators.com/) of the public test network `gaia-1`.

### Prerequisite

You need to have [GO](https://golang.org/doc/install), 
[GCC](https://gcc.gnu.org/install/), and 
[git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git) installed on your machine.

You should not have `$GOROOT` and `$GOPATH` setup.

### Build

```
$ go get github.com/cosmos/gaia 
$ cd $GOPATH/src/github.com/cosmos/gaia
$ make all
```

Upon success, the `gaia` and `gaiacli` binaries will be installed in the `$GOPATH/bin` directory.

```
$ gaia version
v0.3.0
$ gaiacli version
v0.3.0
```

Next initialize your `gaiacli` utility to the `gaia-1` test network.

```
gaiacli init --chain-id=gaia-1 --node=tcp://gaia-1-node0.testnets.interblock.io:46657
```

Troubleshooting

If you see errors, try the following re-run the `make all`.

1. Try running a `git pull` in the `$GOPATH/src/github.com/cosmos/gaia directory`.
2. Remove the `.glide` folder in the user's home directory.
3. [Install gilde](https://glide.sh/) by hand.
4. Delete the `$HOME/.cosmos-gaia-cli` directory before init the `gaiacli`. You can also copy the keys files from your past setup into the `$HOME/.cosmos-gaia-cli/keys` directory.


### Create your own wallet

We use the `gaiacli` utility to create public / private key pairs for the wallet.

```
$ ./gaiacli keys new MyAccount
Enter a passphrase:MyPassword
Repeat the passphrase:MyPassword
MyAccount		ABCDEFGHIGKLMNOPQRSTUVWXYZ123456789
```

### Get some tokens

Please go to the [CosmosValidators.com](http://www.cosmosvalidators.com/) web site to 
send some tokens to your newly created wallet (called fermions on the `gaia-1` test network). You should now be able to see
them in your account via `gaiacli`.

```
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
```

### Run your own node

Check out the `testnets` configurations from github to your local directory, and 
point the `$GAIANET` environment variable to the `gaia-1` network configuration files.

```
$ git clone https://github.com/tendermint/testnets $HOME/testnets
$ GAIANET=$HOME/testnets/gaia/gaia-1
$ cd $GAIANET
```

Now you can run your own node. It will take some time to sync up. 
You can check the current status (including the latest block height) of the gaia-1 network [here](http://gaia-1-node0.testnets.interblock.io:46657/status).

```
$ gaia start --home=$GAIANET
... ...
I[11-07|18:07:44.857] Committed state                              module=state height=1458 txs=0 hash=951D888E60F268E05AA7B87C0F45233479F37D25
... ...
```

### Bond your node as a validator

To bond your node as a validator of the `gaia-1` network, you need two pieces of information. 

1. The wallet that provides the tokens to bond. We already have it when we setup the wallet. It is `MyAccount` in our case.
2. The the public key of your node. This can be found in the `$GAIANET/priv_validator.json` file. Look for `pub_key/data` JSON field.

```
$ gaiacli tx bond --amount 10fermion --name MyAccount --pubkey THE_PUB_KEY_OF_MY_NODE
```

Now, you should be able to see your node (indentified by its public key) in the [network validators](http://gaia-1-node0.testnets.interblock.io:46657/validators) end point.


