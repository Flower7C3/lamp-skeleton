
cd `dirname $0`
cd ..

suffix0=".local"
suffix1=".192.168.33.99.xip.io"
dstDir="domains/"
srcDir="projects/"
domainsFile="apache/domains.list"

find ${dstDir} -maxdepth 1  -type l -delete


echo "New domains:"

cat $domainsFile |                      
while read -r line;
do
	if [[ $line == \(* ]];
	then
   		declare -A row="$line"
   		domain=${row[domain]}
   		directory=${row[dir]}
   		echo "- $domain"

		# if [[ -n "${row[tld]}" ]];
		# then
		# 	tld0="."${row[tld]}
		# else
		# 	tld0=$suffix0
		# fi
		# d=${dstDir}${domain}${tld0}
		# s=${srcDir}${directory}
		# ln -s -r $s $d

		tld1=$suffix1
		d=${dstDir}${domain}${tld1}
		s=${srcDir}${directory}
		ln -s -r $s $d

	    HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`

	    cacheDir=${d}/app/cache/
	    if [ -d "$DIRECTORY" ]; then
		    #rm -rf ${cacheDir}*
		    #chmod 775 ${cacheDir}
		    setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${cacheDir}
		    setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${cacheDir}
	    fi

	    logsDir=${d}/app/logs/
	    if [ -d "$DIRECTORY" ]; then
		    #rm -rf ${logsDir}*
		    #chmod 775 ${logsDir}
		    setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${logsDir}
		    setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX ${logsDir}
	    fi

	fi
done;
