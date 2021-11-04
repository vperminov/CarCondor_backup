### PHP script Usage example
```
 php backup.php -u user_name -p user_password -d database_name -f /var/backups
```

**Params**

- `-u` `--user` database username
- `-p` `--password` database password
- `-d` `--database` database name
- `-f` `--folder` folder to store backup
- `-h` `--host` (optional) database host; default: `127.0.0.1`
- `-o` `--port` (optional) database port; default: `3306`


### Bash script Usage example
```
  sh backup.sh -u user_name -p user_password -d database_name -f /var/backups
```

**Params**

- `-u` database username
- `-p` database password
- `-d` database name
- `-f` folder to store backup

### PHP import script Usage example
```
  php import.php -u user_name -p user_password -d database_name -f /var/backups/db-backup.tar.gz
```

**Params**

- `-u` database username
- `-p` database password
- `-d` database name
- `-f` backup file (can be *.sql or *.tar.gz)