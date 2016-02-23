
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

		if [[ -n "${row[tld]}" ]];
		then
			tld0="."${row[tld]}
		else
			tld0=$suffix0
		fi
		d=${dstDir}${domain}${tld0}
		s=${srcDir}${directory}
		ln -s -r $s $d

		# tld1=$suffix1
		# d=${dstDir}${domain}${tld1}
		# s=${srcDir}${directory}
		# ln -s -r $s $d

	fi
done;
