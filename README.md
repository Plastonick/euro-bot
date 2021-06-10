# Euro Slack Bot

Slack Bot to announce football events and tag the relevant team "owners"!

This bot is useful for keeping people involved in Euros sweepstakes, each country is given a slack username or simply a name. Slack tags are detected by checking for all lowercase names.

This bot then posts to slack at the start or end of a match, tagging the relevant people involved in the match as well as the result of that match.

### Example messages

![example image](example.png "Example")

## Usage

You'll need to generate an api token at football-data.org, and an incoming webhook for your Slack workspace/channel.

### Native

Clone this repository, copy the `.env.example` to `.env` and input the relevant values for your use case. 

Run by executing `src/App.php`, the process will continue indefinitely.

### Docker

Generate your `.env` file from the `.env.example` in this repository, then run it using the command below, inserting the path to your `.env` file (if you're in the same directory, you can use `` `pwd`/.env``)

```
docker run -d --rm \
    -v <local/path/to/.env>:/app/.env \
    davidpugh/euro-bot:latest
```
