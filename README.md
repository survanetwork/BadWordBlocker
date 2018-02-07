# BadWordBlocker
A chat filter which can block certain things

![](https://poggit.pmmp.io/ci.badge/survanetwork/BadWordBlocker/BadWordBlocker)

[Get the latest BadWordBlocker artifacts (PHAR file) here](https://poggit.pmmp.io/ci/survanetwork/BadWordBlocker/BadWordBlocker)

## Description

BadWorkBlocker is a feature rich chat filter suitable for huge servers to keep the chat clean from swear words and spam. It's highly configurable for nearly every needs.

## Features

The main features of this plugin are:

- **SWEAR FILTER** Block messages containing swear words (specified in the config file)
- **BLOCK SAME MESSAGES** Show a warning to the player if he tries to send the same message twice
- **PREVENT SPAM** Prevent spam by preventing the player to send messages in an interval shorter than specified in the config
- **ANTI CAPS** Filter messages containing too much uppercase letters

## Usage

All settings can be changed in the `config.yml`-file, there are no commands:

```yaml
# Language of the plugin messages
# Possible options are: en (English), de (German), ru (Russian)
language: "en"

# List of the blocked words
# Add a new line for every additional swear word, formatted like:
#   - "theswearword"
badwords:
  - "fuck"
  - "shit"
  - "bitch"

# Minimum time between chat messages in seconds
# If a player sends two messages in an interval shorter than 2 (waitingtime) seconds, the second one will be blocked
waitingtime: 2

# Percentage of uppercase chars in a message required to trigger caps checker
# e.g. writing "ABCDEF" -> 1.0 (100%)
#              "ABCdef" -> 0.5 (50%)
uppercasepercentage: 0.75

# Minimum amount of chars in a message required to activate caps checker (to avoid blocking HI, OK, etc.)
minimumchars: 3
```

## Contribution

Feel free to contribute if you have ideas or found an issue.

You can:
- [open an issue](https://github.com/survanetwork/BadWordBlocker/issues) (problems, bugs or feature requests)
- [create a pull request](https://github.com/survanetwork/BadWordBlocker/pulls) (code contributions like fixed bugs or added features)
- [help translating the plugin](https://github.com/survanetwork/BadWordBlocker/tree/master/resources/languages) (create a new language file or correct an existing one)

Many thanks for their support to all contributors!

## License & Credits
[![Creative Commons License](https://i.creativecommons.org/l/by-nc-sa/4.0/88x31.png)](http://creativecommons.org/licenses/by-nc-sa/4.0/)

[BadWordBlocker](https://github.com/survanetwork/BadWordBlocker) by [surva network](https://github.com/survanetwork) is licensed under a [Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License](http://creativecommons.org/licenses/by-nc-sa/4.0/). Permissions beyond the scope of this license may be available on request.
