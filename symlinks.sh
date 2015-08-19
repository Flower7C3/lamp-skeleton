
cd `dirname $0`

suffix=".local"
dstDir="domains/"
srcDir="projects/"

declare -A domains

domains=(
	[exammple.com]=example_company/example_page/www
)

find ${dstDir} -maxdepth 1  -type l -delete

for id in "${!domains[@]}"
do
	d=${dstDir}${id}${suffix}
	s=${srcDir}${domains[$id]}
	ln -s -r $s $d
done
