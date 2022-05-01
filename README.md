# WebMailSender
Web-API for sending emails.

## Setup

### Install API
...

### Setup database

**Create database**
```
CREATE DATABASE WebMailSender;
```
**Grant usage on database**
```
GRANT ALL PRIVILEGES ON WebMailSender TO user@localhost IDENTIFIED BY 'pass';
```
**Create tables**
```
CREATE TABLE API_KEYS (
    api_key         VARCHAR(128)    PRIMARY KEY,
    mail_from       VARCHAR(128)    NOT NULL,
    mail_replyto    VARCHAR(128)    NOT NULL,
    mail_to         VARCHAR(128)    NOT NULL
    );

CREATE TABLE EMAIL_CREDENTIALS (
    email       VARCHAR(128)    PRIMARY KEY,
    host        VARCHAR(128)    NOT NULL,
    username    VARCHAR(128)    NOT NULL,
    password    VARCHAR(128)    NOT NULL,
    port        INT
    );
```

## Usage
```
a.php?api_key=<api_key>&from=<from>&to=<to>&subject=<subject>&message=<message>
```
api_key, from, to, subject, message
