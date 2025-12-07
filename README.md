<p align="center">
    <img src=".github/.media/logo.png" width="144" height="144" alt="BadWordBlocker plugin logo">
</p>

<h1 align="center">BadWordBlocker</h1>
<p align="center">A Chat Filter which can block certain things</p>

<br>

<p align="center">
    <a href="https://poggit.pmmp.io/p/BadWordBlocker">
        <img src="https://poggit.pmmp.io/shield.state/BadWordBlocker" alt="Plugin version">
    </a>
    <a href="https://github.com/pmmp/PocketMine-MP">
        <img src="https://poggit.pmmp.io/shield.api/BadWordBlocker" alt="PocketMine-MP API version">
    </a>
    <a href="https://poggit.pmmp.io/p/BadWordBlocker">
        <img src="https://poggit.pmmp.io/shield.dl.total/BadWordBlocker" alt="Downloads on Poggit">
    </a>
    <a href="https://github.com/survanetwork/BadWordBlocker/blob/master/LICENSE">
        <img src="https://img.shields.io/github/license/survanetwork/BadWordBlocker.svg" alt="License">
    </a>
    <a href="https://discord.gg/t4Kg4j3829">
        <img src="https://img.shields.io/discord/685532530451283997?color=blueviolet" alt="Discord">
    </a>
    <a href="https://dev.surva.net/plugins/">
        <img src="https://img.shields.io/badge/website-visit-ee8031" alt="Website">
    </a>
</p>

##

<p align="center">
    <a href="https://dev.surva.net/plugins/#badwordblocker">
        <img src="https://static.surva.net/osplugins/assets/dl-buttons/badwordblocker.png" width="220" height="auto" alt="Download BadWordBlocker plugin release">
        <img src="https://static.surva.net/osplugins/assets/feature-banners/badwordblocker.png" width="650" height="auto" alt="BadWordBlocker plugin features">
    </a>
</p>

[Description](#-description) | [Features](#-features) | [Usage](#-usage)
| [Contribution](#-contribution) | [License](#%EF%B8%8F-license)

## 📙 Description
BadWorkBlocker is a chat filter suitable for every server to keep the chat clean from swear words and spam (including ready-to-use lists).
It can prevent swear words, sending the same message twice, sending too many messages and using caps lock.
Additionally, it can filter messages that contain IP addresses, websites or e-mail addresses.
Filtering is done at each aspect of the game, including the public server chat, private tell messages and text on signs.
The plugin is highly configurable for nearly every needs.

## 🎁 Features
The main features of this plugin are:

- **SWEAR FILTER** Block messages containing swear words (specified in the config file)
- **BLOCK SAME MESSAGES** Show a warning to the player if he tries to send the same message twice
- **PREVENT SPAM** Prevent spam by preventing the player to send messages in an interval shorter than specified in the config
- **ANTI CAPS** Filter messages containing too much uppercase letters
- **BLOCK ADDRESSES** Prevent sending IP addresses, websites/domains and e-mail addresses into the chat
- **IMPORT LISTS** Import profanity word list from internet sources right into the plugin's config
- **ADVANCED FILTERING** Not only check chat messages, but also private messages or text on placed signs

## 🖱 Usage
All settings can be changed in the `config.yml`-file, there are no commands:

```yaml
# Language of the plugin messages
# Possible options are: en (English), de (German), fr (French), ru (Russian), tr (Turkish), id (Indonesian)
language: "en"

# Try to automatically detect the player's language and send translated messages for each player
# (language set above is used if player's language can't be detected)
autodetectlanguage: true

# List of the blocked words
badwords:
  - "fuck"
  - "shit"
  - "bitch"

# Ignore spaces in messages when checking for swear words / spam
ignorespaces: true

# Show the player which word has been blocked
showblocked: false

# Minimum time between chat messages in seconds
waitingtime: 2

# Percentage of uppercase chars in a message required to trigger caps checker
uppercasepercentage: 0.75

# Minimum amount of chars in a message required to activate caps checker (to avoid blocking HI, OK, etc.)
minimumchars: 3

# Filter web addresses like IP addresses, domains, or email addresses
filter_web_addresses: true

# Check placed signs if they violate a filter
check_signs: true

# After how many violations against the chat filter, a player should be kicked/banned - set to 0 to disable
# Violations are reset after a server restart
violations:
  kick: 0
  ban: 0
  resetafterkick: true # reset violations after kick (this disables banning if kick-count is lower)

# Send a reminder message once per session if bypassing filters using ones permissions
send_bypassed_message: true
```

[Read the full documentation 📖](https://plugin-docs.surva.net/badwordblocker) • [Ask questions on Discord 💬](https://discord.gg/t4Kg4j3829)

## 🙋‍ Contribution
Feel free to contribute if you have ideas or found an issue.

You can:
- [open an issue](https://github.com/survanetwork/BadWordBlocker/issues) (problems, bugs or feature requests)
- [create a pull request](https://github.com/survanetwork/BadWordBlocker/pulls) (code contributions like fixed bugs or added features)
- [help translating the plugin](https://www.transifex.com/surva/badwordblocker) (help us to translate this plugin into your language on Transifex platform)

Please read our **[Contribution Guidelines](CONTRIBUTING.md)** before creating an issue or submitting a pull request.

Many thanks for their support to all contributors!

## 👨‍⚖️ License
[MIT](https://github.com/survanetwork/BadWordBlocker/blob/master/LICENSE)
