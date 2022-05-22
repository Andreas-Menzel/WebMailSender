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
GRANT ALL PRIVILEGES ON WebMailSender.* TO user@localhost IDENTIFIED BY 'pass';
```
**Create tables**
```
CREATE TABLE API_KEYS (
    api_key         VARCHAR(128)    PRIMARY KEY,
    mail_from       VARCHAR(128)    NOT NULL,
    name_from       VARCHAR(128)    NOT NULL,
    mail_replyto    VARCHAR(128)    NOT NULL,
    name_replyto    VARCHAR(128)    NOT NULL,
    mail_to         VARCHAR(128)    NOT NULL,
    expire          DATETIME        NOT NULL
    );

CREATE TABLE EMAIL_SETTINGS (
    email           VARCHAR(128)    PRIMARY KEY,
    host            VARCHAR(128)    NOT NULL,
    username        VARCHAR(128)    NOT NULL,
    password        VARCHAR(128)    NOT NULL,
    port            INT
    );

CREATE TABLE LOG (
    id              INT             PRIMARY KEY AUTO_INCREMENT,
    datetime        DATETIME        NOT NULL    DEFAULT(CURRENT_TIMESTAMP),
    error           BOOL            NOT NULL,
    errmsg          VARCHAR(128),
    api_key         VARCHAR(128),
    mail_from       VARCHAR(128),
    name_from       VARCHAR(128),
    mail_replyto    VARCHAR(128),
    name_replyto    VARCHAR(128),
    mail_to         VARCHAR(128),
    subject         VARCHAR(512),
    message         VARCHAR(10240)
    );
```

## Usage
```
a.php?api_key=<api_key>&from=<from>&to=<to>&subject=<subject>&message=<message>
```
api_key, from, to, subject, message

## PHPMailer
This project uses [PHPMailer](https://github.com/PHPMailer/PHPMailer) version [6.6.0](https://github.com/PHPMailer/PHPMailer/releases/tag/v6.6.0) to send emails. All files within `api/PHPMailer-6.6.0` remain unchanged.

Have a look at the libraries [README.md](https://github.com/PHPMailer/PHPMailer/blob/master/README.md), [license](https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE) and [security notices](https://github.com/PHPMailer/PHPMailer/blob/master/SECURITY.md) if you have any questions regarding this library directly.
