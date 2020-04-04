#!bash

if [ $# -eq 0 ]; then
  echo "
"$0" -i|u [-b branch]
        i       a new install with empty config.
        u       update use exist config.
        b       install the branch after parameter b, default master

example:
        "$0" -i
        "$0" -u
        "$0" -b master
        "$0" -r -b master
        "$0" -ib test
"
#  exit
  echo "###############
0, new install
1, update"
  read -p "Input:" c
  [ g"$c" == g"0" ] && install=1
  [ g"$c" == g"1" ] && update=1
fi

i=0
para[$i]=$0
for av in "$@"
do
#echo $av
  ((i++))
  para[$i]=$av
  if [ g"${av:0:1}" == g"-" ]; then
    while [ g"$av" != g"" ]
    do
      ag=${av:0:1}
      av=${av:1}
      [ g"$ag" == g"b" ] && isbranch=1
      [ g"$ag" == g"i" ] && install=1
      [ g"$ag" == g"u" ] && update=1
    done
  else
    if [ g"$isbranch" == g"1" ]; then
      branch="-b $av"
      isbranch=0
    fi
  fi
done

if [ g"$install" == g"1" -a g"$update" == g"1" ]; then
  echo "Both install & update, exit"
  exit
fi
if [ g"$install" != g"1" -a g"$update" != g"1" ]; then
  echo "Not install & Not update, exit"
  exit
fi

gitsource='https://github.com/qkqpttgf/OneManager-php'

OneManagerPath=`cd $(dirname $0);pwd -P`
cd ${OneManagerPath}

git clone ${branch} ${gitsource}
[ g"$install" == g"1" ] || \mv -b config.php OneManager-php/
\mv -b OneManager-php/* ./
\mv -b OneManager-php/.[^.]* ./
rm -rf *~
rm -rf .[^.]*~
rm -rf OneManager-php
chmod 666 config.php

