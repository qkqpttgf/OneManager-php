#!/bin/bash

if [ $# -eq 0 ]; then
    echo "
Useage:
"$0" -i|u [-g source] [-b branch]
        i       a new install with empty config.
        u       update use exist config.
        g       appoint code source, Github or Gitee.
        b       install the branch after parameter b, default master

example:
        "$0" -i
        "$0" -u
        "$0" -u -b master
        "$0" -ib test
        "$0" -u -g Gitee -b master
"
    echo "###############
    0, update
    1, new install"
    read -p "Input 0 or 1:" c
    [ g"$c" == g"1" ] && install=1 || update=1
    echo "###############
Get code from?
    0, Github
    1, Gitee"
    read -p "Input 0 or 1:" c
    [ g"$c" == g"1" ] && gitSource="gitee" || gitSource="github"
fi

i=0
para[$i]=$0
for av in "$@"; do
    #echo $av
    ((i++))
    para[$i]=$av
    if [ g"${av:0:1}" == g"-" ]; then
        while [ g"$av" != g"" ]; do
            ag=${av:0:1}
            av=${av:1}
            [ g"$ag" == g"b" ] && isbranch=1
            [ g"$ag" == g"g" ] && appointsource=1
            [ g"$ag" == g"i" ] && install=1
            [ g"$ag" == g"u" ] && update=1
        done
    else
        if [ g"$isbranch" == g"1" -a g"$appointsource" == g"1" ]; then
            echo "source and branch should separate appoint, exit"
            exit 1
        fi
        if [ g"$appointsource" == g"1" ]; then
            gitSource="${av,,}"
            appointsource=0
        fi
        if [ g"$isbranch" == g"1" ]; then
            branch="$av"
            isbranch=0
        fi
    fi
done

if [ g"$install" == g"1" -a g"$update" == g"1" ]; then
    echo "Both install & update, exit"
    exit 1
fi
if [ g"$install" != g"1" -a g"$update" != g"1" ]; then
    echo "Not install & Not update, exit"
    exit 1
fi

if [ g"${branch}" = g"" ]; then
    branch="master"
fi

OneManagerPath=$(
    cd $(dirname $0)
    pwd -P
)
cd ${OneManagerPath}

if [ g"${gitSource}" = g"gitee" ]; then
    echo " download from Gitee"
    #  $url = 'https://gitee.com/' . $auth . '/' . $project . '/repository/archive/' . urlencode($branch) . '.zip';
    wget -qc "https://gitee.com/qkqpttgf/OneManager-php/repository/archive/${branch}.zip"
    unzip -qo "${branch}.zip"

    cd "OneManager-php-"*
    if [ $? -eq 0 ]; then
        tmpFolder=$(pwd -P | awk -F "/" '{print $NF}')
        #echo "${tmpFolder}"
        cd ../
    else
        echo " download code or unzip failed"
        exit 1
    fi
else
    echo " download from Github"
    #https://github.com/qkqpttgf/OneManager-php/archive/refs/heads/master.zip
    #wget -qc "https://github.com/qkqpttgf/OneManager-php/archive/refs/heads/${branch}.zip"
    #unzip "${branch}.zip"
    #$url = 'https://github.com/' . $auth . '/' . $project . '/tarball/' . urlencode($branch) . '/';
    wget -qc "https://github.com/qkqpttgf/OneManager-php/tarball/${branch}/" -O "${branch}.tar.gz"
    tar -xzf "${branch}.tar.gz"

    cd "qkqpttgf-OneManager-php-"*
    if [ $? -eq 0 ]; then
        tmpFolder=$(pwd -P | awk -F "/" '{print $NF}')
        #echo "${tmpFolder}"
        cd ../
    else
        echo " download code or untar failed"
        exit 1
    fi
fi

if [ g"${tmpFolder}" != g"" ]; then
    [ g"$install" == g"1" ] || \mv -f ".data/config.php" "${tmpFolder}/.data/"
    \mv -b "${tmpFolder}/"* ./
    \mv -b "${tmpFolder}/".[^.]* ./
    rm -rf *~
    rm -rf .[^.]*~
    rm -f "${branch}.zip"
    rm -f "${branch}.tar.gz"
    rm -rf "${tmpFolder}"
    chmod 666 .data/config.php
    echo " Done success!"
else
    echo " No new code folder"
    exit 1
fi
