<?php
shell_exec('find /var/www/html/magento/var/cache/ -type f -mmin +120 -exec rm {} \;');
?>