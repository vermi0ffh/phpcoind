phpcoind
========

A BitCoin/DogeCoin/AltCoin client written in PHP. The goal is to allow everyone with a simple php installation to connect and interract
with XXXCoins networks.


PHP Requirements
================

At least **php 5.4** is needed, with **socket support**.


Project Status
==============

For now, the project is not working and is subject to heavy code lifting.

What is working
---------------
* Connect to other peers (send version and verack packets)
* Can read all types of packets, but payload is not always parsed
* Can understand alert messages (at least enough to display the message in the logfile)

What is not working
-------------------
* Blockchain download
* Transactions understanding
* Emmiting transactions
* Store wallets
* JSON API compatible with dogecoind
* Advanced JSON API
* Multiple networks
* Nice configuration
* ...