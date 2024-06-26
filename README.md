# Sweepstakes Announcer

Bot to announce football events and tag the relevant team "owners" to either Slack or Discord using "Incoming Webhooks".

This bot is useful for keeping people involved in World Cup or other major football tournament sweepstakes, each country is assigned to a tag or full name. Tags are detected by checking for a preceding "@".

This bot then posts to Slack or Discord at the start or end of a match, tagging the relevant people involved in the match as well as the result of that match.

### Example messages

![example image](example.png "Example")

## Usage (Easy)

You can either head over to https://sweepstakes.plastonick.me and follow instructions from there, or continue reading to host the bot yourself.

## Usage (Developer)

You'll need to generate an api token at football-data.org, and an incoming webhook for your Slack or Discord channel.

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

### Frontend

See [Sweepstakes Frontend](https://github.com/Plastonick/Sweepstakes-Frontend) for the frontend code to host this.

1. Setup a postgres database 
2. Update the .env variables 
3. Start a PHP server: `php -S 0.0.0.0:8090 public/index.php`
4. Start the frontend server
