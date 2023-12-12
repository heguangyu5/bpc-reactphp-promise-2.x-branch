#!/bin/bash

[[ "$1" == "" ]] && {
    echo "Usage: ./bpc-prepare.sh src.list"
    exit
}

rm -rf ./React/Promise
rsync -a                        \
      --exclude=".*"            \
      -f"- React/"                \
      -f"+ */"                  \
      -f"- *"                   \
      ./                        \
      ./React/Promise

echo "placeholder-promise.php" > ./React/src.list

for i in `cat $1`
do
    if [[ "$i" == \#* ]]
    then
        echo $i
    else
        echo "Promise/$i" >> ./React/src.list
        filename=`basename -- $i`
        if [ "${filename##*.}" == "php" ]
        then
            echo "phptobpc $i"
            phptobpc $i > ./React/Promise/$i
        else
            echo "cp       $i"
            cp $i ./React/Promise/$i
        fi
    fi
done
cp bpc.conf Makefile ./React/
