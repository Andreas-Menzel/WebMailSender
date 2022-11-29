# WebMailSender

## Web-API for sending emails.

WebMailSender is an easy-to-use PHP Web-API to send emails via a SMTP-server. It
has two advantages:

**1) Security**: No need to store the email credentials on every devide. You can
create an expiring api-key that limits the email addresses to which an email can
be sent.

**2) Usability**: Just "open a website" to send the email. No need to manually
connect to the mail-server.

### Main Setup

#### Database

**Create database**
```sql
CREATE DATABASE WebMailSender;
```
**Grant usage on database**
```sql
GRANT ALL PRIVILEGES ON WebMailSender.* TO user@localhost IDENTIFIED BY 'pass';
```
**Create tables**
```sql
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

#### API

Clone the repository and move it in your webserver's directory.

Example for Apache:

```bash
cd /var/www/html
git clone https://github.com/Andreas-Menzel/WebMailSender.git
```

Search and update the following lines in `api/WebMailSender.php`:

```php
$sql_host     = "localhost";
$sql_dbname   = "WebMailSender";
$sql_user     = "WebMailSender";
$sql_password = "WebMailSenderPass";
```

In most setups, `$sql_host` will be `localhost`. If you copied the instructions
above, `$sql_dbname` will also not change.


### Add mail credentials and api-keys

#### Add a new email account that can be used by WebMailSender

```sql
INSERT INTO EMAIL_SETTINGS (host, username, password, port) VALUES (<host>,<username>, <password>, <port>);
```

#### Create a new API-key

```sql
INSERT INTO API_KEYS (api_key, mail_from, name_from, mail_replyto, name_replyto, mailto, expire) VALUES (<api_key>, <mail_from>, <name_from>, <mail_replyto>, <name_replyto>, <mailto>, <expire>);
```

**Important**

- The `<api_key>` should be a long and secure key, that cannot be guessed.
- `<mail_from>` must be an email address that was previously added to the
  database.
- `<mail_to>` can be a regular expression to specify the format of the allowed
  recipient addresses (or a list of email addresses specified as a regex).
- It is highly recommened to set the `<expire>` to a useful value (you can
  always create a new api-key or update the expire date!).


### Usage

To send an email with WebMailSender, just "open the website" and set the
following parameters via `GET`:

```
WebMailSender.php?api_key=<api_key>&from=<from>&to=<to>&subject=<subject>&message=<message>
```

- `<api_key>`: A valid api-key
- `<from>`: An email address that was added to the WebMailSender database
  **and** the api-key.
- `<to>`: An email address that matches the regex specified in the api-key.
- `<subject>` and `<message>`: The subject and message of the email.

You can evaluate the response from WebMailSender given as a json-formatted text.
You will receive the following response if the email was sent successfully:

```json
{
    "error": false
}
```

If an error occured, `error` will be set to `true` and an `<errmsg>` will be set
to describe the error type.

```json
{
    "error": false,
    "errmsg": "Error ..."
}
```


### Contributing

If you would like to contribute to this project, I would be more than happy!

The main missing module that would highly improve the user experience is a
user interface (e.g. Web-Interface or Python script) to change the settings and
manage the api-keys. At the moment I'm not planning on implementing this feature
myself because I don't need it.


### PHPMailer
This project uses [PHPMailer](https://github.com/PHPMailer/PHPMailer) version
[6.6.0](https://github.com/PHPMailer/PHPMailer/releases/tag/v6.6.0) to send
emails. All files within `api/PHPMailer-6.6.0` remain unchanged and are not a
part of this project and its license.

Have a look at the libraries
[README.md](https://github.com/PHPMailer/PHPMailer/blob/master/README.md),
[license](https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE) and
[security notices](https://github.com/PHPMailer/PHPMailer/blob/master/SECURITY.md)
if you have any questions regarding this library directly.
