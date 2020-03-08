#!bash

gitsource='https://github.com/qkqpttgf/OneManager-php'

git clone ${gitsource}
\mv -b config.php OneManager-php/
\mv -b OneManager-php/* ./
\mv -b OneManager-php/.[^.]* ./
rm -rf *~
rm -rf .[^.]*~
rm -rf OneManager-php
chmod 666 config.php
